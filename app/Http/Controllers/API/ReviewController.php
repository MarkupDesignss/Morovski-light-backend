<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        // try {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.unauthorized')
            ], 401);
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'item_id'  => 'required|exists:items,id',
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'nullable|string|max:1000',
        ]);

        $order = Order::with('items')->find($request->order_id);

        /*
        --------------------------------
        CHECK OWNERSHIP
        --------------------------------
        */
        if ($order->user_id != $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Not authorized'
            ], 403);
        }

        /*
        --------------------------------
        CHECK ORDER DELIVERED
        --------------------------------
        */
        if ($order->order_status != 'delivered') {
            return response()->json([
                'status' => false,
                'message' => 'You can review only delivered orders'
            ], 400);
        }

        /*
        --------------------------------
        CHECK ITEM EXISTS IN ORDER
        --------------------------------
        */
        $itemExists = $order->items()
            ->where('item_id', $request->item_id)
            ->exists();

        if (!$itemExists) {
            return response()->json([
                'status' => false,
                'message' => 'Item not found in this order'
            ], 400);
        }

        /*
        --------------------------------
        PREVENT DUPLICATE REVIEW
        --------------------------------
        */
        $alreadyReviewed = Review::where('order_id', $order->id)
            ->where('item_id', $request->item_id)
            ->where('reviewer_id', $user->id)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'status' => false,
                'message' => 'You already reviewed this item'
            ], 400);
        }

        /*
        --------------------------------
        CREATE REVIEW
        --------------------------------
        */
        $review = Review::create([
            'reviewer_id' => $user->id,
            //'reviewer_id'     => $order->user_id,
            'order_id'    => $order->id,
            'item_id'     => $request->item_id,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
        ]);

        /*
        --------------------------------
        UPDATE ITEM RATING
        --------------------------------
        */
        $avgRating = Review::where('item_id', $request->item_id)->avg('rating');
        $count = Review::where('item_id', $request->item_id)->count();

        Item::where('id', $request->item_id)->update([
            'rating' => round($avgRating, 1),
            'reviews_count' => $count
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Review submitted successfully',
            'data' => $review
        ], 201);
    }
    
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'status' => false,
                    'message' => 'Review not found'
                ], 422);
            }

            /*
        --------------------------------
        OWNERSHIP CHECK
        --------------------------------
        */
            if ($review->reviewer_id != $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            /*
        --------------------------------
        EDIT WINDOW (7 DAYS LIMIT)
        --------------------------------
        */
            if ($review->created_at->diffInDays(now()) > 7) {
                return response()->json([
                    'status' => false,
                    'message' => 'Edit window expired'
                ], 403);
            }

            /*
        --------------------------------
        VALIDATION
        --------------------------------
        */
            $request->validate([
                'rating'  => 'nullable|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            if (!$request->filled('rating') && !$request->filled('comment')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Nothing to update'
                ], 400);
            }

            /*
        --------------------------------
        UPDATE REVIEW
        --------------------------------
        */
            $review->update([
                'rating'    => $request->rating ?? $review->rating,
                'comment'   => $request->comment ?? $review->comment,
                'is_edited' => true,
                'edited_at' => now(),
            ]);

            /*
        --------------------------------
        RECALCULATE ITEM RATING
        --------------------------------
        */
            $avgRating = Review::where('item_id', $review->item_id)->avg('rating');
            $reviewsCount = Review::where('item_id', $review->item_id)->count();

            Item::where('id', $review->item_id)->update([
                'rating' => round($avgRating, 1),
                'reviews_count' => $reviewsCount
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Review updated successfully',
                'data' => $review
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request, $itemId)
    {
        try {
            //  Get item (only selected field+s)
            $item = Item::select('id', 'name', 'slug', 'price', 'rating', 'reviews_count')
                ->find($itemId);

            if (!$item) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.no_item'),
                ], 422);
            }

            //  Reviews query
            $query = Review::with(['reviewer:id,full_name,profile_picture'])
                ->where('item_id', $itemId);

            if ($request->has('rating')) {
                $query->where('rating', $request->rating);
            }

            $reviews = $query->latest()->paginate(10);

            //  Stats
            $avgRating = Review::where('item_id', $itemId)->avg('rating');

            $distribution = Review::where('item_id', $itemId)
                ->selectRaw('rating, COUNT(*) as total')
                ->groupBy('rating')
                ->pluck('total', 'rating');

            return response()->json([
                'status' => true,
                'data' => [
                    // 'item' => $item,
                    'average_rating' => round($avgRating, 1),
                    'reviews' => $reviews,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getTestimonials()
    {
        $testimonials = Review::with('reviewer:id,full_name,profile_picture')
            ->where('rating', '>=', 4)
            ->whereNotNull('comment')
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Check if collection is empty
        if ($testimonials->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.no_testimonials'),
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.testimonials_fetched'),
            'data' => $testimonials
        ], 200);
    }
}
