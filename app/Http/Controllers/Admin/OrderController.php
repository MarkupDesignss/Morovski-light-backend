<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAllocation;
use App\Models\Payment;
use App\Models\ShippingAddress;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedMail;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OrderItemDelivery;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\Item;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;

class OrderController extends Controller
{
    // public function index()
    // {
    //     // allow filtering by account_type via query string e.g. ?account_type=b2b
    //     $query = Order::with(['user', 'items.item'])->latest();

    //     if (request()->filled('account_type') && in_array(request('account_type'), ['b2b', 'b2c'])) {
    //         $acctType = request('account_type');
    //         $query->whereHas('user', function ($q) use ($acctType) {
    //             $q->where('account_type', $acctType);
    //         });
    //     }

    //     $orders = $query->get();

    //     return view('admin.orders.index', compact('orders'));
    // }

    public function index()
    {
        $query = Order::with(['user', 'items.item'])->where('order_status','!=','pending')->latest();

        if (request()->filled('account_type') && in_array(request('account_type'), ['b2b', 'b2c'])) {
            $acctType = request('account_type');
            $query->whereHas('user', function ($q) use ($acctType) {
                $q->where('account_type', $acctType);
            });
        }

        $orders = $query->paginate(10);

        // load active warehouses for dispatch selection
        $warehouses = Warehouse::where('is_active', true)->get();

        // build warehouse stock map for items on this page
        $itemIds = [];
        $orderItemIds = [];
        foreach ($orders as $order) {
            foreach ($order->items as $orderItem) {
                $itemIds[] = $orderItem->item_id;
                $orderItemIds[] = $orderItem->id;
            }
        }
        $itemIds = array_values(array_unique($itemIds));
        $orderItemIds = array_values(array_unique($orderItemIds));
        $warehouseStocks = [];
        if (!empty($itemIds)) {
            $whItems = WarehouseItem::whereIn('item_id', $itemIds)->get();
            foreach ($whItems as $wi) {
                $warehouseStocks[$wi->item_id][$wi->warehouse_id] = $wi->quantity;
            }
        }

        $pendingAllocations = OrderItemAllocation::whereIn('order_item_id', $orderItemIds)
            ->where('status', 'pending')
            ->groupBy('order_item_id')
            ->select('order_item_id', DB::raw('COUNT(*) as count'))
            ->pluck('count', 'order_item_id')
            ->toArray();

        return view('admin.orders.index', compact('orders', 'warehouses', 'warehouseStocks', 'pendingAllocations'));
    }

    // public function show($id)
    // {
    //     $order = Order::with([
    //         'user.businessProfile',
    //         'items.item.images',
    //         'payment',
    //         'shippingAddress'
    //     ])->findOrFail($id);

    //     return view('admin.orders.show', compact('order'));
    // }

    public function show($id)
    {
        $order = Order::with([
            'user.businessProfile',
            'items.item.images',
            'payment',
            'shippingAddress',
            'promocode'
        ])->findOrFail($id);

        $subtotal = $order->subtotal ?? 0;

        /*
        |--------------------------------------------------------------------------
        | B2B Discount
        |--------------------------------------------------------------------------
        */
        $businessDiscount = 0;

        if (
            $order->user &&
            $order->user->account_type === 'b2b' &&
            $order->user->businessProfile
        ) {
            $discountPercentage = $order->user->businessProfile->discountpercentage ?? 0;

            $businessDiscount = ($subtotal * $discountPercentage) / 100;
        }

        /*
        |--------------------------------------------------------------------------
        | Promocode Discount
        |--------------------------------------------------------------------------
        */
        $promocodeDiscount = 0;

        if ($order->promocode_id && $order->promocode_discount) {
            $promocodeDiscount = $order->promocode_discount;
        }

        /*
        |--------------------------------------------------------------------------
        | Shipping Charges
        |--------------------------------------------------------------------------
        */
        $shippingCharges = $order->shipping_charges ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Grand Total
        |--------------------------------------------------------------------------
        */
        $grandTotal = (
            $subtotal
            - $businessDiscount
            - $promocodeDiscount
        ) + $shippingCharges;

        return view('admin.orders.show', compact(
            'order',
            'subtotal',
            'businessDiscount',
            'promocodeDiscount',
            'shippingCharges',
            'grandTotal'
        ));
    }

    // public function dispatchItem(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $request->validate([
    //             'order_item_id' => 'required|exists:order_items,id',
    //             'quantity' => 'nullable|numeric|min:1',
    //             'warehouse_id' => 'nullable|exists:warehouses,id',
    //             'allocations' => 'nullable|array',
    //             'allocations.*.warehouse_id' => 'required_with:allocations|exists:warehouses,id',
    //             'allocations.*.quantity' => 'required_with:allocations|numeric|min:1',
    //         ]);

    //         $orderItem = OrderItem::with(['order', 'item'])->find($request->order_item_id);

    //         $remaining = $orderItem->quantity - $orderItem->dispatched_qty;

    //         // build allocations array from either allocations[] or single warehouse_id+quantity
    //         $allocations = [];
    //         if ($request->filled('allocations')) {
    //             $allocations = $request->input('allocations');
    //         } elseif ($request->filled('warehouse_id') && $request->filled('quantity')) {
    //             $allocations = [
    //                 ['warehouse_id' => $request->warehouse_id, 'quantity' => (int)$request->quantity]
    //             ];
    //         } else {
    //             return response()->json(['status' => false, 'message' => 'No allocation provided'], 422);
    //         }

    //         $totalQty = collect($allocations)->sum('quantity');

    //         if ($totalQty > $remaining) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Exceeds remaining quantity'
    //             ]);
    //         }

    //         // validate stock for each allocation and prepare warehouse items to decrement
    //         $warehouseItemsToUpdate = [];
    //         foreach ($allocations as $alloc) {
    //             $wh = Warehouse::findOrFail($alloc['warehouse_id']);
    //             $wi = WarehouseItem::where('warehouse_id', $wh->id)
    //                 ->where('item_id', $orderItem->item_id)
    //                 ->first();
    //             if (!$wi) {
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => 'Item not found in selected warehouse',
    //                     'warehouse_id' => $wh->id
    //                 ], 422);
    //             }
    //             if ($wi->quantity < $alloc['quantity']) {
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => 'Insufficient stock in selected warehouse',
    //                     'warehouse_id' => $wh->id,
    //                     'available' => $wi->quantity,
    //                     'required' => $alloc['quantity']
    //                 ], 422);
    //             }
    //             $warehouseItemsToUpdate[] = ['warehouse_item' => $wi, 'qty' => (int)$alloc['quantity']];
    //         }

    //         /*
    //     --------------------------------
    //     CREATE DISPATCH INVOICE
    //     --------------------------------
    //     */
    //         $invoice = Invoice::create([
    //             'order_id' => $orderItem->order_id,
    //             'invoice_number' => 'INV-' . time(),
    //             'type' => 'dispatch',
    //             'total_amount' => $totalQty * $orderItem->unit_price
    //         ]);

    //         /*
    //     --------------------------------
    //     CREATE INVOICE ITEM
    //     --------------------------------
    //     */
    //         InvoiceItem::create([
    //             'invoice_id' => $invoice->id,
    //             'order_item_id' => $orderItem->id,
    //             'quantity' => $totalQty,
    //             'unit_price' => $orderItem->unit_price,
    //             'total_price' => $totalQty * $orderItem->unit_price
    //         ]);

    //         /*
    //     --------------------------------
    //     CREATE DELIVERY ENTRY
    //     --------------------------------
    //     */
    //         OrderItemDelivery::create([
    //             'order_item_id' => $orderItem->id,
    //             'invoice_id' => $invoice->id,
    //             'quantity' => $totalQty,
    //             'status' => 'shipped',
    //             'shipped_at' => now()
    //         ]);

    //         // update dispatched quantity on order item
    //         $orderItem->dispatched_qty += $totalQty;
    //         $orderItem->save();

    //         // deduct from each warehouse's stock
    //         foreach ($warehouseItemsToUpdate as $wup) {
    //             $wup['warehouse_item']->decrement('quantity', $wup['qty']);
    //         }

    //         // deduct from main item stock
    //         Item::where('id', $orderItem->item_id)->decrement('quantity', $totalQty);

    //         DB::commit();

    //         /*
    //     --------------------------------
    //     SEND MAIL (DISPATCH INVOICE)
    //     --------------------------------
    //     */
    //         Mail::to($orderItem->order->user->email)
    //             ->send(new OrderPlacedMail($orderItem->order, 'dispatch', $invoice));

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Item dispatched successfully',
    //             'data' => [
    //                 'invoice_id' => $invoice->id
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }

    public function dispatchItem(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'order_item_id' => ['required', 'exists:order_items,id'],
                'allocations' => ['required', 'array', 'min:1'],
                'allocations.*.warehouse_id' => ['required', 'exists:warehouses,id'],
                'allocations.*.quantity' => ['required', 'integer', 'min:1'],
            ]);

            $orderItem = OrderItem::with('order')->findOrFail($request->order_item_id);
            $remainingQty = $orderItem->quantity - $orderItem->dispatched_qty;

            if ($remainingQty <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'This order item is already fully dispatched.'
                ], 422);
            }

            $pendingAllocationExists = OrderItemAllocation::where('order_item_id', $orderItem->id)
                ->where('status', 'pending')
                ->exists();

            if ($pendingAllocationExists) {
                return response()->json([
                    'status' => false,
                    'message' => 'This order item already has a pending allocation request.'
                ], 422);
            }

            $allocations = collect($request->allocations)->map(function ($alloc) {
                return [
                    'warehouse_id' => (int)$alloc['warehouse_id'],
                    'quantity' => (int)$alloc['quantity'],
                ];
            });

            $totalQty = $allocations->sum('quantity');

            if ($totalQty > $remainingQty) {
                return response()->json([
                    'status' => false,
                    'message' => 'Total allocation quantity exceeds remaining order item quantity.'
                ], 422);
            }

            $warehouseIds = $allocations->pluck('warehouse_id')->unique()->values()->all();
            $createdAllocations = [];

            foreach ($allocations as $alloc) {
                $warehouse = Warehouse::findOrFail($alloc['warehouse_id']);
                $warehouseItem = WarehouseItem::where('warehouse_id', $warehouse->id)
                    ->where('item_id', $orderItem->item_id)
                    ->first();

                if (!$warehouseItem) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Item not found in selected warehouse.',
                        'warehouse_id' => $warehouse->id
                    ], 422);
                }

                if ($warehouseItem->quantity < $alloc['quantity']) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Insufficient stock in selected warehouse.',
                        'warehouse_id' => $warehouse->id,
                        'available_stock' => $warehouseItem->quantity,
                        'required_stock' => $alloc['quantity'],
                    ], 422);
                }
                $createdAllocations[] = OrderItemAllocation::create([
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'warehouse_id' => $warehouse->id,
                    'admin_id' => Auth::id(),
                    'allocated_qty' => $alloc['quantity'],
                    'dispatched_qty' => 0,
                    'status' => 'pending',
                ]);
            }

            $warehouseManagers = User::whereIn('warehouse_id', $warehouseIds)
                ->where('is_active', 1)
                ->get();

            foreach ($warehouseManagers as $manager) {
                Notification::create([
                    'user_id' => $manager->id,
                    'type' => 'warehouse_item_allocation',
                    'title' => 'New Warehouse Allocation',
                    'message' => "A new warehouse allocation request has been created for Order #{$orderItem->order_id}.",
                    'reference_type' => 'order',
                    'reference_id' => $orderItem->order_id,
                    'priority' => 'high',
                    'extra_data' => json_encode([
                        'order_item_id' => $orderItem->id,
                        'warehouse_id' => $manager->warehouse_id,
                        'allocated_qty' => $totalQty,
                    ]),
                ]);

                try {
                    if (!empty($manager->phone)) {
                        $message = "New allocation request for Order #{$orderItem->order_id}. Please review and dispatch from your warehouse.";

                        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
                        $twilio->messages->create(
                            "whatsapp:+91{$manager->phone}",
                            [
                                'from' => env('TWILIO_WHATSAPP_NUMBER'),
                                'body' => $message,
                            ]
                        );
                    }
                } catch (\Exception $twilioError) {
                    Log::error('Twilio WhatsApp Error: ' . $twilioError->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Allocation request sent to warehouse manager(s).',
                'data' => [
                    'order_item_id' => $orderItem->id,
                    'total_allocated_qty' => $totalQty,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}
