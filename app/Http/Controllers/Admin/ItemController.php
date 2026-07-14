<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    public function shareProduct($slug)
    {
        try {
            $item = Item::with(['images', 'category'])
                ->where('slug', $slug)
                ->firstOrFail();

            $firstImage = $item->images->first();
            $imagePath = null;
            $ogImage = null;

            if ($firstImage) {
                $imagePath = $firstImage->image_url ?? $firstImage->image;

                if (!empty($imagePath)) {
                    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                        $ogImage = $imagePath;
                    } else {
                        $imagePath = ltrim($imagePath, '/');
                        if (strpos($imagePath, 'items/') === 0) {
                            $ogImage = asset('storage/' . $imagePath);
                        } elseif (strpos($imagePath, 'storage/') === 0) {
                            $ogImage = asset($imagePath);
                        } else {
                            $ogImage = asset($imagePath);
                        }
                    }
                }
            }

            if (!$ogImage) {
                $ogImage = asset('images/default-product.jpg');
            }

            $imageInfo = @getimagesize(str_replace('https://', 'http://', $ogImage));
            $imageWidth = $imageInfo ? $imageInfo[0] : 1200;
            $imageHeight = $imageInfo ? $imageInfo[1] : 630;
            $imageType = $imageInfo ? $imageInfo['mime'] : 'image/jpeg';

            $ogImage = str_replace('http://', 'https://', $ogImage);

            return response()
                ->view('admin.items.product_share', [
                    'item' => $item,
                    'ogImage' => $ogImage,
                    'imageType' => $imageType,
                    'imageWidth' => $imageWidth,
                    'imageHeight' => $imageHeight,
                ])
                ->header('Cache-Control', 'public, max-age=3600');
        } catch (\Exception $e) {
            \Log::error('Share Product Error', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(404);
        }
    }

    public function index(Request $request)
    {
        $query = Item::with('images')->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->stock) {
            if ($request->stock === 'in_stock') {
                $query->where('quantity', '>', 20);
            } elseif ($request->stock === 'low_stock') {
                $query->whereBetween('quantity', [1, 20]);
            } elseif ($request->stock === 'out_of_stock') {
                $query->where('quantity', '<=', 0);
            }
        }
        if ($request->type) {
            if (in_array($request->type, ['online', 'offline'])) {
                $query->where('type', $request->type);
            }
        }
        $totalItems = Item::count();

        $items = $query->paginate(10);

        return view('admin.items.index', compact('items', 'totalItems'));
    }

    public function create()
    {
        $categories = Category::all();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('admin.items.create', compact('categories', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required|string',
            'model' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp',
            'warehouse_quantities' => 'required|array',
            'warehouse_quantities.*' => 'integer|min:0',
        ]);

        $specs = [];
        if ($request->spec_key) {
            foreach ($request->spec_key as $i => $key) {
                if ($key) {
                    $specs[$key] = $request->spec_value[$i] ?? null;
                }
            }
        }

        $totalQuantity = array_sum($request->warehouse_quantities);

        $slug = Str::slug($request->name);
        if (Item::where('slug', $slug)->exists()) {
            return redirect()->back()->withInput()->withErrors(['name' => 'Item with same slug already exists.']);
        }

        $item = Item::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'slug' => $slug,
            'sku' => $request->sku,
            'is_featured' => $request->is_featured ?? 0,
            'price' => $request->price,
            'quantity' => $totalQuantity,
            'type' => $request->type,
            'model' => $request->model,
            'description' => $request->description,
            'specifications' => $specs
        ]);

        foreach ($request->warehouse_quantities as $warehouseId => $qty) {
            WarehouseItem::create([
                'warehouse_id' => $warehouseId,
                'item_id' => $item->id,
                'quantity' => $qty,
                'reserved_quantity' => 0,
            ]);
        }

        //  UPLOAD IMAGES – COMPRESSED FOR PDF
        if ($request->hasFile('images')) {
            $manager = new ImageManager(new Driver());
            $uploadDir = public_path('storage/items/');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            foreach ($request->file('images') as $img) {
                $extension = strtolower($img->getClientOriginalExtension());
                $fileName = time() . '_' . uniqid() . '.' . $extension;
                $fullPath = $uploadDir . $fileName;

                try {
                    if ($img->getSize() <= 100 * 1024) {
                        // Small image – save as original
                        $img->move($uploadDir, $fileName);
                    } else {
                        $image = $manager->read($img->getRealPath());
                        // PDF के लिए 800px और 60% quality
                        $image->scale(800, 800);

                        switch ($extension) {
                            case 'jpg':
                            case 'jpeg':
                                $encoded = $image->toJpeg(60);
                                break;
                            case 'png':
                                $encoded = $image->toPng();
                                break;
                            case 'webp':
                                $encoded = $image->toWebp(60);
                                break;
                            default:
                                $encoded = $image->encode();
                        }
                        $encoded->save($fullPath);
                    }

                    ItemImage::create([
                        'item_id' => $item->id,
                        'image' => 'items/' . $fileName
                    ]);
                } catch (\Exception $e) {
                    // Fallback – save original
                    $img->move($uploadDir, $fileName);
                    ItemImage::create([
                        'item_id' => $item->id,
                        'image' => 'items/' . $fileName
                    ]);
                    Log::error('Image optimization failed', [
                        'file' => $fileName,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return redirect()->route('admin.items.index')->with('success', 'Item created with warehouse stocks');
    }

    public function show($id)
    {
        $item = Item::with('images')->findOrFail($id);
        return view('admin.items.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Item::with('images')->findOrFail($id);
        $categories = Category::all();
        $warehouses = Warehouse::where('is_active', true)->get();
        $warehouseStocks = $item->warehouseItems->keyBy('warehouse_id');

        return view('admin.items.edit', compact('item', 'categories', 'warehouses', 'warehouseStocks'));
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        $request->validate([
            'name' => 'required',
            'type' => 'required|string',
            'model' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp',
            'warehouse_quantities' => 'required|array',
            'warehouse_quantities.*' => 'integer|min:0',
        ]);

        $specs = [];
        if ($request->spec_key) {
            foreach ($request->spec_key as $i => $key) {
                if ($key) {
                    $specs[$key] = $request->spec_value[$i] ?? null;
                }
            }
        }

        $totalQuantity = array_sum($request->warehouse_quantities);

        $item->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'sku' => $request->sku,
            'is_featured' => $request->is_featured ?? 0,
            'price' => $request->price,
            'slug' => Str::slug($request->name),
            'quantity' => $totalQuantity,
            'type' => $request->type,
            'model' => $request->model ?? $item->model,
            'description' => $request->description,
            'specifications' => $specs,
        ]);

        foreach ($request->warehouse_quantities as $warehouseId => $qty) {
            WarehouseItem::updateOrCreate(
                ['warehouse_id' => $warehouseId, 'item_id' => $item->id],
                ['quantity' => $qty]
            );
        }

        $submittedWarehouses = array_keys($request->warehouse_quantities);
        $item->warehouseItems()->whereNotIn('warehouse_id', $submittedWarehouses)->delete();

        // UPLOAD NEW IMAGES – COMPRESSED FOR PDF
        if ($request->hasFile('images')) {
            $manager = new ImageManager(new Driver());
            $uploadDir = public_path('storage/items/');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            foreach ($request->file('images') as $img) {
                $extension = strtolower($img->getClientOriginalExtension());
                $fileName = time() . '_' . uniqid() . '.' . $extension;
                $fullPath = $uploadDir . $fileName;

                try {
                    if ($img->getSize() <= 100 * 1024) {
                        $img->move($uploadDir, $fileName);
                    } else {
                        $image = $manager->read($img->getRealPath());
                        $image->scale(800, 800);

                        switch ($extension) {
                            case 'jpg':
                            case 'jpeg':
                                $encoded = $image->toJpeg(60);
                                break;
                            case 'png':
                                $encoded = $image->toPng();
                                break;
                            case 'webp':
                                $encoded = $image->toWebp(60);
                                break;
                            default:
                                $encoded = $image->encode();
                        }
                        $encoded->save($fullPath);
                    }

                    ItemImage::create([
                        'item_id' => $item->id,
                        'image' => 'items/' . $fileName
                    ]);
                } catch (\Exception $e) {
                    $img->move($uploadDir, $fileName);
                    ItemImage::create([
                        'item_id' => $item->id,
                        'image' => 'items/' . $fileName
                    ]);
                    Log::error('Image optimization failed', [
                        'file' => $fileName,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return redirect()->route('admin.items.index')
            ->with('success', 'Item updated successfully');
    }

    public function destroy($id)
    {
        $item = Item::with('images')->findOrFail($id);
        foreach ($item->images as $img) {
            Storage::disk('public')->delete($img->image);
        }
        $item->delete();
        return back()->with('success', 'Item deleted');
    }

    public function deleteImage($id)
    {
        $image = ItemImage::findOrFail($id);
        Storage::disk('public')->delete($image->image);
        $image->delete();
        return back()->with('success', 'Image deleted');
    }

    // ========== CSV IMPORT METHODS ==========

    public function showImportForm()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('admin.items.import', compact('warehouses'));
    }

    public function importChunk(Request $request)
    {
        Log::info('Chunk received', [
            'chunk_index' => $request->chunk_index,
            'total_chunks' => $request->total_chunks,
            'token' => $request->upload_token
        ]);

        try {
            $request->validate([
                'chunk'            => 'required|string',
                'chunk_index'      => 'required|integer',
                'total_chunks'     => 'required|integer',
                'upload_token'     => 'required|string',
                'original_name'    => 'required|string',
                'chunk_size_lines' => 'required|integer|min:1',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Validation failed: ' . $e->getMessage()
            ], 422);
        }

        $token = $request->upload_token;
        $tempDir = storage_path('app/temp_imports');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $tempFile = $tempDir . '/' . $token . '.csv';

        if ($request->chunk_index == 0) {
            File::put($tempFile, $request->chunk);
        } else {
            File::append($tempFile, $request->chunk);
        }

        if ($request->chunk_index == $request->total_chunks - 1) {
            $imported = 0;
            $updated = 0;
            $importErrors = [];
            $validationErrors = [];
            $offset = $request->chunk_index * $request->chunk_size_lines;

            try {
                $this->processCsvFile($tempFile, $imported, $updated, $importErrors, $offset, $validationErrors);
                File::delete($tempFile);

                Log::info('Chunked import completed', [
                    'created' => $imported,
                    'updated' => $updated
                ]);

                return response()->json([
                    'success'  => true,
                    'message'  => "Imported: {$imported}, Updated: {$updated}",
                    'warnings' => $importErrors
                ]);
            } catch (\Exception $e) {
                File::delete($tempFile);
                Log::error('Chunk processing failed', ['error' => $e->getMessage()]);
                if (!empty($validationErrors)) {
                    return response()->json([
                        'success' => false,
                        'error'   => 'Validation errors occurred.',
                        'validation_errors' => $validationErrors
                    ], 422);
                }
                return response()->json([
                    'success' => false,
                    'error'   => 'Processing failed: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json(['success' => true, 'message' => 'Chunk received']);
    }

    protected function processCsvFile($filePath, &$imported, &$updated, &$importErrors, $offset = 0, &$validationErrors = [])
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Cannot open file: {$filePath}");
        }

        $headers = fgetcsv($handle);
        if (isset($headers[0])) {
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        }
        Log::info('Processing CSV headers', ['headers' => $headers]);

        $validRows = [];
        $errors = [];
        $rowNumber = 1;
        $skuSeen = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $globalRow = $rowNumber + $offset;

            if (empty($row) || (count($row) == 1 && trim($row[0]) === '')) {
                continue;
            }

            if (count($headers) !== count($row)) {
                $errors[] = "Row {$globalRow}: Column count mismatch (expected " . count($headers) . ", got " . count($row) . ")";
                continue;
            }

            $data = array_combine($headers, $row);
            $rowErrors = [];

            $required = ['name', 'category', 'type', 'price', 'warehouses'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $rowErrors[] = "Missing required field: {$field}";
                }
            }

            if (!empty($data['price']) && !is_numeric($data['price'])) {
                $rowErrors[] = "Price must be numeric";
            }

            $category = null;
            if (!empty($data['category'])) {
                $category = Category::where('name', $data['category'])->first();
                if (!$category) {
                    $rowErrors[] = "Category '{$data['category']}' not found in database";
                }
            } else {
                $rowErrors[] = "Category is required";
            }

            if (!empty($data['warehouses'])) {
                $parts = explode(',', $data['warehouses']);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if (strpos($part, ':') === false) {
                        $rowErrors[] = "Invalid warehouse format: {$part}. Expected CODE:QUANTITY";
                    } else {
                        [$code, $qty] = explode(':', $part);
                        if (!Warehouse::where('code', $code)->exists()) {
                            $rowErrors[] = "Warehouse code '{$code}' not found";
                        }
                    }
                }
            } else {
                $rowErrors[] = "Warehouses column required";
            }

            if (!empty($data['images'])) {
                $imageUrls = array_map('trim', explode(',', $data['images']));
                foreach ($imageUrls as $url) {
                    if (empty($url)) {
                        $rowErrors[] = "Empty image value found";
                    }
                }
            }

            if (!empty($data['sku'])) {
                $sku = trim($data['sku']);
                if (in_array($sku, $skuSeen)) {
                    $rowErrors[] = "Duplicate SKU found in CSV: '{$sku}'.";
                } else {
                    $skuSeen[] = $sku;
                }
            }

            if (empty($rowErrors)) {
                $validRows[] = [
                    'data'      => $data,
                    'category'  => $category,
                    'globalRow' => $globalRow,
                ];
            } else {
                foreach ($rowErrors as $err) {
                    $errors[] = "Row {$globalRow}: {$err}";
                }
            }
        }
        fclose($handle);

        if (!empty($errors)) {
            $validationErrors = $errors;
            $errorMsg = "Validation errors:\n" . implode("\n", $errors);
            throw new \Exception($errorMsg);
        }

        DB::beginTransaction();
        try {
            foreach ($validRows as $validRow) {
                $data = $validRow['data'];
                $category = $validRow['category'];
                $globalRow = $validRow['globalRow'];

                $warehouseQtys = [];
                $parts = explode(',', $data['warehouses']);
                foreach ($parts as $part) {
                    [$code, $qty] = explode(':', trim($part));
                    $warehouse = Warehouse::where('code', $code)->first();
                    if ($warehouse) {
                        $warehouseQtys[$warehouse->id] = (int) $qty;
                    }
                }

                $specs = [];
                if (!empty($data['specifications'])) {
                    $pairs = explode(';', $data['specifications']);
                    foreach ($pairs as $pair) {
                        if (strpos($pair, ':') !== false) {
                            [$k, $v] = explode(':', $pair);
                            $specs[trim($k)] = trim($v);
                        }
                    }
                }

                $totalQuantity = array_sum($warehouseQtys);

                $item = null;
                if (!empty($data['sku'])) {
                    $item = Item::where('sku', $data['sku'])->first();
                }

                $baseSlug = Str::slug($data['name']);
                $slug = $baseSlug;
                $counter = 1;
                if ($item) {
                    while (Item::where('slug', $slug)->where('id', '!=', $item->id)->exists()) {
                        $slug = $baseSlug . '-' . $counter++;
                    }
                } else {
                    while (Item::where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $counter++;
                    }
                }

                $isFeatured = 0;
                if (!empty($data['is_featured'])) {
                    $val = strtolower(trim($data['is_featured']));
                    if (in_array($val, ['yes', 'true', '1', 'best seller'])) {
                        $isFeatured = 1;
                    }
                }

                if ($item) {
                    $item->update([
                        'name'           => $data['name'],
                        'category_id'    => $category->id,
                        'slug'           => $slug,
                        'price'          => (float) $data['price'],
                        'type'           => $data['type'],
                        'quantity'       => $totalQuantity,
                        'is_featured'    => $isFeatured,
                        'description'    => $data['description'] ?? null,
                        'specifications' => $specs,
                    ]);
                    $item->warehouseItems()->delete();
                    foreach ($warehouseQtys as $whId => $qty) {
                        WarehouseItem::create([
                            'warehouse_id'      => $whId,
                            'item_id'           => $item->id,
                            'quantity'          => $qty,
                            'reserved_quantity' => 0,
                            'damaged_quantity'  => 0,
                        ]);
                    }
                    if (!empty($data['images'])) {
                        $imageUrls = array_map('trim', explode(',', $data['images']));
                        foreach ($imageUrls as $url) {
                            $this->attachImageFromUrl($item->id, $url, $importErrors, $globalRow);
                        }
                    }
                    $updated++;
                } else {
                    $item = Item::create([
                        'name'           => $data['name'],
                        'category_id'    => $category->id,
                        'slug'           => $slug,
                        'sku'            => $data['sku'] ?? null,
                        'price'          => (float) $data['price'],
                        'type'           => $data['type'],
                        'quantity'       => $totalQuantity,
                        'is_featured'    => $isFeatured,
                        'description'    => $data['description'] ?? null,
                        'specifications' => $specs,
                    ]);
                    foreach ($warehouseQtys as $whId => $qty) {
                        WarehouseItem::create([
                            'warehouse_id'      => $whId,
                            'item_id'           => $item->id,
                            'quantity'          => $qty,
                            'reserved_quantity' => 0,
                            'damaged_quantity'  => 0,
                        ]);
                    }
                    if (!empty($data['images'])) {
                        $imageUrls = array_map('trim', explode(',', $data['images']));
                        foreach ($imageUrls as $url) {
                            $this->attachImageFromUrl($item->id, $url, $importErrors, $globalRow);
                        }
                    }
                    $imported++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function importCsv(Request $request)
    {
        Log::info('=== CSV IMPORT STARTED ===', [
            'file_name' => $request->file('csv_file')?->getClientOriginalName(),
            'file_size' => $request->file('csv_file')?->getSize(),
            'timestamp' => now()->toDateTimeString(),
            'user_id'   => auth()->id()
        ]);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120'
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        $headers = fgetcsv($handle);

        Log::info('CSV Headers', ['headers' => $headers]);

        $validRows = [];
        $errors = [];
        $rowNumber = 1;
        $skuSeen = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $data = array_combine($headers, $row);
            $rowErrors = [];

            $required = ['name', 'category', 'type', 'price', 'warehouses'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $rowErrors[] = "Missing required field: {$field}";
                }
            }

            if (!empty($data['price']) && !is_numeric($data['price'])) {
                $rowErrors[] = "Price must be numeric";
            }

            $category = null;
            if (!empty($data['category'])) {
                $category = Category::where('name', $data['category'])->first();
                if (!$category) {
                    $rowErrors[] = "Category '{$data['category']}' not found in database";
                }
            } else {
                $rowErrors[] = "Category is required";
            }

            if (!empty($data['warehouses'])) {
                $parts = explode(',', $data['warehouses']);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if (strpos($part, ':') === false) {
                        $rowErrors[] = "Invalid warehouse format: {$part}. Expected CODE:QUANTITY";
                    } else {
                        [$code, $qty] = explode(':', $part);
                        if (!Warehouse::where('code', $code)->exists()) {
                            $rowErrors[] = "Warehouse code '{$code}' not found";
                        }
                    }
                }
            } else {
                $rowErrors[] = "Warehouses column required";
            }

            if (!empty($data['images'])) {
                $imageUrls = array_map('trim', explode(',', $data['images']));
                foreach ($imageUrls as $url) {
                    if (empty($url)) {
                        $rowErrors[] = "Empty image value found";
                    }
                }
            }

            if (!empty($data['sku'])) {
                $sku = trim($data['sku']);
                if (in_array($sku, $skuSeen)) {
                    $rowErrors[] = "Duplicate SKU found in CSV: '{$sku}'.";
                } else {
                    $skuSeen[] = $sku;
                }
            }

            if (empty($rowErrors)) {
                $validRows[] = [
                    'data'     => $data,
                    'category' => $category,
                ];
            } else {
                Log::warning("Row {$rowNumber} validation failed", [
                    'errors' => $rowErrors,
                    'data'   => $data
                ]);
                foreach ($rowErrors as $err) {
                    $errors[] = "Row {$rowNumber}: {$err}";
                }
            }
        }

        fclose($handle);

        Log::info('Validation summary', [
            'valid_rows'   => count($validRows),
            'error_rows'   => count($errors),
            'total_rows'   => $rowNumber - 1
        ]);

        if (!empty($errors)) {
            Log::error('CSV validation failed', ['errors' => $errors]);
            return redirect()->route('admin.items.index')
                ->with('error', 'CSV validation failed. No items were imported.')
                ->with('errors', $errors);
        }

        $imported = 0;
        $updated = 0;
        $importErrors = [];

        DB::beginTransaction();
        Log::info('Database transaction started');

        try {
            foreach ($validRows as $validRow) {
                $data = $validRow['data'];
                $category = $validRow['category'];

                $warehouseQtys = [];
                $parts = explode(',', $data['warehouses']);
                foreach ($parts as $part) {
                    [$code, $qty] = explode(':', trim($part));
                    $warehouse = Warehouse::where('code', $code)->first();
                    if ($warehouse) {
                        $warehouseQtys[$warehouse->id] = (int) $qty;
                    }
                }

                $specs = [];
                if (!empty($data['specifications'])) {
                    $pairs = explode(';', $data['specifications']);
                    foreach ($pairs as $pair) {
                        if (strpos($pair, ':') !== false) {
                            [$k, $v] = explode(':', $pair);
                            $specs[trim($k)] = trim($v);
                        }
                    }
                }

                $totalQuantity = array_sum($warehouseQtys);

                $item = null;
                if (!empty($data['sku'])) {
                    $item = Item::where('sku', $data['sku'])->first();
                }

                $baseSlug = Str::slug($data['name']);
                $slug = $baseSlug;
                $counter = 1;

                if ($item) {
                    while (Item::where('slug', $slug)->where('id', '!=', $item->id)->exists()) {
                        $slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }
                } else {
                    while (Item::where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }
                }

                $isFeatured = 0;
                if (!empty($data['is_featured'])) {
                    $val = strtolower(trim($data['is_featured']));
                    if (in_array($val, ['yes', 'true', '1', 'best seller'])) {
                        $isFeatured = 1;
                    }
                }

                if ($item) {
                    Log::info('Updating existing item', [
                        'id'  => $item->id,
                        'sku' => $data['sku']
                    ]);

                    $item->update([
                        'name'            => $data['name'],
                        'category_id'     => $category->id,
                        'slug'            => $slug,
                        'price'           => (float) $data['price'],
                        'type'            => $data['type'],
                        'quantity'        => $totalQuantity,
                        'is_featured'     => $isFeatured,
                        'description'     => $data['description'] ?? null,
                        'specifications'  => $specs,
                    ]);

                    $item->warehouseItems()->delete();
                    foreach ($warehouseQtys as $whId => $qty) {
                        WarehouseItem::create([
                            'warehouse_id'      => $whId,
                            'item_id'           => $item->id,
                            'quantity'          => $qty,
                            'reserved_quantity' => 0,
                            'damaged_quantity'  => 0,
                        ]);
                    }

                    if (!empty($data['images'])) {
                        $imageUrls = array_map('trim', explode(',', $data['images']));
                        foreach ($imageUrls as $url) {
                            $this->attachImageFromUrl($item->id, $url, $importErrors, $rowNumber);
                        }
                    }

                    $updated++;
                } else {
                    Log::info('Creating new item', [
                        'name' => $data['name'],
                        'sku'  => $data['sku'] ?? 'N/A'
                    ]);

                    $item = Item::create([
                        'name'            => $data['name'],
                        'category_id'     => $category->id,
                        'slug'            => $slug,
                        'sku'             => $data['sku'] ?? null,
                        'price'           => (float) $data['price'],
                        'type'            => $data['type'],
                        'quantity'        => $totalQuantity,
                        'is_featured'     => $isFeatured,
                        'description'     => $data['description'] ?? null,
                        'specifications'  => $specs,
                    ]);

                    foreach ($warehouseQtys as $whId => $qty) {
                        WarehouseItem::create([
                            'warehouse_id'      => $whId,
                            'item_id'           => $item->id,
                            'quantity'          => $qty,
                            'reserved_quantity' => 0,
                            'damaged_quantity'  => 0,
                        ]);
                    }

                    if (!empty($data['images'])) {
                        $imageUrls = array_map('trim', explode(',', $data['images']));
                        foreach ($imageUrls as $url) {
                            $this->attachImageFromUrl($item->id, $url, $importErrors, $rowNumber);
                        }
                    }

                    $imported++;
                }
            }

            DB::commit();
            Log::info('Transaction committed', [
                'created' => $imported,
                'updated' => $updated
            ]);

            $message = "{$imported} items created, {$updated} items updated successfully.";
            if (!empty($importErrors)) {
                Log::warning('Image import warnings', $importErrors);
                return redirect()->route('admin.items.index')
                    ->with('success', $message)
                    ->with('warnings', $importErrors);
            }

            return redirect()->route('admin.items.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CSV import failed with exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.items.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * UPDATED – Now compresses URL images to 800px JPG with 65% quality
     */
    private function attachImageFromUrl($itemId, $imageInput, &$errors, $rowNumber)
    {
        if (!filter_var($imageInput, FILTER_VALIDATE_URL)) {
            $this->attachImageToItem($itemId, $imageInput, $errors, $rowNumber);
            return;
        }

        $downloadUrl = $this->getGoogleDriveDirectUrl($imageInput);

        try {
            $contents = file_get_contents($downloadUrl);
            if ($contents === false) {
                $errors[] = "Row {$rowNumber}: Failed to download image from URL: {$imageInput}";
                return;
            }

            // COMPRESS USING INTERVENTION
            $manager = new ImageManager(new Driver());
            $image = $manager->read($contents);

            // PDF के लिए 800px और JPEG में बदलें (65% quality)
            $image->scale(800, 800);
            $encoded = $image->toJpeg(65);
            $contents = (string) $encoded; // compressed binary

            // Always store as .jpg
            $hash = md5($imageInput);
            $fileName = $hash . '.jpg';
            $storagePath = 'items/' . $fileName;

            $existing = ItemImage::where('item_id', $itemId)
                ->where('image', $storagePath)
                ->first();
            if ($existing) {
                return;
            }

            if (!Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->put($storagePath, $contents);
            }

            ItemImage::create([
                'item_id' => $itemId,
                'image'   => $storagePath,
            ]);
        } catch (\Exception $e) {
            $errors[] = "Row {$rowNumber}: Error downloading image from {$imageInput} – " . $e->getMessage();
        }
    }

    private function getGoogleDriveDirectUrl($url)
    {
        $patterns = [
            '/\/file\/d\/([^\/]+)/',
            '/\/open\?id=([^&]+)/',
            '/\/uc\?id=([^&]+)/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                $fileId = $matches[1];
                return "https://drive.google.com/uc?export=download&id={$fileId}";
            }
        }
        return $url;
    }

    private function getExtensionFromMime($mime)
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        return $map[$mime] ?? null;
    }

    private function attachImageToItem($itemId, $imgName, &$errors, $rowNumber)
    {
        $found = false;
        $relativePath = null;
        $originalName = $imgName;

        $hash = md5($originalName);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        if (empty($ext)) {
            $ext = 'jpg';
        }
        $destFileName = $hash . '.' . $ext;
        $storageDest = 'items/' . $destFileName;

        $existing = ItemImage::where('item_id', $itemId)
            ->where('image', $storageDest)
            ->first();
        if ($existing) {
            return;
        }

        if (Storage::disk('public')->exists('items/' . $originalName)) {
            $contents = Storage::disk('public')->get('items/' . $originalName);
            Storage::disk('public')->put($storageDest, $contents);
            $relativePath = $storageDest;
            $found = true;
        } elseif (file_exists(public_path('import_images/' . $originalName))) {
            $contents = file_get_contents(public_path('import_images/' . $originalName));
            Storage::disk('public')->put($storageDest, $contents);
            $relativePath = $storageDest;
            $found = true;
        }

        if ($found) {
            ItemImage::create([
                'item_id' => $itemId,
                'image'   => $relativePath,
            ]);
        } else {
            $errors[] = "Row {$rowNumber}: Image not found: {$originalName} (item created but image missing)";
        }
    }

    public function showImageUploader()
    {
        $importDir = public_path('import_images/');
        if (!File::exists($importDir)) {
            File::makeDirectory($importDir, 0755, true);
        }

        $files = File::files($importDir);
        $filenames = array_map(fn($file) => $file->getFilename(), $files);

        if (request()->filled('search')) {
            $search = strtolower(request('search'));
            $filenames = array_values(array_filter($filenames, function ($filename) use ($search) {
                return str_contains(strtolower($filename), $search);
            }));
        }

        $page = request()->get('page', 1);
        $perPage = 24;
        $offset = ($page - 1) * $perPage;

        $paginatedItems = array_slice($filenames, $offset, $perPage);

        $imageNames = new LengthAwarePaginator(
            $paginatedItems,
            count($filenames),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.items.import_images', compact('imageNames'));
    }

    /**
     * UPDATED – Compress import images to 800px, 60% quality
     */
    public function uploadImportImages(Request $request)
    {
        $request->validate([
            'images' => 'required',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120'
        ]);

        $importDir = public_path('import_images/');

        if (!File::exists($importDir)) {
            File::makeDirectory($importDir, 0755, true);
        }

        $uploaded = [];
        $warnings = [];

        $manager = new ImageManager(new Driver());

        foreach ($request->file('images') as $file) {
            $originalName = $file->getClientOriginalName();
            $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
            $targetPath = $importDir . $cleanName;

            if (File::exists($targetPath)) {
                $warnings[] = "File '{$cleanName}' already exists. Skipped to avoid overwrite.";
                continue;
            }

            try {
                if ($file->getSize() <= 100 * 1024) {
                    $file->move($importDir, $cleanName);
                } else {
                    $extension = strtolower($file->getClientOriginalExtension());
                    $image = $manager->read($file->getRealPath());
                    $image->scale(800, 800);

                    switch ($extension) {
                        case 'jpg':
                        case 'jpeg':
                            $encoded = $image->toJpeg(60);
                            break;
                        case 'png':
                            $encoded = $image->toPng();
                            break;
                        case 'webp':
                            $encoded = $image->toWebp(60);
                            break;
                        default:
                            $encoded = $image->encode();
                    }
                    $encoded->save($targetPath);
                }
                $uploaded[] = $cleanName;
            } catch (\Exception $e) {
                $file->move($importDir, $cleanName);
                $uploaded[] = $cleanName;
                $warnings[] = "Optimization failed for '{$cleanName}'. Original image saved.";
                \Log::error('Import image optimization failed', [
                    'file' => $cleanName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()
            ->with('success', count($uploaded) . ' images uploaded successfully.')
            ->with('warnings', $warnings);
    }

    public function deleteImportImage($filename)
    {
        $filePath = public_path('import_images/' . $filename);
        if (File::exists($filePath)) {
            File::delete($filePath);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function checkSimilarSku(Request $request)
    {
        $sku = $request->get('sku');
        if (!$sku) {
            return response()->json(['similar' => []]);
        }

        $similar = Item::where('sku', 'LIKE', $sku . '%')
            ->orWhereRaw("? LIKE CONCAT(sku, '%')", [$sku])
            ->pluck('sku')
            ->unique()
            ->filter(function ($existingSku) use ($sku) {
                return $existingSku !== $sku;
            })
            ->values();

        return response()->json(['similar' => $similar]);
    }
}