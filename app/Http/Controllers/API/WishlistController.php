<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    public function add(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'item_id' => 'required|exists:items,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $exists = Wishlist::where('user_id', $user->id)
                ->where('item_id', $request->item_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.already_in_wishlist')
                ], 400);
            }

            Wishlist::create([
                'user_id' => $user->id,
                'item_id' => $request->item_id,
            ]);

            //  increment count
            Item::where('id', $request->item_id)->increment('wishlist_count');

            return response()->json([
                'status' => true,
                'message' => __('messages.added_to_wishlist')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function remove(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            $wishlist = Wishlist::where('user_id', $user->id)
                ->where('item_id', $request->item_id)
                ->first();

            if (!$wishlist) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.item_not_in_wishlist')
                ], 404);
            }

            $wishlist->delete();

            //  decrement count safely
            Item::where('id', $request->item_id)
                ->where('wishlist_count', '>', 0)
                ->decrement('wishlist_count');

            return response()->json([
                'status' => true,
                'message' => __('messages.removed_from_wishlist')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function index()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            $wishlist = Wishlist::with([
                'item:id,name,slug,price,wishlist_count',
                'item.images'
            ])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);

            return response()->json([
                'status' => true,
                'data' => $wishlist
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
