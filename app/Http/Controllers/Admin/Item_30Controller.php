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
use Illuminate\Support\Facades\File;


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

    //  EDIT PAGE
    // public function edit($id)
    // {
    //     $item = Item::with('images')->findOrFail($id);
    //     $categories = Category::all();

    //     return view('admin.items.edit', compact('item', 'categories'));
    // }

    // // UPDATE
    // public function update(Request $request, $id)
    // {
    //     $item = Item::findOrFail($id);

    //     $request->validate([
    //         'name' => 'required',
    //         'quantity' => 'required|min:1',
    //         'type' => 'required|string',
    //         'category_id' => 'required|exists:categories,id',
    //         'price' => 'required|numeric',
    //         'images.*' => 'image|mimes:jpg,jpeg,png,webp'
    //     ]);

    //     // rebuild JSON specs
    //     $specs = [];
    //     if ($request->spec_key) {
    //         foreach ($request->spec_key as $i => $key) {
    //             if ($key) {
    //                 $specs[$key] = $request->spec_value[$i] ?? null;
    //             }
    //         }
    //     }

    //     $item->update([
    //         'name' => $request->name ?? $item->name,
    //         'quantity' => $request->quantity ?? $item->quantity,
    //         'category_id' => $request->category_id ?? $item->category_id,
    //         'slug' => $item->slug,
    //         'is_featured' => $request->is_featured ?? $item->is_featured,
    //         'type' => $request->type ?? $item->type ,
    //         'sku' => $request->sku ?? $item->sku,
    //         'price' => $request->price ?? $item->price,
    //         'description' => $request->description ?? $item->description,
    //         'specifications' => $specs  ?? $item->specifications
    //     ]);

    //     // Add new images (existing remain)
    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $img) {
    //             $path = $img->store('items', 'public');

    //             ItemImage::create([
    //                 'item_id' => $item->id,
    //                 'image' => $path
    //             ]);
    //         }
    //     }

    //     return redirect()->route('admin.items.index')
    //         ->with('success', 'Item updated successfully');
    // }

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
    
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120'
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        $headers = fgetcsv($handle);

        $validRows = [];
        $errors = [];
        $rowNumber = 1;
        $skuSeen = []; // Track SKU duplicates in CSV

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $data = array_combine($headers, $row);
            $rowErrors = [];

            // ---------- Required fields ----------
            $required = ['name', 'category', 'type', 'model', 'price', 'warehouses'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $rowErrors[] = "Missing required field: {$field}";
                }
            }

            if (!empty($data['price']) && !is_numeric($data['price'])) {
                $rowErrors[] = "Price must be numeric";
            }

            // ---------- Category validation – only existing allowed ----------
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

            // ---------- Image validation – only empty check ----------
            if (!empty($data['images'])) {
                $imageUrls = array_map('trim', explode(',', $data['images']));
                foreach ($imageUrls as $url) {
                    if (empty($url)) {
                        $rowErrors[] = "Empty image value found";
                    }
                }
            }

            // ---------- DUPLICATE SKU CHECK (NEW) ----------
            if (!empty($data['sku'])) {
                $sku = trim($data['sku']);
                // Check if this SKU already appeared in the CSV
                if (in_array($sku, $skuSeen)) {
                    $rowErrors[] = "Duplicate SKU found in CSV: '{$sku}'. Each product must have a unique SKU within the file.";
                } else {
                    $skuSeen[] = $sku;
                }
            }

            // ---------- (Optional) Slug validation – we'll handle during create/update ----------

            if (empty($rowErrors)) {
                $validRows[] = [
                    'data'     => $data,
                    'category' => $category ?? null,
                ];
            } else {
                foreach ($rowErrors as $err) {
                    $errors[] = "Row {$rowNumber}: {$err}";
                }
            }
        }

        fclose($handle);

        if (!empty($errors)) {
            return redirect()->route('admin.items.index')
                ->with('error', 'CSV validation failed. No items were imported.')
                ->with('errors', $errors);
        }

        $imported = 0;
        $updated = 0;
        $importErrors = [];

        DB::beginTransaction();

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

                // ---------- Generate unique slug (numeric suffix) ----------
                $baseSlug = Str::slug($data['name']);
                $slug = $baseSlug;
                $counter = 1;

                if ($item) {
                    // UPDATE: slug can be same as current, but must not conflict with other items
                    while (Item::where('slug', $slug)->where('id', '!=', $item->id)->exists()) {
                        $slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }
                } else {
                    // CREATE: slug must be globally unique
                    while (Item::where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }
                }

                // ---------- Perform update or create ----------
                if ($item) {
                    // UPDATE existing item
                    $item->update([
                        'name'            => $data['name'],
                        'category_id'     => $category->id,
                        'slug'            => $slug,
                        'price'           => (float) $data['price'],
                        'type'            => $data['type'],
                        'model'           => $data['model'],
                        'quantity'        => $totalQuantity,
                        'is_featured'     => $data['is_featured'] ?? 0,
                        'description'     => $data['description'] ?? null,
                        'specifications'  => $specs,
                    ]);

                    // Sync warehouses (delete old, insert new)
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

                    // Attach images (duplicate prevention inside attachImageFromUrl)
                    if (!empty($data['images'])) {
                        $imageUrls = array_map('trim', explode(',', $data['images']));
                        foreach ($imageUrls as $url) {
                            $this->attachImageFromUrl($item->id, $url, $importErrors, $rowNumber);
                        }
                    }

                    $updated++;
                } else {
                    // CREATE new item
                    $item = Item::create([
                        'name'            => $data['name'],
                        'category_id'     => $category->id,
                        'slug'            => $slug,
                        'sku'             => $data['sku'] ?? null,
                        'price'           => (float) $data['price'],
                        'type'            => $data['type'],
                        'model'           => $data['model'],
                        'quantity'        => $totalQuantity,
                        'is_featured'     => $data['is_featured'] ?? 0,
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

            $message = "{$imported} items created, {$updated} items updated successfully.";
            if (!empty($importErrors)) {
                return redirect()->route('admin.items.index')
                    ->with('success', $message)
                    ->with('warnings', $importErrors);
            }

            return redirect()->route('admin.items.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
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
        $imageNames = array_map(fn($file) => $file->getFilename(), File::files($importDir));
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