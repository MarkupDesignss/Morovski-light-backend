<?php

namespace App\Jobs;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class GenerateItemPDFJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $itemIds;
    protected array $requestData;
    protected string $jobId;
    protected ?string $callbackUrl;
    protected const BATCH_SIZE = 50;
    protected const PDF_STORAGE_PATH = 'pdfs/jobs/';
    protected const CACHE_TTL = 7200; // 2 hours
    
    public $timeout = 600;
    public $tries = 3;
    public $backoff = [30, 60, 120];

    public function __construct(array $itemIds, array $requestData, string $jobId, ?string $callbackUrl = null)
    {
        $this->itemIds = $itemIds;
        $this->requestData = $requestData;
        $this->jobId = $jobId;
        $this->callbackUrl = $callbackUrl;
    }

    public function handle(): void
    {
        $startTime = microtime(true);
        
        try {
            $this->updateStatus('processing', 'Generating PDF...');

            $items = $this->getOptimizedItems();

            if ($items->isEmpty()) {
                $this->handleError('No items found.');
                return;
            }

            $pdfContent = $this->processItemsInChunks($items);

            // Store PDF in filesystem
            $this->storePdfInFilesystem($pdfContent, $items);

            // Send callback
            $this->sendCallback('completed', '', $pdfContent, $items);

            $duration = round((microtime(true) - $startTime) * 1000);
            Log::info("PDF generated successfully for job: {$this->jobId}", [
                'items' => $items->count(),
                'duration_ms' => $duration,
                'size_kb' => round(strlen($pdfContent) / 1024, 2)
            ]);

        } catch (\Exception $e) {
            $this->handleError($e->getMessage());
            Log::error("PDF generation failed for job {$this->jobId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get optimized items with chunking
     */
    private function getOptimizedItems(): Collection
    {
        $items = collect();
        $chunks = array_chunk($this->itemIds, self::BATCH_SIZE);

        foreach ($chunks as $chunk) {
            $chunkItems = Item::with([
                'category' => function($query) {
                    $query->select('id', 'name', 'parent_id');
                },
                'category.parent' => function($query) {
                    $query->select('id', 'name');
                }
            ])
            ->whereIn('id', $chunk)
            ->select([
                'id', 'name', 'sku', 'price', 'type', 'model',
                'quantity', 'damaged_quantity', 'rating', 'is_featured',
                'description', 'category_id', 'created_at'
            ])
            ->get();

            $items = $items->merge($chunkItems);
        }

        return $items;
    }

    /**
     * Process items in chunks
     */
    private function processItemsInChunks(Collection $items): string
    {
        $pdfContents = [];
        $chunks = $items->chunk(self::BATCH_SIZE);

        foreach ($chunks as $index => $chunk) {
            $data = $this->prepareData($chunk);
            $html = $this->generateOptimizedHTML($data);
            
            $mpdf = $this->createMpdfInstance();
            
            try {
                $mpdf->WriteHTML($html);
                $pdfContents[] = $mpdf->Output('', 'S');
            } catch (\Exception $e) {
                Log::warning("PDF generation error for chunk {$index}: " . $e->getMessage());
                $simpleHtml = $this->generateSimpleHTML($chunk);
                $mpdf->WriteHTML($simpleHtml);
                $pdfContents[] = $mpdf->Output('', 'S');
            }

            // Free memory
            unset($mpdf);
            gc_collect_cycles();
        }

        if (count($pdfContents) === 1) {
            return $pdfContents[0];
        }

        return $this->mergePdfs($pdfContents);
    }

    /**
     * Create mPDF instance
     */
    private function createMpdfInstance(): Mpdf
    {
        return new Mpdf([
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'default_font' => 'dejavusans',
            'tempDir' => storage_path('temp/mpdf'),
            'mode' => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'cache' => true,
            'debug' => false,
        ]);
    }

    /**
     * Merge PDFs
     */
    private function mergePdfs(array $pdfContents): string
    {
        $mpdf = $this->createMpdfInstance();

        foreach ($pdfContents as $index => $content) {
            if ($index === 0) {
                $mpdf->WriteHTML($this->extractHtmlFromPdf($content));
            } else {
                $mpdf->AddPage();
                $mpdf->WriteHTML($this->extractHtmlFromPdf($content));
            }
        }

        $result = $mpdf->Output('', 'S');
        unset($mpdf);
        
        return $result;
    }

    /**
     * Extract HTML from PDF (simplified)
     */
    private function extractHtmlFromPdf(string $pdfContent): string
    {
        // In production, use a proper PDF parser library
        return $pdfContent;
    }

    /**
     * Prepare data for PDF
     */
    private function prepareData(Collection $items): array
    {
        $totalAmount = $items->sum('price');
        $totalQuantity = $items->sum('quantity');
        $totalDamaged = $items->sum('damaged_quantity');
        $totalAvailable = $items->sum(function ($item) {
            return ($item->quantity ?? 0) - ($item->damaged_quantity ?? 0);
        });

        return [
            'items' => $items,
            'totalItems' => $items->count(),
            'totalAmount' => $totalAmount,
            'totalQuantity' => $totalQuantity,
            'totalDamaged' => $totalDamaged,
            'totalAvailable' => $totalAvailable,
            'showPrice' => $this->requestData['show_price'] ?? true,
            'showLogo' => $this->requestData['show_logo'] ?? false,
            'showDescription' => $this->requestData['show_description'] ?? false,
            'logoPath' => $this->getLogoPath(),
            'generatedDate' => now()->format('d M Y H:i'),
            'currencySymbol' => config('app.currency_symbol', '$'),
            'appName' => config('app.name', 'Inventory'),
        ];
    }

    /**
     * Generate optimized HTML
     */
    private function generateOptimizedHTML(array $data): string
    {
        extract($data);
        
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
        $html .= $this->getMinifiedCss();
        $html .= '</style></head><body>';
        
        // Header
        $html .= '<div class="header">';
        $html .= '<div><div class="title">' . htmlspecialchars($appName) . '</div>';
        $html .= '<div class="subtitle">Items Report • ' . $generatedDate . '</div></div>';
        if ($showLogo && $logoPath) {
            $html .= '<img src="' . $logoPath . '" class="logo" alt="Logo">';
        }
        $html .= '</div>';

        // Items Table
        $html .= $this->renderItemsTableOptimized($items, $showPrice, $currencySymbol);

        // Summary
        $html .= $this->renderSummaryOptimized($data);

        // Footer
        $html .= '<div class="footer">© ' . date('Y') . ' ' . htmlspecialchars($appName) . ' | All Rights Reserved</div>';
        
        $html .= '</body></html>';
        return $html;
    }

    /**
     * Render items table optimized
     */
    private function renderItemsTableOptimized(Collection $items, bool $showPrice, string $currency): string
    {
        if ($items->isEmpty()) {
            return '<p style="text-align:center;padding:20px 0;color:#8b8371;">No items found.</p>';
        }

        $html = '<table>';
        $html .= '<thead><tr>';
        $html .= '<th style="width:30px">#</th>';
        $html .= '<th style="width:70px">SKU</th>';
        $html .= '<th>Name</th>';
        $html .= '<th style="width:80px">Category</th>';
        $html .= '<th style="width:50px">Stock</th>';
        if ($showPrice) {
            $html .= '<th style="width:60px;text-align:right">Price</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($items as $index => $item) {
            $available = ($item->quantity ?? 0) - ($item->damaged_quantity ?? 0);
            $stockClass = $available <= 0 ? 'stock-out' : ($available < 5 ? 'stock-low' : 'stock-ok');
            $rowClass = $index % 2 === 0 ? 'even' : '';

            $html .= '<tr class="' . $rowClass . '">';
            $html .= '<td>' . ($index + 1) . '</td>';
            $html .= '<td style="font-size:8px;color:#8b8371">' . htmlspecialchars($item->sku ?? 'N/A') . '</td>';
            $html .= '<td>' . htmlspecialchars($item->name) . '</td>';
            $html .= '<td><span class="badge">' . htmlspecialchars($item->category->name ?? 'Uncategorized') . '</span></td>';
            $html .= '<td><span class="stock-status ' . $stockClass . '">' . $available . '</span></td>';
            if ($showPrice) {
                $html .= '<td style="text-align:right" class="price">' . $currency . number_format($item->price ?? 0, 2) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Render summary optimized
     */
    private function renderSummaryOptimized(array $data): string
    {
        $html = '<div class="summary">';
        $html .= '<div style="font-weight:600;font-size:11px;margin-bottom:8px;">Summary</div>';
        $html .= '<div class="summary-grid">';
        $html .= '<div class="summary-item"><div class="summary-label">Total Items</div><div class="summary-value">' . $data['totalItems'] . '</div></div>';
        $html .= '<div class="summary-item"><div class="summary-label">Available Stock</div><div class="summary-value">' . $data['totalAvailable'] . '</div></div>';
        
        if ($data['showPrice']) {
            $html .= '<div class="summary-item"><div class="summary-label">Total Value</div><div class="summary-value">' . $data['currencySymbol'] . number_format($data['totalAmount'], 2) . '</div></div>';
        }
        
        $html .= '</div></div>';
        return $html;
    }

    /**
     * Get minified CSS
     */
    private function getMinifiedCss(): string
    {
        return 'body{font-family:dejavusans,Arial,sans-serif;font-size:9px;color:#1c1a16;margin:0;padding:12px}
        .header{border-bottom:2px solid #a9812f;padding-bottom:8px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center}
        .title{font-size:20px;font-weight:700;color:#1c1a16;margin:0}
        .subtitle{color:#8b8371;font-size:9px}
        table{width:100%;border-collapse:collapse;margin-top:8px;font-size:8px}
        th{background:#1c1a16;color:#fff;padding:5px 6px;text-align:left}
        td{padding:4px 6px;border-bottom:1px solid #e4dcc7}
        .even{background:#faf6ec}
        .price{font-weight:600;color:#a9812f;white-space:nowrap}
        .stock-status{display:inline-block;padding:1px 5px;border-radius:3px;font-size:7px;font-weight:600}
        .stock-ok{background:#4d7a5c;color:#fff}
        .stock-low{background:#a9812f;color:#fff}
        .stock-out{background:#b34a3c;color:#fff}
        .summary{margin-top:12px;padding:10px;background:#faf6ec;border-radius:4px}
        .summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:6px}
        .summary-item{text-align:center;padding:5px;background:#fff;border-radius:4px;border:1px solid #e4dcc7}
        .summary-label{font-size:6px;color:#8b8371;text-transform:uppercase}
        .summary-value{font-size:12px;font-weight:700;color:#1c1a16}
        .footer{margin-top:15px;border-top:1px solid #e4dcc7;padding-top:6px;font-size:6px;color:#8b8371;text-align:center}
        .badge{display:inline-block;background:#faf6ec;padding:1px 5px;border-radius:3px;font-size:7px}
        .logo{max-height:30px}
        @page{margin:8px}';
    }

    /**
     * Generate simple HTML fallback
     */
    private function generateSimpleHTML(Collection $items): string
    {
        $html = '<html><head><style>
            body{font-family:Arial,sans-serif;margin:15px;font-size:10px}
            h1{color:#1c1a16;border-bottom:2px solid #a9812f;padding-bottom:8px}
            table{width:100%;border-collapse:collapse;margin-top:10px}
            th{background:#1c1a16;color:#fff;padding:6px;text-align:left}
            td{padding:4px 6px;border-bottom:1px solid #e4dcc7}
        </style></head><body>';
        
        $html .= '<h1>Items Report</h1>';
        $html .= '<p>Generated: ' . now()->format('d M Y H:i') . '</p>';
        $html .= '<p>Total Items: ' . $items->count() . '</p>';
        
        if ($items->isNotEmpty()) {
            $html .= '<table>';
            $html .= '<tr><th>#</th><th>SKU</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th></tr>';
            
            foreach ($items as $index => $item) {
                $available = ($item->quantity ?? 0) - ($item->damaged_quantity ?? 0);
                $html .= '<tr>';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td>' . htmlspecialchars($item->sku ?? 'N/A') . '</td>';
                $html .= '<td>' . htmlspecialchars($item->name) . '</td>';
                $html .= '<td>' . htmlspecialchars($item->category->name ?? 'Uncategorized') . '</td>';
                $html .= '<td>' . config('app.currency_symbol', '$') . number_format($item->price ?? 0, 2) . '</td>';
                $html .= '<td>' . $available . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }
        
        $html .= '<p style="margin-top:20px;font-size:8px;color:#8b8371;text-align:center">© ' . date('Y') . ' ' . config('app.name') . ' | All Rights Reserved</p>';
        $html .= '</body></html>';
        
        return $html;
    }

    /**
     * Store PDF in filesystem
     */
    private function storePdfInFilesystem(string $pdfContent, Collection $items): void
    {
        $filename = $this->jobId . '.pdf';
        $path = self::PDF_STORAGE_PATH . $filename;
        
        // Store the PDF file
        Storage::disk('local')->put($path, $pdfContent);

        // Store metadata in cache (small data)
        $data = [
            'status' => 'completed',
            'job_id' => $this->jobId,
            'filename' => 'items-report.pdf',
            'storage_path' => $path,
            'items_count' => $items->count(),
            'total_amount' => $items->sum('price'),
            'total_quantity' => $items->sum('quantity'),
            'total_available' => $items->sum(function ($item) {
                return ($item->quantity ?? 0) - ($item->damaged_quantity ?? 0);
            }),
            'size_bytes' => strlen($pdfContent),
            'generated_at' => now()->toDateTimeString(),
            'expires_at' => now()->addHours(2)->toDateTimeString(),
        ];

        Cache::put("pdf_job_{$this->jobId}", $data, self::CACHE_TTL);
        
        // Track job key for cleanup
        $this->trackJobKey($this->jobId);
    }

    /**
     * Track job key for cleanup
     */
    private function trackJobKey(string $jobId): void
    {
        $keys = Cache::get('pdf_job_keys', []);
        if (!in_array($jobId, $keys)) {
            $keys[] = $jobId;
            Cache::put('pdf_job_keys', $keys, 86400); // 24 hours
        }
    }

    /**
     * Get logo path
     */
    private function getLogoPath(): string
    {
        if (isset($this->requestData['logo']) && is_string($this->requestData['logo'])) {
            return $this->requestData['logo'];
        }
        
        if (isset($this->requestData['logo_url'])) {
            return $this->requestData['logo_url'];
        }
        
        return $this->getDefaultLogo();
    }

    /**
     * Get default logo
     */
    private function getDefaultLogo(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 80">
            <rect width="200" height="80" fill="#1c1a16" rx="4"/>
            <text x="100" y="48" font-family="Georgia, serif" font-size="28" fill="#a9812f" text-anchor="middle" font-weight="600">MOROVSKI</text>
        </svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Update status in cache
     */
    private function updateStatus(string $status, string $message = ''): void
    {
        $data = [
            'status' => $status,
            'message' => $message,
            'job_id' => $this->jobId,
            'updated_at' => now()->toDateTimeString(),
        ];

        Cache::put("pdf_job_{$this->jobId}", $data, self::CACHE_TTL);
    }

    /**
     * Send callback to webhook URL
     */
    private function sendCallback(string $status, string $message = '', ?string $pdfContent = null, ?Collection $items = null): void
    {
        if (!$this->callbackUrl) {
            return;
        }

        try {
            $payload = [
                'job_id' => $this->jobId,
                'status' => $status,
                'message' => $message,
                'completed_at' => now()->toDateTimeString(),
            ];

            if ($status === 'completed' && $items) {
                $payload['items_count'] = $items->count();
                $payload['total_amount'] = $items->sum('price');
                $payload['download_url'] = url("/api/pdf/download/{$this->jobId}");
                
                if ($pdfContent && strlen($pdfContent) < 5 * 1024 * 1024) {
                    $payload['pdf_base64'] = base64_encode($pdfContent);
                }
            }

            Http::timeout(30)
                ->retry(3, 100)
                ->post($this->callbackUrl, $payload);

        } catch (\Exception $e) {
            Log::warning("Callback failed for job {$this->jobId}: " . $e->getMessage());
        }
    }

    /**
     * Handle error
     */
    private function handleError(string $message): void
    {
        $this->updateStatus('failed', $message);
        $this->sendCallback('failed', $message);
    }
}