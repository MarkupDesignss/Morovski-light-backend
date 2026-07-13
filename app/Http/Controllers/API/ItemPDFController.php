<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Address;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class ItemPDFController extends Controller
{
    /**
     * Generate PDF as downloadable file
     */
    public function generatePDF(Request $request)
    {
        try {
            Log::info('PDF Generation Request', [
                'item_ids' => $request->item_ids,
                'all' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'item_ids' => 'required|array|min:1',
                'item_ids.*' => 'integer|exists:items,id',
                'show_price' => 'boolean',
                'show_logo' => 'boolean',
                'show_description' => 'boolean',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'logo_url' => 'nullable|string|max:2048',
                'user_id' => 'nullable|integer|exists:users,id',
                'address_id' => 'nullable|integer|exists:addresses,id',
                'company_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $items = Item::with(['category.parent', 'images'])
                ->whereIn('id', $request->item_ids)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'message' => 'No items found.'
                ], 404);
            }

            $data = $this->prepareData($items, $request);

            $html = View::make('pdf.items', $data)->render();

            $mpdf = new Mpdf([
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'default_font' => 'dejavusans',
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'allow_charset_conversion' => true,
                'charset_in' => 'utf-8',
                'showImageErrors' => false,
                'curlAllowUnsafeSslRequests' => true,
                'tempDir' => storage_path('app/temp'),
                'debug' => false,
            ]);

            // Add custom CSS for better rendering
            $mpdf->WriteHTML($html);

            return response(
                $mpdf->Output('catalogue.pdf', 'S'),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="catalogue.pdf"',
                ]
            );

        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to generate PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF and return as base64
     */
    public function generatePDFBase64(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_ids' => 'required|array|min:1',
                'item_ids.*' => 'integer|exists:items,id',
                'show_price' => 'boolean',
                'show_logo' => 'boolean',
                'show_description' => 'boolean',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'logo_url' => 'nullable|string|max:2048',
                'user_id' => 'nullable|integer|exists:users,id',
                'address_id' => 'nullable|integer|exists:addresses,id',
                'company_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $items = Item::with(['category.parent', 'images'])
                ->whereIn('id', $request->item_ids)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'message' => 'No items found.'
                ], 404);
            }

            $data = $this->prepareData($items, $request);

            $html = View::make('pdf.items', $data)->render();

            $mpdf = new Mpdf([
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'default_font' => 'dejavusans',
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'allow_charset_conversion' => true,
                'charset_in' => 'utf-8',
                'tempDir' => storage_path('app/temp'),
            ]);

            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S');

            return response()->json([
                'success' => true,
                'pdf_base64' => base64_encode($pdfContent),
                'file_name' => 'catalogue.pdf',
                'items_count' => $items->count(),
                'total_amount' => $items->sum('price'),
            ]);

        } catch (\Exception $e) {
            Log::error('PDF Base64 Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to generate PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview PDF in browser
     */
    public function previewPDF(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_ids' => 'required|array|min:1',
                'item_ids.*' => 'integer|exists:items,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $items = Item::with(['category.parent', 'images'])
                ->whereIn('id', $request->item_ids)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'message' => 'No items found.'
                ], 404);
            }

            $data = $this->prepareData($items, $request);
            return view('pdf.items', $data);

        } catch (\Exception $e) {
            Log::error('Preview Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to preview',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prepare data for the view
     */
    private function prepareData($items, Request $request): array
    {
        $logoPath = $this->getLogoPath($request);
        $logoExists = $this->checkLogoExists($logoPath);
        
        $user = null;
        $address = null;
        
        if ($request->filled('user_id')) {
            $user = \App\Models\User::with('addresses')->find($request->user_id);
        } elseif (auth()->check()) {
            $user = auth()->user()->load('addresses');
        }
        
        if ($request->filled('address_id')) {
            $address = Address::find($request->address_id);
        } elseif ($user) {
            $address = $user->addresses()->where('is_default', true)->first();
            if (!$address && $user->addresses->isNotEmpty()) {
                $address = $user->addresses->first();
            }
        }

        $categories = Category::with('items')->get();

        $company = (object) [
            'name' => $request->input('company_name', 'Catalogue'),
            'tagline' => 'Illuminate Spaces, Elevate Style',
            'foreword_title' => 'Light, Crafted Like an Heirloom',
            'foreword_text' => 'Every fixture in this catalogue is designed the way a jeweller sets a stone — with proportion, patience, and an eye for how light will fall across a room for decades to come. Brass is hand-turned, crystal is cut in small batches, and every finish is aged to a warmth that only improves with time.',
            'foreword_text_2' => 'This collection is organised into six worlds: Chandeliers for the room that commands attention, Pendants for the table that gathers a family, Wall Lights for the hallway that welcomes a guest, Table and Floor Lamps for quiet corners, and Outdoor Lights for the threshold that greets them all.',
            'signature' => 'The Design Atelier',
            'established' => 'EST. 1998',
            'established_text' => 'Handcrafted Since 1998',
            'address' => '42 Heritage Court, Greater Noida, Uttar Pradesh, India',
            'email' => 'support@morovski.com',
            'phone' => '+91 98XXX XXXXX',
            'website' => 'www.morovskilight.in',
            'social' => '@Morvoski.lights',
            'categories' => 'Chandeliers · Pendants · Wall · Table · Floor · Outdoor',
        ];

        return [
            'items' => $items,
            'totalItems' => $items->count(),
            'totalAmount' => $items->sum('price'),
            'showPrice' => $request->boolean('show_price', true),
            'showLogo' => $request->boolean('show_logo', true),
            'showDescription' => $request->boolean('show_description', false),
            'logoPath' => $logoPath,
            'logoExists' => $logoExists,
            'generatedDate' => now()->format('d M Y H:i'),
            'user' => $user,
            'address' => $address,
            'company' => $company,
            'categories' => $categories,
            'company_name' => $request->input('company_name', 'Catalogue'),
        ];
    }

    private function checkLogoExists($logoPath): bool
    {
        if (!$logoPath) return false;
        if (strpos($logoPath, 'data:') === 0) return true;
        if (filter_var($logoPath, FILTER_VALIDATE_URL)) return true;
        if (file_exists(public_path($logoPath))) return true;
        if (file_exists(storage_path('app/public/' . str_replace('storage/', '', $logoPath)))) return true;
        return false;
    }

    private function getLogoPath(Request $request): string
    {
        if ($request->hasFile('logo')) {
            try {
                $file = $request->file('logo');
                $filename = 'logo_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('logos', $filename, 'public');
                
                $fullPublicPath = public_path('storage/logos/' . $filename);
                if (file_exists($fullPublicPath)) {
                    $imageContent = file_get_contents($fullPublicPath);
                    $mimeType = mime_content_type($fullPublicPath) ?: 'image/png';
                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                }
                return Storage::url($path);
            } catch (\Exception $e) {
                return '';
            }
        }

        if ($request->filled('logo_url')) {
            $logoUrl = $request->input('logo_url');
            if (filter_var($logoUrl, FILTER_VALIDATE_URL)) {
                try {
                    $imageContent = @file_get_contents($logoUrl);
                    if ($imageContent !== false) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_buffer($finfo, $imageContent);
                        finfo_close($finfo);
                        return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                    }
                    return $logoUrl;
                } catch (\Exception $e) {
                    return $logoUrl;
                }
            }
            return $logoUrl;
        }

        return '';
    }
}