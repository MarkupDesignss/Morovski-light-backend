<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReplacementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ReplacementController extends Controller
{
    // public function store(Request $request)
    // {
    //     try {

    //         $validator = Validator::make($request->all(), [
    //             'order_id' => 'required|exists:orders,id',
    //             'reason' => 'required|in:defective,quality_issue,different_size,wrong_item,damaged,other',
    //             'message' => 'nullable|string',
    //             'items' => 'required|array|min:1',
    //             'items.*.order_item_id' => 'required|exists:order_items,id',
    //             'items.*.quantity' => 'required|integer|min:1',
    //             'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Validation error',
    //                 'errors' => $validator->errors()
    //             ], 422);
    //         }

    //         $user = Auth::user();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | Check Order
    //     |--------------------------------------------------------------------------
    //     */
    //         $order = Order::where('id', $request->order_id)
    //             ->where('user_id', $user->id)
    //             ->first();

    //         if (!$order) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Order not found'
    //             ], 422);
    //         }


    //         if ($order->order_status != 'delivered') {

    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Replacement is only allowed for delivered orders.'
    //             ], 422);
    //         }

    //         $deliveredDate = \Carbon\Carbon::parse($order->updated_at);

    //         if ($deliveredDate->diffInDays(now()) > 7) {

    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Replacement request can only be created within 7 days of delivery.'
    //             ], 422);
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | Check If Any Request Already In Process
    //     |--------------------------------------------------------------------------
    //     */
    //         $processedRequest = ReplacementRequest::where('user_id', $user->id)
    //             ->where('order_id', $request->order_id)
    //             ->whereIn('status', ['approved', 'rejected', 'processing'])
    //             ->first();

    //         if ($processedRequest) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Replacement request is already under process'
    //             ], 422);
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | Check Existing Pending Request
    //     |--------------------------------------------------------------------------
    //     */
    //         $existingRequest = ReplacementRequest::where('user_id', $user->id)
    //             ->where('order_id', $request->order_id)
    //             ->where('status', 'pending')
    //             ->first();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | Validate Order Items Belong To Order
    //     |--------------------------------------------------------------------------
    //     */
    //         $orderItemIds = $order->items()->pluck('id')->toArray();

    //         foreach ($request->items as $item) {

    //             if (!in_array($item['order_item_id'], $orderItemIds)) {

    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => 'Invalid order item selected'
    //                 ], 422);
    //             }
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | Upload Images
    //     |--------------------------------------------------------------------------
    //     */
    //         $uploadedImages = [];

    //         if ($request->hasFile('images')) {

    //             foreach ($request->file('images') as $image) {

    //                 try {

    //                     $path = $image->store('replacement_requests', 'public');

    //                     $uploadedImages[] = $path;
    //                 } catch (\Exception $e) {

    //                     return response()->json([
    //                         'status' => false,
    //                         'message' => 'Failed to upload image',
    //                         'error' => $e->getMessage()
    //                     ], 500);
    //                 }
    //             }
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | Update Existing Pending Request
    //     |--------------------------------------------------------------------------
    //     */
    //         if ($existingRequest) {

    //             $existingImages = is_array($existingRequest->images)
    //                 ? $existingRequest->images
    //                 : [];

    //             $existingRequest->update([
    //                 'reason' => $request->reason,
    //                 'message' => $request->message,
    //                 'items' => $request->items,
    //                 'images' => array_merge($existingImages, $uploadedImages),
    //                 'request_date' => now(),
    //             ]);

    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Existing replacement request updated successfully',
    //                 'data' => $existingRequest->fresh()
    //             ], 200);
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | Create New Replacement Request
    //     |--------------------------------------------------------------------------
    //     */
    //         $replacement = ReplacementRequest::create([
    //             'user_id' => $user->id,
    //             'order_id' => $order->id,
    //             'request_number' => 'REP-' . strtoupper(uniqid()),
    //             'reason' => $request->reason,
    //             'message' => $request->message,
    //             'images' => $uploadedImages,
    //             'items' => $request->items,
    //             'request_date' => now(),
    //             'status' => 'pending'
    //         ]);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Replacement request submitted successfully',
    //             'data' => $replacement
    //         ], 201);
    //     } catch (\Illuminate\Database\QueryException $e) {

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Database error occurred',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
   public function store(Request $request)
    {
        try {
    
            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */
    
            $validator = Validator::make($request->all(), [
    
                'order_id' => 'required|exists:orders,id',
    
                'reason' => 'required|in:defective,quality_issue,different_size,wrong_item,damaged,other',
    
                'message' => 'nullable|string',
    
                'items' => 'required|array|min:1',
    
                /*
                |--------------------------------------------------------------------------
                | NOW VALIDATING item_id INSTEAD OF order_item_id
                |--------------------------------------------------------------------------
                */
    
                'items.*.order_item_id' => 'required|exists:items,id',
    
                'items.*.quantity' => 'required|integer|min:1',
    
                'images.*' => 'nullable|image|mimes:jpg,jpeg,png'
    
            ]);
    
            if ($validator->fails()) {
    
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors()
                ], 422);
            }
    
            $user = Auth::user();
    
            /*
            |--------------------------------------------------------------------------
            | CHECK ORDER
            |--------------------------------------------------------------------------
            */
    
            $order = Order::with('items')
                ->where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->first();
    
            if (!$order) {
    
                return response()->json([
                    'status'  => false,
                    'message' => 'Order not found'
                ], 422);
            }
    
            /*
            |--------------------------------------------------------------------------
            | ONLY DELIVERED ORDERS
            |--------------------------------------------------------------------------
            */
    
            if ($order->order_status != 'delivered') {
    
                return response()->json([
                    'status'  => false,
                    'message' => 'Replacement is only allowed for delivered orders.'
                ], 422);
            }
    
            /*
            |--------------------------------------------------------------------------
            | 15 DAYS CHECK
            |--------------------------------------------------------------------------
            */
    
            $deliveredDate = \Carbon\Carbon::parse($order->updated_at);
    
            if ($deliveredDate->diffInDays(now()) > 15) {
    
                return response()->json([
                    'status'  => false,
                    'message' => 'Replacement request can only be created within 15 days of delivery.'
                ], 422);
            }
    
            /*
            |--------------------------------------------------------------------------
            | EXISTING REQUEST CHECK
            |--------------------------------------------------------------------------
            */
    
            $existingRequest = ReplacementRequest::where('user_id', $user->id)
                ->where('order_id', $request->order_id)
                ->whereIn('status', [
                    'pending',
                    'approved',
                    'received'
                ])
                ->first();
    
            if ($existingRequest) {
    
                return response()->json([
                    'status'  => false,
                    'message' => 'Replacement request already exists for this order.'
                ], 422);
            }
    
            /*
            |--------------------------------------------------------------------------
            | VALIDATE ITEMS BELONG TO ORDER
            |--------------------------------------------------------------------------
            */
    
            $orderItemIds = $order->items->pluck('item_id')->toArray();
    
            foreach ($request->items as $item) {
    
                /*
                |--------------------------------------------------------------------------
                | CHECK ITEM EXISTS IN ORDER
                |--------------------------------------------------------------------------
                */
    
                if (!in_array($item['order_item_id'], $orderItemIds)) {
    
                    return response()->json([
                        'status'  => false,
                        'message' => 'Invalid item selected for this order'
                    ], 422);
                }
    
                /*
                |--------------------------------------------------------------------------
                | GET ORDER ITEM
                |--------------------------------------------------------------------------
                */
    
                $orderItem = $order->items
                    ->where('item_id', $item['order_item_id'])
                    ->first();
    
                if (!$orderItem) {
    
                    return response()->json([
                        'status'  => false,
                        'message' => 'Order item not found'
                    ], 422);
                }
    
                /*
                |--------------------------------------------------------------------------
                | CHECK QUANTITY
                |--------------------------------------------------------------------------
                */
    
                if ($item['quantity'] > $orderItem->quantity) {
    
                    return response()->json([
                        'status'  => false,
                        'message' => 'Replacement quantity exceeds ordered quantity'
                    ], 422);
                }
            }
    
            /*
            |--------------------------------------------------------------------------
            | UPLOAD IMAGES
            |--------------------------------------------------------------------------
            */
    
            $uploadedImages = [];
    
            if ($request->hasFile('images')) {
    
                foreach ($request->file('images') as $image) {
    
                    try {
    
                        $path = $image->store(
                            'replacement_requests',
                            'public'
                        );
    
                        $uploadedImages[] = $path;
    
                    } catch (\Exception $e) {
    
                        return response()->json([
                            'status'  => false,
                            'message' => 'Failed to upload image',
                            'error'   => $e->getMessage()
                        ], 500);
                    }
                }
            }
    
            /*
            |--------------------------------------------------------------------------
            | CREATE REQUEST
            |--------------------------------------------------------------------------
            */
    
            $replacement = ReplacementRequest::create([
    
                'user_id'        => $user->id,
    
                'order_id'       => $order->id,
    
                'request_number' => 'REP-' . strtoupper(uniqid()),
    
                'reason'         => $request->reason,
    
                'message'        => $request->message,
    
                'images'         => $uploadedImages,
    
                'items'          => $request->items,
    
                'request_date'   => now(),
    
                'status'         => 'pending'
            ]);
    
            return response()->json([
    
                'status'  => true,
    
                'message' => 'Replacement request submitted successfully',
    
                'data'    => $replacement
    
            ], 201);
    
        } catch (\Illuminate\Database\QueryException $e) {
    
            return response()->json([
                'status'  => false,
                'message' => 'Database error occurred',
                'error'   => $e->getMessage()
            ], 500);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function cancelReplacementRequest($id)
    {
        try {

            $user = Auth::user();

            $replacement = ReplacementRequest::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$replacement) {

                return response()->json([
                    'status' => false,
                    'message' => 'Replacement request not found'
                ], 404);
            }

            // Optional:
            // Only pending requests can be cancelled
            if ($replacement->status != 'pending') {

                return response()->json([
                    'status' => false,
                    'message' => 'Only pending replacement requests can be cancelled'
                ], 422);
            }

            $replacement->delete();

            return response()->json([
                'status' => true,
                'message' => 'Replacement request cancelled successfully'
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function index()
    // {
    //     $user = Auth::user();

    //     $requests = ReplacementRequest::with('order')
    //         ->where('user_id', $user->id)
    //         ->latest()
    //         ->get();

    //     return response()->json([
    //         'status' => true,
    //         'data' => $requests
    //     ]);
    // }
    
   public function index()
    {
        $warehouseId = Auth::user()->warehouse_id;
        dd($warehouseId);
        $requests = ReplacementRequest::select('replacement_requests.*')
            ->with('order')
            ->join('order_items', function ($join) {
                $join->on('replacement_requests.order_id', '=', 'order_items.order_id')
                     ->on('replacement_requests.item_id', '=', 'order_items.item_id');
            })
            ->where('order_items.warehouse_id', $warehouseId)
            ->latest('replacement_requests.id')
            ->get();
    
        return response()->json([
            'status' => true,
            'data' => $requests
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();

        $requestData = ReplacementRequest::with('order')
            ->where('user_id', $user->id)
            ->find($id);

        if (!$requestData) {

            return response()->json([
                'status' => false,
                'message' => 'Replacement request not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $requestData
        ]);
    }
}
