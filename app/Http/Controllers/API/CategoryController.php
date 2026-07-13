<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;

class CategoryController extends Controller
{

    // public function index()
    // {
    //     try {
    //         $categories = Category::where('is_active', 1)
    //             ->whereNull('parent_id')
    //             ->withCount(['items' => function ($query) {
    //                 $query->where('status', 'active');
    //             }])
    //             ->orderBy('sort_order')
    //             ->get();

    //         if ($categories->isEmpty()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => __('messages.no_categories_found'),
    //             ], 200);
    //         }

    //         return response()->json([
    //             'status' => true,
    //             'categories' => $categories
    //         ]);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => __('messages.categories_fetch_error'),
    //             'error' => $th->getMessage()
    //         ], 500);
    //     }
    // }
    public function index()
    {
        try {
            $priceRange = Item::where('type', 'online')
                ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
                ->first();

            $categories = Category::where('is_active', 1)
                ->whereNull('parent_id')
                ->withCount([
                    'items as items_count' => function ($query) {
                        $query->where('type', 'online');
                    }
                ])
                ->orderBy('sort_order')
                ->get();

            if ($categories->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.no_categories_found'),
                ], 422);
            }

            return response()->json([
                'status' => true,

                // Top level min/max price
                'price_range' => [
                    'min_price' => $priceRange->min_price,
                    'max_price' => $priceRange->max_price,
                ],

                // Categories with item count
                'categories' => $categories
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'status' => false,
                'message' => __('messages.categories_fetch_error'),
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
