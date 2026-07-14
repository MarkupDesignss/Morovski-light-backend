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
            // Get the image path (handle both column names)
            $imagePath = $firstImage->image_url ?? $firstImage->image;
            
            if (!empty($imagePath)) {
                // Check if it's already a full URL
                if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                    $ogImage = $imagePath;
                } else {
                    // Remove any leading slashes
                    $imagePath = ltrim($imagePath, '/');
                    
                    // Check if it's a storage path (starts with 'items/' or 'storage/')
                    if (strpos($imagePath, 'items/') === 0) {
                        // Use storage URL for files in storage/app/public
                        $ogImage = asset('storage/' . $imagePath);
                    } elseif (strpos($imagePath, 'storage/') === 0) {
                        $ogImage = asset($imagePath);
                    } else {
                        // Regular path
                        $ogImage = asset($imagePath);
                    }
                }
            }
        }

        // Fallback to a default image if no image found
        if (!$ogImage) {
            $ogImage = asset('images/default-product.jpg');
        }

        // Get image dimensions for OG tags (optional, can be hardcoded)
        $imageInfo = @getimagesize(str_replace('https://', 'http://', $ogImage)); // Temporarily use HTTP to avoid SSL issues
        $imageWidth = $imageInfo ? $imageInfo[0] : 1200;
        $imageHeight = $imageInfo ? $imageInfo[1] : 630;
        $imageType = $imageInfo ? $imageInfo['mime'] : 'image/jpeg';

        // Enforce HTTPS
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
        // Stock filter: in_stock (>20), low_stock (1-20), out_of_stock (<=0)
        if ($request->stock) {
            if ($request->stock === 'in_stock') {
                $query->where('quantity', '>', 20);
            } elseif ($request->stock === 'low_stock') {
                $query->whereBetween('quantity', [1, 20]);
            } elseif ($request->stock === 'out_of_stock') {
                $query->where('quantity', '<=', 0);
            }
        }
        // Type filter: online/offline
        if ($request->type) {
            if (in_array($request->type, ['online', 'offline'])) {
                $query->where('type', $request->type);
            }
        }
        $totalItems = Item::count();

        $items = $query->paginate(10);

        return view('admin.items.index', compact('items', 'totalItems'));
    }

    //  CREATE PAGE
    public function create()
    {
        $categories = Category::all();
        $warehouses = Warehouse::where('is_active', true)->get(); // only active warehouses
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

        // Build specs
        $specs = [];
        if ($request->spec_key) {
            foreach ($request->spec_key as $i => $key) {
                if ($key) {
                    $specs[$key] = $request->spec_value[$i] ?? null;
                }
            }
        }

        // Calculate total quantity from warehouse inputs
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

        // Save warehouse-wise stocks
        foreach ($request->warehouse_quantities as $warehouseId => $qty) {
            WarehouseItem::create([
                'warehouse_id' => $warehouseId,
                'item_id' => $item->id,
                'quantity' => $qty,
                'reserved_quantity' => 0,
            ]);
        }

        // Upload images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('items', 'public');
                ItemImage::create(['item_id' => $item->id, 'image' => $path]);
            }
        }

        return redirect()->route('admin.items.index')->with('success', 'Item created with warehouse stocks');
    }

    //  Show Items
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
        // Get existing warehouse stocks keyed by warehouse_id
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

        // rebuild JSON specs
        $specs = [];
        if ($request->spec_key) {
            foreach ($request->spec_key as $i => $key) {
                if ($key) {
                    $specs[$key] = $request->spec_value[$i] ?? null;
                }
            }
        }

        // Calculate total quantity from warehouse inputs
        $totalQuantity = array_sum($request->warehouse_quantities);

        // if (Item::where('slug', $slug)->exists())
        //     { return redirect()->back() ->withInput() ->withErrors([ 'name' => 'Item with same slug already exists. Kindly use another name.' ]); }

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

        // Sync warehouse stocks (update or create)
        foreach ($request->warehouse_quantities as $warehouseId => $qty) {
            WarehouseItem::updateOrCreate(
                ['warehouse_id' => $warehouseId, 'item_id' => $item->id],
                ['quantity' => $qty]
            );
        }

        // Remove warehouses that are not in the request
        $submittedWarehouses = array_keys($request->warehouse_quantities);
        $item->warehouseItems()->whereNotIn('warehouse_id', $submittedWarehouses)->delete();

        // Add new images (existing remain)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('items', 'public');
                ItemImage::create([
                    'item_id' => $item->id,
                    'image' => $path
                ]);
            }
        }

        return redirect()->route('admin.items.index')
            ->with('success', 'Item updated successfully');
    }

    //  DELETE ITEM (with images)
    public function destroy($id)
    {
        $item = Item::with('images')->findOrFail($id);

        // delete images from storage
        foreach ($item->images as $img) {
            Storage::disk('public')->delete($img->image);
        }

        $item->delete();

        return back()->with('success', 'Item deleted');
    }

    // DELETE SINGLE IMAGE (optional but useful)
    public function deleteImage($id)
    {
        $image = ItemImage::findOrFail($id);

        Storage::disk('public')->delete($image->image);
        $image->delete();

        return back()->with('success', 'Image deleted');
    }

    // ========== CSV IMPORT METHODS ==========

    /**
     * Show CSV import form
     */
    public function showImportForm()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('admin.items.import', compact('warehouses'));
    }

     /**
     * Chunked upload – temporary file banaye / append karein
     */
    // public function importChunk(Request $request)
    // {
    //     Log::info('Chunk received', [
    //         'chunk_index' => $request->chunk_index,
    //         'total_chunks' => $request->total_chunks,
    //         'token' => $request->upload_token
    //     ]);

    //     $request->validate([
    //         'chunk'          => 'required|string',
    //         'chunk_index'    => 'required|integer',
    //         'total_chunks'   => 'required|integer',
    //         'upload_token'   => 'required|string',
    //         'original_name'  => 'required|string',
    //     ]);

    //     $token = $request->upload_token;
    //     $tempDir = storage_path('app/temp_imports');
    //     if (!File::exists($tempDir)) {
    //         File::makeDirectory($tempDir, 0755, true);
    //     }

    //     $tempFile = $tempDir . '/' . $token . '.csv';

    //     // First chunk: overwrite (or create)
    //     if ($request->chunk_index == 0) {
    //         File::put($tempFile, $request->chunk);
    //     } else {
    //         File::append($tempFile, $request->chunk);
    //     }

    //     // Agar last chunk hai toh process karein
    //     if ($request->chunk_index == $request->total_chunks - 1) {
    //         $imported = 0;
    //         $updated = 0;
    //         $importErrors = [];

    //         try {
    //             $this->processCsvFile($tempFile, $imported, $updated, $importErrors);
    //             File::delete($tempFile); // cleanup

    //             Log::info('Chunked import completed', [
    //                 'created' => $imported,
    //                 'updated' => $updated
    //             ]);

    //             return response()->json([
    //                 'success'  => true,
    //                 'message'  => "Imported: {$imported}, Updated: {$updated}",
    //                 'warnings' => $importErrors
    //             ]);
    //         } catch (\Exception $e) {
    //             File::delete($tempFile);
    //             Log::error('Chunk processing failed', ['error' => $e->getMessage()]);
    //             return response()->json([
    //                 'success' => false,
    //                 'error'   => 'Processing failed: ' . $e->getMessage()
    //             ], 500);
    //         }
    //     }

    //     return response()->json(['success' => true, 'message' => 'Chunk received']);
    // }

    /**
     * Core CSV processing – extracted from importCsv
     */
    //  protected function processCsvFile($filePath, &$imported, &$updated, &$importErrors)
    // {
    //     $handle = fopen($filePath, 'r');
    //     if (!$handle) {
    //         throw new \Exception("Cannot open file: {$filePath}");
    //     }

    //     $headers = fgetcsv($handle);
    //     // Remove BOM from first header if present
    //     if (isset($headers[0])) {
    //         $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    //     }
    //     Log::info('Processing CSV headers', ['headers' => $headers]);

    //     $validRows = [];
    //     $errors = [];
    //     $rowNumber = 1; // header is row 0, so we start counting from 1
    //     $skuSeen = [];

    //     while (($row = fgetcsv($handle)) !== false) {
    //         $rowNumber++;

    //         // Skip completely empty lines
    //         if (empty($row) || (count($row) == 1 && trim($row[0]) === '')) {
    //             continue;
    //         }

    //         // ----- CRITICAL: Column count check -----
    //         if (count($headers) !== count($row)) {
    //             Log::warning("Row {$rowNumber} column mismatch", [
    //                 'expected' => count($headers),
    //                 'actual'   => count($row),
    //                 'preview'  => implode(',', array_slice($row, 0, 5))
    //             ]);
    //             $errors[] = "Row {$rowNumber}: Column count mismatch (expected " . count($headers) . ", got " . count($row) . ")";
    //             continue; // skip this row
    //         }

    //         $data = array_combine($headers, $row);
    //         $rowErrors = [];

    //         // ---------- Required fields ----------
    //         $required = ['name', 'category', 'type', 'price', 'warehouses'];
    //         foreach ($required as $field) {
    //             if (empty($data[$field])) {
    //                 $rowErrors[] = "Missing required field: {$field}";
    //             }
    //         }

    //         if (!empty($data['price']) && !is_numeric($data['price'])) {
    //             $rowErrors[] = "Price must be numeric";
    //         }

    //         // ---------- Category validation ----------
    //         $category = null;
    //         if (!empty($data['category'])) {
    //             $category = Category::where('name', $data['category'])->first();
    //             if (!$category) {
    //                 $rowErrors[] = "Category '{$data['category']}' not found in database";
    //             }
    //         } else {
    //             $rowErrors[] = "Category is required";
    //         }

    //         // ---------- Warehouse validation ----------
    //         if (!empty($data['warehouses'])) {
    //             $parts = explode(',', $data['warehouses']);
    //             foreach ($parts as $part) {
    //                 $part = trim($part);
    //                 if (strpos($part, ':') === false) {
    //                     $rowErrors[] = "Invalid warehouse format: {$part}. Expected CODE:QUANTITY";
    //                 } else {
    //                     [$code, $qty] = explode(':', $part);
    //                     if (!Warehouse::where('code', $code)->exists()) {
    //                         $rowErrors[] = "Warehouse code '{$code}' not found";
    //                     }
    //                 }
    //             }
    //         } else {
    //             $rowErrors[] = "Warehouses column required";
    //         }

    //         // ---------- Image validation ----------
    //         if (!empty($data['images'])) {
    //             $imageUrls = array_map('trim', explode(',', $data['images']));
    //             foreach ($imageUrls as $url) {
    //                 if (empty($url)) {
    //                     $rowErrors[] = "Empty image value found";
    //                 }
    //             }
    //         }

    //         // ---------- SKU duplicate check ----------
    //         if (!empty($data['sku'])) {
    //             $sku = trim($data['sku']);
    //             if (in_array($sku, $skuSeen)) {
    //                 $rowErrors[] = "Duplicate SKU found in CSV: '{$sku}'.";
    //             } else {
    //                 $skuSeen[] = $sku;
    //             }
    //         }

    //         if (empty($rowErrors)) {
    //             $validRows[] = [
    //                 'data'     => $data,
    //                 'category' => $category,
    //             ];
    //         } else {
    //             foreach ($rowErrors as $err) {
    //                 $errors[] = "Row {$rowNumber}: {$err}";
    //             }
    //         }
    //     }
    //     fclose($handle);

    //     // If there are any errors (including column mismatch), stop and throw
    //     if (!empty($errors)) {
    //         $errorMsg = "Validation errors:\n" . implode("\n", array_slice($errors, 0, 10)) . (count($errors) > 10 ? "\n... and " . (count($errors) - 10) . " more" : '');
    //         throw new \Exception($errorMsg);
    //     }

    //     // ---------- DB Transaction ----------
    //     DB::beginTransaction();
    //     try {
    //         foreach ($validRows as $validRow) {
    //             $data = $validRow['data'];
    //             $category = $validRow['category'];

    //             // Parse warehouse quantities
    //             $warehouseQtys = [];
    //             $parts = explode(',', $data['warehouses']);
    //             foreach ($parts as $part) {
    //                 [$code, $qty] = explode(':', trim($part));
    //                 $warehouse = Warehouse::where('code', $code)->first();
    //                 if ($warehouse) {
    //                     $warehouseQtys[$warehouse->id] = (int) $qty;
    //                 }
    //             }

    //             // Parse specifications
    //             $specs = [];
    //             if (!empty($data['specifications'])) {
    //                 $pairs = explode(';', $data['specifications']);
    //                 foreach ($pairs as $pair) {
    //                     if (strpos($pair, ':') !== false) {
    //                         [$k, $v] = explode(':', $pair);
    //                         $specs[trim($k)] = trim($v);
    //                     }
    //                 }
    //             }

    //             $totalQuantity = array_sum($warehouseQtys);

    //             // Find existing item by SKU
    //             $item = null;
    //             if (!empty($data['sku'])) {
    //                 $item = Item::where('sku', $data['sku'])->first();
    //             }

    //             // Generate unique slug
    //             $baseSlug = Str::slug($data['name']);
    //             $slug = $baseSlug;
    //             $counter = 1;
    //             if ($item) {
    //                 while (Item::where('slug', $slug)->where('id', '!=', $item->id)->exists()) {
    //                     $slug = $baseSlug . '-' . $counter++;
    //                 }
    //             } else {
    //                 while (Item::where('slug', $slug)->exists()) {
    //                     $slug = $baseSlug . '-' . $counter++;
    //                 }
    //             }

    //             // Best Seller (is_featured)
    //             $isFeatured = 0;
    //             if (!empty($data['is_featured'])) {
    //                 $val = strtolower(trim($data['is_featured']));
    //                 if (in_array($val, ['yes', 'true', '1', 'best seller'])) {
    //                     $isFeatured = 1;
    //                 }
    //             }

    //             if ($item) {
    //                 $item->update([
    //                     'name'           => $data['name'],
    //                     'category_id'    => $category->id,
    //                     'slug'           => $slug,
    //                     'price'          => (float) $data['price'],
    //                     'type'           => $data['type'],
    //                     'quantity'       => $totalQuantity,
    //                     'is_featured'    => $isFeatured,
    //                     'description'    => $data['description'] ?? null,
    //                     'specifications' => $specs,
    //                 ]);
    //                 // Sync warehouses
    //                 $item->warehouseItems()->delete();
    //                 foreach ($warehouseQtys as $whId => $qty) {
    //                     WarehouseItem::create([
    //                         'warehouse_id'      => $whId,
    //                         'item_id'           => $item->id,
    //                         'quantity'          => $qty,
    //                         'reserved_quantity' => 0,
    //                         'damaged_quantity'  => 0,
    //                     ]);
    //                 }
    //                 // Attach images
    //                 if (!empty($data['images'])) {
    //                     $imageUrls = array_map('trim', explode(',', $data['images']));
    //                     foreach ($imageUrls as $url) {
    //                         $this->attachImageFromUrl($item->id, $url, $importErrors, $rowNumber);
    //                     }
    //                 }
    //                 $updated++;
    //             } else {
    //                 $item = Item::create([
    //                     'name'           => $data['name'],
    //                     'category_id'    => $category->id,
    //                     'slug'           => $slug,
    //                     'sku'            => $data['sku'] ?? null,
    //                     'price'          => (float) $data['price'],
    //                     'type'           => $data['type'],
    //                     'quantity'       => $totalQuantity,
    //                     'is_featured'    => $isFeatured,
    //                     'description'    => $data['description'] ?? null,
    //                     'specifications' => $specs,
    //                 ]);
    //                 foreach ($warehouseQtys as $whId => $qty) {
    //                     WarehouseItem::create([
    //                         'warehouse_id'      => $whId,
    //                         'item_id'           => $item->id,
    //                         'quantity'          => $qty,
    //                         'reserved_quantity' => 0,
    //                         'damaged_quantity'  => 0,
    //                     ]);
    //                 }
    //                 if (!empty($data['images'])) {
    //                     $imageUrls = array_map('trim', explode(',', $data['images']));
    //                     foreach ($imageUrls as $url) {
    //                         $this->attachImageFromUrl($item->id, $url, $importErrors, $rowNumber);
    //                     }
    //                 }
    //                 $imported++;
    //             }
    //         }
    //         DB::commit();
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }
    
/**
 * Chunked upload – receives a chunk, appends to temp file,
 * and processes the whole file when the last chunk arrives.
 */
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

    // First chunk: overwrite (or create)
    if ($request->chunk_index == 0) {
        File::put($tempFile, $request->chunk);
    } else {
        File::append($tempFile, $request->chunk);
    }

    // If last chunk, process the whole file
    if ($request->chunk_index == $request->total_chunks - 1) {
        $imported = 0;
        $updated = 0;
        $importErrors = [];
        $validationErrors = []; // will hold full error list

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

            // If we have validation errors array, send them in full
            if (!empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Validation errors occurred.',
                    'validation_errors' => $validationErrors // full list
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

/**
 * Core CSV processing – validates and imports rows.
 * Now accepts $offset and $validationErrors by reference.
 */
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

        // Required fields
        $required = ['name', 'category', 'type', 'price', 'warehouses'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $rowErrors[] = "Missing required field: {$field}";
            }
        }

        if (!empty($data['price']) && !is_numeric($data['price'])) {
            $rowErrors[] = "Price must be numeric";
        }

        // Category
        $category = null;
        if (!empty($data['category'])) {
            $category = Category::where('name', $data['category'])->first();
            if (!$category) {
                $rowErrors[] = "Category '{$data['category']}' not found in database";
            }
        } else {
            $rowErrors[] = "Category is required";
        }

        // Warehouses
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

        // Images (only format check)
        if (!empty($data['images'])) {
            $imageUrls = array_map('trim', explode(',', $data['images']));
            foreach ($imageUrls as $url) {
                if (empty($url)) {
                    $rowErrors[] = "Empty image value found";
                }
            }
        }

        // SKU duplicate
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

    // If there are validation errors, store them and throw exception
    if (!empty($errors)) {
        $validationErrors = $errors; // assign full list
        $errorMsg = "Validation errors:\n" . implode("\n", $errors);
        throw new \Exception($errorMsg);
    }

    // ---------- DB Transaction ----------
    DB::beginTransaction();
    try {
        foreach ($validRows as $validRow) {
            $data = $validRow['data'];
            $category = $validRow['category'];
            $globalRow = $validRow['globalRow'];

            // Parse warehouse quantities
            $warehouseQtys = [];
            $parts = explode(',', $data['warehouses']);
            foreach ($parts as $part) {
                [$code, $qty] = explode(':', trim($part));
                $warehouse = Warehouse::where('code', $code)->first();
                if ($warehouse) {
                    $warehouseQtys[$warehouse->id] = (int) $qty;
                }
            }

            // Parse specifications
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

            // Find existing item by SKU
            $item = null;
            if (!empty($data['sku'])) {
                $item = Item::where('sku', $data['sku'])->first();
            }

            // Generate unique slug
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
    
// --------------------------------------------------------------------

    public function importCsv(Request $request)
    {
        // ---------- START LOG ----------
        Log::info('=== CSV IMPORT STARTED ===', [
            'file_name' => $request->file('csv_file')?->getClientOriginalName(),
            'file_size' => $request->file('csv_file')?->getSize(),
            'timestamp' => now()->toDateTimeString(),
            'user_id'   => auth()->id()
        ]);

        // Validation (size limit already 5MB)
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120'
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        $headers = fgetcsv($handle);

        // Log headers
        Log::info('CSV Headers', ['headers' => $headers]);

        $validRows = [];
        $errors = [];
        $rowNumber = 1;
        $skuSeen = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $data = array_combine($headers, $row);
            $rowErrors = [];

            // ---------- Required fields ----------
            $required = ['name', 'category', 'type', 'price', 'warehouses'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $rowErrors[] = "Missing required field: {$field}";
                }
            }

            if (!empty($data['price']) && !is_numeric($data['price'])) {
                $rowErrors[] = "Price must be numeric";
            }

            // ---------- Category validation ----------
            $category = null;
            if (!empty($data['category'])) {
                $category = Category::where('name', $data['category'])->first();
                if (!$category) {
                    $rowErrors[] = "Category '{$data['category']}' not found in database";
                }
            } else {
                $rowErrors[] = "Category is required";
            }

            // ---------- Warehouse validation ----------
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

            // ---------- Image validation ----------
            if (!empty($data['images'])) {
                $imageUrls = array_map('trim', explode(',', $data['images']));
                foreach ($imageUrls as $url) {
                    if (empty($url)) {
                        $rowErrors[] = "Empty image value found";
                    }
                }
            }

            // ---------- SKU duplicate check ----------
            if (!empty($data['sku'])) {
                $sku = trim($data['sku']);
                if (in_array($sku, $skuSeen)) {
                    $rowErrors[] = "Duplicate SKU found in CSV: '{$sku}'.";
                } else {
                    $skuSeen[] = $sku;
                }
            }

            // ---------- Log row errors if any ----------
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

        // Log validation summary
        Log::info('Validation summary', [
            'valid_rows'   => count($validRows),
            'error_rows'   => count($errors),
            'total_rows'   => $rowNumber - 1 // exclude header
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

                // ---------- Parse warehouse quantities ----------
                $warehouseQtys = [];
                $parts = explode(',', $data['warehouses']);
                foreach ($parts as $part) {
                    [$code, $qty] = explode(':', trim($part));
                    $warehouse = Warehouse::where('code', $code)->first();
                    if ($warehouse) {
                        $warehouseQtys[$warehouse->id] = (int) $qty;
                    }
                }

                // ---------- Parse specifications ----------
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

                // ---------- Find existing item by SKU ----------
                $item = null;
                if (!empty($data['sku'])) {
                    $item = Item::where('sku', $data['sku'])->first();
                }

                // ---------- Generate unique slug ----------
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

                // ---------- Process Best Seller (is_featured) ----------
                $isFeatured = 0;
                if (!empty($data['is_featured'])) {
                    $val = strtolower(trim($data['is_featured']));
                    if (in_array($val, ['yes', 'true', '1', 'best seller'])) {
                        $isFeatured = 1;
                    }
                }

                // ---------- Perform update or create ----------
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
                        'is_featured'     => $isFeatured,  // <-- Best Seller
                        'description'     => $data['description'] ?? null,
                        'specifications'  => $specs,
                    ]);

                    // Sync warehouses
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

                    // Attach images
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
                        'is_featured'     => $isFeatured, // <-- Best Seller
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
     * Attach image to item – supports both local filename (legacy) and URL.
     * If URL, downloads and stores locally.
     */
    private function attachImageFromUrl($itemId, $imageInput, &$errors, $rowNumber)
    {
        // If it's a local filename, delegate
        if (!filter_var($imageInput, FILTER_VALIDATE_URL)) {
            $this->attachImageToItem($itemId, $imageInput, $errors, $rowNumber);
            return;
        }

        // Google Drive conversion
        $downloadUrl = $this->getGoogleDriveDirectUrl($imageInput);

        try {
            // Download image
            $contents = file_get_contents($downloadUrl);
            if ($contents === false) {
                $errors[] = "Row {$rowNumber}: Failed to download image from URL: {$imageInput}";
                return;
            }

            // Determine extension
            $extension = pathinfo(parse_url($downloadUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_buffer($finfo, $contents);
                finfo_close($finfo);
                $extension = $this->getExtensionFromMime($mime) ?: 'jpg';
            }

            // ====== DETERMINISTIC FILENAME ======
            // Use MD5 hash of the original URL to always get same filename
            $hash = md5($imageInput);
            $fileName = $hash . '.' . $extension;
            $storagePath = 'items/' . $fileName;

            // Check if this image already exists for this item
            $existing = ItemImage::where('item_id', $itemId)
                ->where('image', $storagePath)
                ->first();
            if ($existing) {
                // Already attached, skip
                return;
            }

            // Store only if not already stored physically
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

    /**
     * Convert Google Drive shareable link to direct download URL
     */
    private function getGoogleDriveDirectUrl($url)
    {
        // Match Google Drive file ID from various formats
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
        return $url; // return as is if not Google Drive
    }

    /**
     * Helper to map mime type to extension
     */
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

    /**
     * Helper to attach an image to an item (copy from temp if needed)
     */
    private function attachImageToItem($itemId, $imgName, &$errors, $rowNumber)
    {
        $found = false;
        $relativePath = null;
        $originalName = $imgName;

        // First, check if this filename already exists in storage (with deterministic name)
        $hash = md5($originalName);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        if (empty($ext)) {
            // If no extension, try to guess from file if exists
            // For simplicity, we'll skip and treat as is
            $ext = 'jpg'; // fallback
        }
        $destFileName = $hash . '.' . $ext;
        $storageDest = 'items/' . $destFileName;

        // Check if already attached to this item
        $existing = ItemImage::where('item_id', $itemId)
            ->where('image', $storageDest)
            ->first();
        if ($existing) {
            return; // already exists
        }

        // Try to locate the source file
        if (Storage::disk('public')->exists('items/' . $originalName)) {
            // If the file exists with original name, copy to deterministic name
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

    /**
     * Show image uploader for CSV import
     */
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
     * Upload images to temporary folder (keeps original filename)
     */
    public function uploadImportImages(Request $request)
    {
        $request->validate([
            'images' => 'required',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120' // max 5MB per image
        ]);

        $importDir = public_path('import_images/');
        if (!File::exists($importDir)) {
            File::makeDirectory($importDir, 0755, true);
        }

        $uploaded = [];
        $warnings = [];

        foreach ($request->file('images') as $file) {
            $originalName = $file->getClientOriginalName();

            // Remove any special characters from filename (but keep name)
            $cleanName = preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);

            // Check if file with same name already exists
            $targetPath = $importDir . $cleanName;

            if (File::exists($targetPath)) {
                $warnings[] = "File '{$cleanName}' already exists. Skipped to avoid overwrite.";
                continue;
            }

            $file->move($importDir, $cleanName);
            $uploaded[] = $cleanName;
        }

        $message = count($uploaded) . ' images uploaded successfully.';
        if (!empty($warnings)) {
            return back()->with('success', $message)->with('warnings', $warnings);
        }

        return back()->with('success', $message);
    }

    /**
     * Delete an image from temporary folder
     */
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

        // Find similar SKUs that are either:
        // - existing SKU starts with given SKU (prefix), OR
        // - given SKU starts with existing SKU (suffix), BUT exclude exact match
        $similar = Item::where('sku', 'LIKE', $sku . '%')   // existing starts with input
            ->orWhereRaw("? LIKE CONCAT(sku, '%')", [$sku]) // input starts with existing
            ->pluck('sku')
            ->unique()
            ->filter(function ($existingSku) use ($sku) {
                return $existingSku !== $sku; // exclude exact match
            })
            ->values();

        return response()->json(['similar' => $similar]);
    }
}