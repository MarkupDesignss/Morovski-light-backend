<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\ItemBackInStockMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    public function itemBycategory($id)
    {
        try {
            $items = Item::with('images')->where('category_id', $id)->get();
            return response()->json([
                'status' => true,
                'message' => 'Items fetched successfully',
                'data' => $items
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }
    public function scopeVisibleForUser($query, $user)
    {
        if (!$user || !in_array($user->role_id, [1, 2, 3])) {
            return $query->where('type', 'online');
        }
        return $query;
    }
    
    public function b2bItems(){
         try {
            $items = Item::with('images','category')->where('type','online') ->orderBy('id', 'desc')->get();
            return response()->json([
                'status' => true,
                'message' => 'Items fetched successfully',
                'data' => $items
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }
    
     public function index(Request $request)
    {
        $user = Auth::user();

        $query = Item::with('images', 'category','reviews')->visibleForUser($user)->latest();

        /*
        --------------------------------
        SEARCH
        --------------------------------
        */
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        /*
        --------------------------------
        CATEGORY FILTER
        --------------------------------
        */
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->get('type') === 'newly-launched') {
            $query->where('created_at', '>=', now()->subDays(30));
        }
        /*
        --------------------------------
        PRICE FILTER
        --------------------------------
        */
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        /*
        --------------------------------
        STOCK FILTER
        --------------------------------
        */
        if ($request->stock == 'in_stock') {
            $query->where('quantity', '>', 20);
        }

       if ($request->stock == 'low_stock') {
            $query->whereBetween('quantity', [1, 20]);
        }
        if ($request->stock == 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        }
        /*
        --------------------------------
        WATTAGE FILTER (JSON)
        --------------------------------
        */
        if ($request->filled('wattage')) {
            $query->where('specifications->wattage', $request->wattage);
        }

        /*
        --------------------------------
        COLOR TEMPERATURE FILTER (JSON)
        --------------------------------
        */
        // if ($request->filled('color_temperature')) {
        //     $query->where('specifications->Temperature', $request->color_temperature);
        // }
        
        /*
        --------------------------------
        COLOR TEMPERATURE FILTER (RANGE)
        --------------------------------
        */
        if ($request->filled('color_temp_range')) {
        
            $range = explode('-', $request->color_temp_range);
        
            if (count($range) == 2) {
        
                $minTemp = (int) $range[0];
                $maxTemp = (int) $range[1];
        
                $query->whereBetween(
                    DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(specifications, '$.Temperature')) AS UNSIGNED)"),
                    [$minTemp, $maxTemp]
                );
            }
        }

        /*
        --------------------------------
        ATTRIBUTES FILTER (JSON ARRAY)
        --------------------------------
        */
        if ($request->filled('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attr) {
                $query->whereJsonContains('specifications->attributes', $attr);
            }
        }

        /*
        --------------------------------
        PAGINATION
        --------------------------------
        */
        // $items = $query->paginate(10);
        $items = $query->paginate(15)->withQueryString();

        /*
        --------------------------------
        GET CART & WISHLIST IDS
        --------------------------------
        */
        $cartItemIds = [];
        $wishlistItemIds = [];

        if ($user) {
            $cartItemIds = CartItem::whereHas('cart', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->pluck('item_id')
                ->toArray();

            $wishlistItemIds = Wishlist::where('user_id', $user->id)
                ->pluck('item_id')
                ->toArray();
        }

        /*
        --------------------------------
        FORMAT RESPONSE
        --------------------------------
        */
        // $data = $items->getCollection()->map(function ($item) use ($cartItemIds, $wishlistItemIds) {

        //     return [
        //         'id' => $item->id,
        //         'name' => $item->name,
        //         'slug' => $item->slug,
        //         'sku' => $item->sku,
        //         'price' => $item->price,
        //         'quantity' => $item->quantity,

        //         'stock_type' => $item->quantity > 20 ? 'in_stock' : 'low_stock',

        //         'thumbnail' => $item->images->first()
        //             ? asset('storage/' . $item->images->first()->image)
        //             : null,

        //         'images' => $item->images->map(function ($img) {
        //             return asset('storage/' . $img->image);
        //         }),

        //         'specifications' => $item->specifications,

        //         'is_cart' => in_array($item->id, $cartItemIds),
        //         'is_wishlist' => in_array($item->id, $wishlistItemIds),
        //     ];
        // });
        $data = $items->getCollection()->map(function ($item) use ($cartItemIds, $wishlistItemIds) {
            
        // Calculate overall rating
        $overallRating = 0;
        $ratingCount = $item->reviews->count();
        
        if ($ratingCount > 0) {
            $overallRating = round($item->reviews->avg('rating'), 2);
        }

            return [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'sku' => $item->sku,
                'model' => $item->model,
                'price' => $item->price,
                'quantity' => $item->quantity,

                'stock_type' => $item->quantity > 20 ? 'in_stock' : 'low_stock',

                'thumbnail' => $item->images->first()
                    ? asset('storage/' . $item->images->first()->image)
                    : null,

                'images' => $item->images->map(function ($img) {
                    return asset('storage/' . $img->image);
                }),

                'category' => $item->category ? [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                    'slug' => $item->category->slug,
                ] : null,

                'specifications' => $item->specifications,

                'is_cart' => in_array($item->id, $cartItemIds),
                'is_wishlist' => in_array($item->id, $wishlistItemIds),
                'reviews' => $item->reviews,
                // New field added
                'overall_rating' => $overallRating,
                'total_reviews_count' => $ratingCount,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Items fetched successfully',
            'data' => $data,
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),

                'next_page_url' => $items->nextPageUrl(),
                'prev_page_url' => $items->previousPageUrl(),
                'first_page_url' => $items->url(1),
                'last_page_url' => $items->url($items->lastPage()),
            ],
        ]);
    }
    

    
        public function bestSellers()
    {
        $items = Item::with('images')
            ->select(
                'items.*',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->join('order_items', 'items.id', '=', 'order_items.item_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_status', '!=', 'cancelled')
            ->groupBy(
                'items.id',
                'items.category_id',
                'items.name',
                'items.slug',
                'items.sku',
                'items.price',
                'items.type',
                'items.quantity',
                'items.rating',
                'items.is_featured',
                'items.reviews_count',
                'items.wishlist_count',
                'items.description',
                'items.specifications',
                'items.created_at',
                'items.updated_at'
            )
            ->orderByDesc('total_sold')
            ->take(4)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Best seller items fetched successfully',
            'data' => $items
        ]);
    }

    /**
     * Newly Launched Items API
     * Items created within last 30 days
     */
    public function newlyLaunched()
    {
        $items = Item::with('images')
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->take(4)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Newly launched items fetched successfully',
            'data' => $items
        ]);
    }
    
     public function newlyArrivals(Request $request)
    {
        $user = Auth::user();

        $query = Item::with('images', 'category')
            ->where('created_at', '>=', now()->subDays(30))
            ->visibleForUser($user)->latest();

        /*
        --------------------------------
        SEARCH
        --------------------------------
        */
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        /*
        --------------------------------
        CATEGORY FILTER
        --------------------------------
        */
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->get('type') === 'newly-launched') {
            $query->where('created_at', '>=', now()->subDays(30));
        }
        /*
        --------------------------------
        PRICE FILTER
        --------------------------------
        */
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        /*
        --------------------------------
        STOCK FILTER
        --------------------------------
        */
        if ($request->stock == 'in_stock') {
            $query->where('quantity', '>', 20);
        }

        if ($request->stock == 'low_stock') {
            $query->whereBetween('quantity', [1, 20]);
        }
        if ($request->stock == 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        }
        /*
        --------------------------------
        WATTAGE FILTER (JSON)
        --------------------------------
        */
        if ($request->filled('wattage')) {
            $query->where('specifications->wattage', $request->wattage);
        }

        /*
        --------------------------------
        COLOR TEMPERATURE FILTER (JSON)
        --------------------------------
        */
        if ($request->filled('color_temperature')) {
            $query->where('specifications->color_temperature', $request->color_temperature);
        }

        /*
        --------------------------------
        ATTRIBUTES FILTER (JSON ARRAY)
        --------------------------------
        */
        if ($request->filled('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attr) {
                $query->whereJsonContains('specifications->attributes', $attr);
            }
        }

        /*
        --------------------------------
        PAGINATION
        --------------------------------
        */
        // $items = $query->paginate(10);
        $items = $query->paginate(10)->withQueryString();

        /*
        --------------------------------
        GET CART & WISHLIST IDS
        --------------------------------
        */
        $cartItemIds = [];
        $wishlistItemIds = [];

        if ($user) {
            $cartItemIds = CartItem::whereHas('cart', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->pluck('item_id')
                ->toArray();

            $wishlistItemIds = Wishlist::where('user_id', $user->id)
                ->pluck('item_id')
                ->toArray();
        }

        /*
        --------------------------------
        FORMAT RESPONSE
        --------------------------------
        */
        // $data = $items->getCollection()->map(function ($item) use ($cartItemIds, $wishlistItemIds) {

        //     return [
        //         'id' => $item->id,
        //         'name' => $item->name,
        //         'slug' => $item->slug,
        //         'sku' => $item->sku,
        //         'price' => $item->price,
        //         'quantity' => $item->quantity,

        //         'stock_type' => $item->quantity > 20 ? 'in_stock' : 'low_stock',

        //         'thumbnail' => $item->images->first()
        //             ? asset('storage/' . $item->images->first()->image)
        //             : null,

        //         'images' => $item->images->map(function ($img) {
        //             return asset('storage/' . $img->image);
        //         }),

        //         'specifications' => $item->specifications,

        //         'is_cart' => in_array($item->id, $cartItemIds),
        //         'is_wishlist' => in_array($item->id, $wishlistItemIds),
        //     ];
        // });
        $data = $items->getCollection()->map(function ($item) use ($cartItemIds, $wishlistItemIds) {

            return [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'sku' => $item->sku,
                'model' => $item->model,
                'price' => $item->price,
                'quantity' => $item->quantity,

                'stock_type' => $item->quantity > 20 ? 'in_stock' : 'low_stock',

                'thumbnail' => $item->images->first()
                    ? asset('storage/' . $item->images->first()->image)
                    : null,

                'images' => $item->images->map(function ($img) {
                    return asset('storage/' . $img->image);
                }),

                'category' => $item->category ? [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                    'slug' => $item->category->slug,
                ] : null,

                'specifications' => $item->specifications,

                'is_cart' => in_array($item->id, $cartItemIds),
                'is_wishlist' => in_array($item->id, $wishlistItemIds),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Items fetched successfully',
            'data' => $data,
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),

                'next_page_url' => $items->nextPageUrl(),
                'prev_page_url' => $items->previousPageUrl(),
                'first_page_url' => $items->url(1),
                'last_page_url' => $items->url($items->lastPage()),
            ],
        ]);
    }
    
    // public function show($slug)
    // {
    //     $user = Auth::user();

    //     $item = Item::with('images')
    //         ->where('slug', $slug)
    //         ->first();

    //     if (!$item) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Item not found'
    //         ], 404);
    //     }

    //     /*
    //     --------------------------------
    //     CHECK CART & WISHLIST
    //     --------------------------------
    //     */
    //     $isCart = false;
    //     $isWishlist = false;

    //     if ($user) {
    //         $isCart = CartItem::whereHas('cart', function ($q) use ($user) {
    //             $q->where('user_id', $user->id);
    //         })
    //             ->where('item_id', $item->id)
    //             ->exists();

    //         $isWishlist = Wishlist::where('user_id', $user->id)
    //             ->where('item_id', $item->id)
    //             ->exists();
    //     }

    //     /*
    //     --------------------------------
    //     FORMAT RESPONSE
    //     --------------------------------
    //     */
    //     $data = [
    //         'id' => $item->id,
    //         'name' => $item->name,
    //         'slug' => $item->slug,
    //         'sku' => $item->sku,
    //         'price' => $item->price,
    //         'quantity' => $item->quantity,

    //         'stock_type' => $item->quantity > 20 ? 'in_stock' : 'low_stock',

    //         'thumbnail' => $item->images->first()
    //             ? asset('storage/' . $item->images->first()->image)
    //             : null,

    //         'images' => $item->images->map(function ($img) {
    //             return asset('storage/' . $img->image);
    //         }),

    //         'specifications' => $item->specifications,

    //         'is_cart' => $isCart,
    //         'is_wishlist' => $isWishlist,
    //     ];

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Item details fetched',
    //         'data' => $data
    //     ]);
    // }
    
//   public function shareProduct($slug)
//     {
//         try {
    
//             $item = Item::with(['images', 'category'])
//                 ->where('slug', $slug)
//                 ->firstOrFail();
    
//             $firstImage = $item->images->first();
    
//             $ogImage = $firstImage
//                 ? $firstImage->image_url
//                 : asset('images/default-product.jpg');

//             return view('admin.items.product_share', [
//                 'item'    => $item,
//                 'ogImage' => $ogImage,
//             ]);
    
//         } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    
//             return response()->view('errors.404', [
//                 'message' => 'Product not found'
//             ], 404);
    
//         } catch (\Exception $e) {
    
//             \Log::error('Share Product Error', [
//                 'slug'  => $slug,
//                 'error' => $e->getMessage()
//             ]);
    
//             return response()->view('errors.500', [
//                 'message' => 'Something went wrong.'
//             ], 500);
//         }
//     }
        
      public function show($slug)
    {
        $user = Auth::user();

        $item = Item::with([
            'images',
            'category',
            'reviews.reviewer'
        ])
            ->where('slug', $slug)
            ->first();

        if (!$item) {
            return response()->json([
                'status' => false,
                'message' => 'Item not found'
            ], 404);
        }

        /*
    --------------------------------
    CHECK CART & WISHLIST
    --------------------------------
    */
        $isCart = false;
        $isWishlist = false;

        if ($user) {
            $isCart = CartItem::whereHas('cart', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->where('item_id', $item->id)
                ->exists();

            $isWishlist = Wishlist::where('user_id', $user->id)
                ->where('item_id', $item->id)
                ->exists();
        }

        /*
    --------------------------------
    REVIEWS
    --------------------------------
    */
        $reviews = $item->reviews->map(function ($review) {
            return [
                'id' => $review->id,
                'reviewer_id' => $review->reviewer_id,
                'reviewer_name' => optional($review->reviewer)->full_name,
                'reviewer_image' => optional($review->reviewer)->profile_picture
                    ? asset('storage/' . $review->reviewer->profile_picture)
                    : null,

                'rating' => $review->rating,
                'comment' => $review->comment,
                'is_edited' => $review->is_edited,
                'edited_at' => $review->edited_at,
                'created_at' => $review->created_at,
            ];
        });

        /*
    --------------------------------
    AVERAGE RATING
    --------------------------------
    */
        $averageRating = round($item->reviews->avg('rating'), 1);

        /*
    --------------------------------
    FORMAT RESPONSE
    --------------------------------
    */
        $data = [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'sku' => $item->sku,
            'model' => $item->model,
            'price' => $item->price,
            'quantity' => $item->quantity,
            'description' => $item->description,

            'stock_type' => $item->quantity > 20
                ? 'in_stock'
                : 'low_stock',

            'thumbnail' => $item->images->first()
                ? asset('storage/' . $item->images->first()->image)
                : null,

            'images' => $item->images->map(function ($img) {
                return asset('storage/' . $img->image);
            }),
            
              'category' => $item->category ? [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                    'slug' => $item->category->slug,
                ] : null,

            'specifications' => $item->specifications,

            'is_cart' => $isCart,
            'is_wishlist' => $isWishlist,

            /*
        --------------------------------
        REVIEW DATA
        --------------------------------
        */
            'average_rating' => $averageRating,
            'total_reviews' => $item->reviews->count(),
            'reviews' => $reviews,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Item details fetched',
            'data' => $data
        ]);
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx'
        ]);

        DB::beginTransaction();

        try {

            $file = fopen($request->file('file')->getRealPath(), 'r');

            if (!$file) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unable to read file'
                ], 400);
            }

            $header = fgetcsv($file);
            $header = array_map(fn($h) => strtolower(trim($h)), $header);
            dd($header);
            $rowNumber = 1;

            while (($row = fgetcsv($file)) !== false) {

                $rowNumber++;

                if (count(array_filter($row)) === 0) {
                    continue;
                }

                $row = array_combine($header, $row);

                $validator = Validator::make($row, [
                    'name' => 'required|string|max:255',
                    'sku' => 'required|string|unique:items,sku',
                    'price' => 'required|numeric|min:0',
                    'quantity' => 'required|integer|min:0',
                    'category_id' => 'required|exists:categories,id',
                    'wattage' => 'nullable|string',
                    'color_temperature' => 'nullable|string',
                    'attributes' => 'nullable|string',
                    'image_urls' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    DB::rollBack();

                    return response()->json([
                        'status' => false,
                        'message' => "Error on row {$rowNumber}",
                        'errors' => $validator->errors()
                    ], 422);
                }

                $item = Item::create([
                    'name' => $row['name'],
                    'slug' => Str::slug($row['name']) . '-' . uniqid(),
                    'sku' => $row['sku'],
                    'price' => $row['price'],
                    'quantity' => $row['quantity'],
                    'category_id' => $row['category_id'],
                    'specifications' => [
                        'wattage' => $row['wattage'] ?? null,
                        'color_temperature' => $row['color_temperature'] ?? null,
                        'attributes' => !empty($row['attributes'])
                            ? array_map('trim', explode(',', $row['attributes']))
                            : []
                    ]
                ]);

                // IMAGE HANDLING
                if (!empty($row['image_urls'])) {

                    foreach (explode('|', $row['image_urls']) as $url) {

                        $url = trim($url);
                        if (!$url) continue;

                        try {
                            $response = Http::timeout(10)->get($url);

                            if (!$response->successful()) continue;

                            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';

                            $filename = 'items/' . uniqid() . '.' . $ext;

                            Storage::disk('public')->put($filename, $response->body());

                            $item->images()->create([
                                'image' => $filename
                            ]);
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }

            fclose($file);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Bulk items uploaded successfully'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx'
        ]);

        DB::beginTransaction();

        try {
            $file = fopen($request->file('file')->getRealPath(), 'r');

            if (!$file) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unable to read file'
                ], 400);
            }

            $header = fgetcsv($file);

            if (!$header) {
                return response()->json([
                    'status' => false,
                    'message' => 'CSV header missing'
                ], 400);
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            $header = array_map(fn($h) => strtolower(trim($h)), $header);

            /*
         CHECK REQUIRED COLUMN
        */
            if (!in_array('sku', $header)) {
                return response()->json([
                    'status' => false,
                    'message' => 'SKU column is required in file'
                ], 422);
            }

            $rowNumber = 1;
            $errors = [];
            $updatedCount = 0;

            while (($row = fgetcsv($file)) !== false) {

                $rowNumber++;

                if (count(array_filter($row)) === 0) continue;

                $row = array_pad($row, count($header), null);

                if (count($header) !== count($row)) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'error' => 'Column mismatch'
                    ];
                    continue;
                }

                $row = array_combine($header, $row);
                $row = $this->cleanUtf8($row);

                /*
            VALIDATION (SKU BASED)
            */
                $validator = Validator::make($row, [
                    'sku'         => 'required|exists:items,sku',
                    'name'        => 'required|string|max:255',
                    'price'       => 'required|numeric|min:0',
                    'quantity'    => 'required|integer|min:0',
                    'category_id' => 'required|exists:categories,id',
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'row'   => $rowNumber,
                        'error' => $validator->errors()->all()
                    ];
                    continue;
                }

                /*
             FIND ITEM BY SKU
            */
                $item = Item::where('sku', $row['sku'])->first();

                if (!$item) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'error' => 'Item not found for SKU: ' . $row['sku']
                    ];
                    continue;
                }

                $oldQty = $item->quantity;
                $newQty = (int) $row['quantity'];

                /*
             UPDATE ITEM
            */
                $item->update([
                    'name'        => $row['name'],
                    'slug'        => Str::slug($row['name']) . '-' . uniqid(),
                    'price'       => $row['price'],
                    'quantity'    => $newQty,
                    'category_id' => $row['category_id'],
                ]);

                /*
            STOCK INCREASE → SKU BASED MATCH
            */
                if ($newQty > $oldQty) {

                    $pendingUsers = DB::table('pending_order_items')
                        ->where('item_sku', $item->sku) // 🔥 KEY CHANGE
                        ->where('pending_qty', '>', 0)
                        ->pluck('user_id')
                        ->unique();

                    if ($pendingUsers->isNotEmpty()) {

                        $users = User::whereIn('id', $pendingUsers)->get();

                        foreach ($users as $user) {

                            /*
                         MAIL
                        */
                            try {
                              Mail::to($user->email)->send(
                                    new ItemBackInStockMail($user, $item)
                                );
                            } catch (\Exception $e) {
                                Log::error('Mail failed: ' . $e->getMessage());
                            }

                            /*
                        WHATSAPP
                        */
                            try {
                                if (!empty($user->phone)) {
                                    $this->sendWhatsAppStock(
                                        $user->phone,
                                        $user->name,
                                        $item->name
                                    );
                                }
                            } catch (\Exception $e) {
                                Log::error('WhatsApp failed: ' . $e->getMessage());
                            }
                        }
                    }
                }

                $updatedCount++;
            }

            fclose($file);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Bulk update completed. {$updatedCount} item(s) updated.",
                'errors'  => $errors
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $this->safeMessage($e->getMessage())
            ], 500);
        }
    }

    // public function bulkUpdate(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:csv,xlsx'
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         $file = fopen($request->file('file')->getRealPath(), 'r');

    //         if (!$file) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Unable to read file'
    //             ], 400);
    //         }

    //         $header = fgetcsv($file);

    //         if (!$header) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'CSV header missing'
    //             ], 400);
    //         }

    //         $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    //         $header = array_map(fn($h) => strtolower(trim($h)), $header);

    //         $rowNumber = 1;
    //         $errors = [];
    //         $updatedCount = 0;

    //         while (($row = fgetcsv($file)) !== false) {

    //             $rowNumber++;

    //             if (count(array_filter($row)) === 0) continue;

    //             $row = array_pad($row, count($header), null);

    //             if (count($header) !== count($row)) {
    //                 $errors[] = [
    //                     'row' => $rowNumber,
    //                     'error' => 'Column mismatch'
    //                 ];
    //                 continue;
    //             }

    //             $row = array_combine($header, $row);
    //             $row = $this->cleanUtf8($row);

    //             $validator = Validator::make($row, [
    //                 'id'          => 'required|exists:items,id',
    //                 'name'        => 'required|string|max:255',
    //                 'price'       => 'required|numeric|min:0',
    //                 'quantity'    => 'required|integer|min:0',
    //                 'category_id' => 'required|exists:categories,id',
    //             ]);

    //             if ($validator->fails()) {
    //                 $errors[] = [
    //                     'row'   => $rowNumber,
    //                     'error' => $validator->errors()->all()
    //                 ];
    //                 continue;
    //             }

    //             $item = Item::find($row['id']);

    //             if (!$item) continue;

    //             /*
    //         --------------------------------
    //          CHECK QUANTITY INCREASE
    //         --------------------------------
    //         */
    //             $oldQty = $item->quantity;
    //             $newQty = (int) $row['quantity'];

    //             /*
    //         --------------------------------
    //         UPDATE ITEM
    //         --------------------------------
    //         */
    //             $item->update([
    //                 'name'        => $row['name'],
    //                 'slug'        => Str::slug($row['name']) . '-' . uniqid(),
    //                 'price'       => $row['price'],
    //                 'quantity'    => $newQty,
    //                 'category_id' => $row['category_id'],
    //             ]);

    //             /*
    //         --------------------------------
    //          STOCK INCREASED → NOTIFY USERS
    //         --------------------------------
    //         */
    //             if ($newQty > $oldQty && $item->sku) {

    //                 $pendingUsers = DB::table('pending_order_items')
    //                     ->where('item_sku', $item->sku)
    //                     ->where('pending_qty', '>', 0)
    //                     ->pluck('user_id')
    //                     ->unique();

    //                 if ($pendingUsers->isNotEmpty()) {

    //                     $users = User::whereIn('id', $pendingUsers)->get();

    //                     foreach ($users as $user) {

    //                         /*
    //                     📧 MAIL
    //                     */
    //                         try {
    //                             Mail::raw(
    //                                 "Hi {$user->name}, your requested item '{$item->name}' is now back in stock. You can place your order now.",
    //                                 function ($message) use ($user) {
    //                                     $message->to($user->email)
    //                                         ->subject('Item Back in Stock');
    //                                 }
    //                             );
    //                         } catch (\Exception $e) {
    //                             Log::error('Mail failed: ' . $e->getMessage());
    //                         }

    //                         /*
    //                      WHATSAPP (Pseudo - integrate provider)
    //                     */
    //                         try {
    //                             if (!empty($user->phone)) {
    //                                 $this->sendWhatsAppStock(
    //                                     $user->phone,
    //                                     $user->name,
    //                                     $item->name
    //                                 );

    //                                 Log::info("WhatsApp sent to {$user->phone}");
    //                             }
    //                         } catch (\Exception $e) {
    //                             Log::error('WhatsApp failed: ' . $e->getMessage());
    //                         }
    //                     }
    //                 }
    //             }

    //             $updatedCount++;
    //         }

    //         fclose($file);

    //         DB::commit();

    //         return response()->json([
    //             'status'  => true,
    //             'message' => "Bulk update completed. {$updatedCount} item(s) updated.",
    //             'errors'  => $errors
    //         ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status'  => false,
    //             'message' => $this->safeMessage($e->getMessage())
    //         ], 500);
    //     }
    // }

    public function sendWhatsAppStock($phone, $userName, $itemName)
    {
        try {
            $sid   = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');

            $client = new \Twilio\Rest\Client($sid, $token);

            $message = "🔔 *Item Back in Stock!*\n\n"
                . "Hi {$userName},\n\n"
                . "The item *{$itemName}* you requested is now available.\n\n"
                . "You can place your order now before it goes out of stock again!\n\n"
                . "👉 Visit our store now.";

            $client->messages->create(
                "whatsapp:+91{$phone}",
                [
                    "from" => "whatsapp:+14155238886",
                    "body" => $message
                ]
            );
        } catch (\Exception $e) {
            Log::error('WhatsApp Stock Alert Failed: ' . $e->getMessage());
        }
    }

    private function cleanUtf8($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'cleanUtf8'], $data);
        }

        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }

        return $data;
    }

    private function safeMessage($message)
    {
        return mb_convert_encoding($message, 'UTF-8', 'UTF-8');
    }
    
        public function featuredItems()
    {
        try {

            $items = Item::with(['category', 'images'])
                ->where('is_featured', 1)
                ->latest()
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Featured items fetched successfully',
                'data' => $items
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getUniqueSpecifications()
    {
        try {
    
            $items = DB::table('items')
                ->whereNotNull('specifications')
                ->pluck('specifications');
    
            $finalSpecifications = [];
    
            foreach ($items as $specification) {
    
                $decoded = json_decode($specification, true);
    
                if (!$decoded || !is_array($decoded)) {
                    continue;
                }
    
                foreach ($decoded as $key => $value) {
    
                    // Create key if not exists
                    if (!isset($finalSpecifications[$key])) {
                        $finalSpecifications[$key] = [];
                    }
    
                    // If value is array
                    if (is_array($value)) {
    
                        foreach ($value as $val) {
    
                            if (!in_array($val, $finalSpecifications[$key])) {
                                $finalSpecifications[$key][] = $val;
                            }
                        }
    
                    } else {
    
                        // Single value
                        if (!in_array($value, $finalSpecifications[$key])) {
                            $finalSpecifications[$key][] = $value;
                        }
                    }
                }
            }
    
            // Optional: sort values
            foreach ($finalSpecifications as $key => $values) {
                sort($values);
                $finalSpecifications[$key] = array_values($values);
            }
    
            ksort($finalSpecifications);
    
            return response()->json($finalSpecifications);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    

    public function searchItems(Request $request)
    {
        try {
    
            $search = $request->search;
    
            $items = Item::with('images','category')
                ->where('type', 'online')
                ->where(function ($query) use ($search) {
    
                    // Search by item fields
                    $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('slug', 'LIKE', "%{$search}%")
                        ->orWhere('sku', 'LIKE', "%{$search}%")
                        ->orWhere('model', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
    
                    // Search by category name
                    $query->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
                })
                ->latest()
                ->get();
    
            if ($items->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No items found',
                    'data' => []
                ], 422);
            }
    
            return response()->json([
                'status' => true,
                'message' => 'Items fetched successfully',
                'total_items' => $items->count(),
                'data' => $items
            ]);
    
        } catch (\Throwable $th) {
    
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
