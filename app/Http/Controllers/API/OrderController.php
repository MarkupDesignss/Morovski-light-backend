<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Wishlist;
use App\Models\ReplacementRequest;
use App\Models\Payment;
use App\Models\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedMail;
use App\Models\Notification;
use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\OrderItemDelivery;
use App\Models\PiPayment;
use App\Models\Promocode;
use App\Models\User;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;


class OrderController extends Controller
{

    public function dispatchItem(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'order_item_id' => ['required', 'exists:order_items,id'],
                'quantity'      => ['required', 'numeric', 'min:1'],
            ]);

            $orderItem = OrderItem::with(['order.user'])->findOrFail($validated['order_item_id']);

            $remainingQty = $orderItem->quantity - $orderItem->dispatched_qty;

            if ($validated['quantity'] > $remainingQty) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Dispatch quantity exceeds remaining quantity',
                    'remaining_qty' => $remainingQty
                ], 422);
            }

            $invoice = Invoice::create([
                'order_id'       => $orderItem->order_id,
                'invoice_number' => 'INV-' . now()->timestamp,
                'type'           => 'dispatch',
                'total_amount'   => $validated['quantity'] * $orderItem->unit_price,
            ]);

            InvoiceItem::create([
                'invoice_id'    => $invoice->id,
                'order_item_id' => $orderItem->id,
                'quantity'      => $validated['quantity'],
                'unit_price'    => $orderItem->unit_price,
                'total_price'   => $validated['quantity'] * $orderItem->unit_price,
            ]);

            OrderItemDelivery::create([
                'order_item_id' => $orderItem->id,
                'invoice_id'    => $invoice->id,
                'quantity'      => $validated['quantity'],
                'status'        => 'shipped',
                'shipped_at'    => now(),
            ]);

            $orderItem->increment('dispatched_qty', $validated['quantity']);

            DB::commit();
            if ($orderItem->order->user && $orderItem->order->user->email) {
                // dd($orderItem->order->user, $orderItem->order->user->email);
                Mail::to($orderItem->order->user->email)
                    ->send(new OrderPlacedMail(
                        $orderItem->order,
                        'dispatch',
                        $invoice
                    ));
            }

            return response()->json([
                'status'  => true,
                'message' => 'Item dispatched successfully',
                'data'    => [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'dispatched_qty' => $orderItem->dispatched_qty,
                    'remaining_qty'  => $remainingQty - $validated['quantity']
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function myOrdersItems($id)
    {
        try {
            $orders = OrderItem::with(['item.images', 'order'])
                ->where('order_id', $id)
                ->latest()
                ->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'Orders fetched successfully',
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'total' => $orders->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function sendWhatsApp($phone, $order)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');

        $client = new \Twilio\Rest\Client($sid, $token);

        // Get items list
        $itemsText = '';
        foreach ($order->items as $item) {
            $itemsText .= "- {$item->product_name} (Qty: {$item->quantity})\n";
        }

        // Get address
        $address = $order->shippingAddress;
        $addressText = $address
            ? "{$address->name}, {$address->address}, {$address->city}, {$address->state} - {$address->pincode}"
            : 'N/A';

        // Prepare message
        $message = " *Order Confirmed!*\n\n"
            . " Name: {$order->user->full_name}\n"
            . " Order ID: {$order->order_number}\n\n"
            . " *Items:*\n{$itemsText}\n"
            . " Total Amount: ₹{$order->total_amount}\n"
            . " Date: " . now()->format('d M Y, h:i A') . "\n\n"
            . "Thank you for shopping with us! ";

        // Send message
        $client->messages->create(
            "whatsapp:+91" . $phone,
            [
                "from" => "whatsapp:+14155238886",
                "body" => $message
            ]
        );
    }

    public function placeOrder(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'address_id'        => 'required|exists:shipping_addresses,id',
                'payment_method'    => 'required|in:card,upi,bank_transfer',
                'final_amount'      => 'required|numeric|min:1',
                'pay_amount'        => 'required|numeric|min:0',
                'b2b_discount'      => 'nullable|numeric',
                'promo_discount'    => 'nullable|numeric',
                'shipping_charges'  => 'required|numeric',
                'gst_amount'        => 'nullable|numeric',
                'order_date'        => 'nullable|date',
            ]);

            $user = Auth::user();
            $cart = Cart::with(['items.item'])->where('user_id', $user->id)->first();


            if (!$cart || $cart->items->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'Cart is empty']);
            }

            $address = ShippingAddress::where('id', $request->address_id)
                ->where('user_id', $user->id)
                ->first();
            if (!$address) {
                return response()->json(['status' => false, 'message' => 'Invalid address']);
            }

            $isB2B = ($user->account_type === 'b2b');

            // ==================== B2B FLOW (FRD Compliant) ====================
            if ($isB2B) {
                // 1. Create Proforma Invoice (no order, no payment yet)
                $invoice = Invoice::create([
                    'order_id'       => null,   // No order linked yet
                    'invoice_number' => 'PRI-' . time(),
                    'type'           => 'proforma',
                    'total_amount'   => (float) $request->final_amount,
                    'pi_status'      => 'B2B Web Request',   // Special queue status
                    'client_id'      => $user->id,
                    'payment_terms'  => null,
                    'valid_until'    => now()->addDays(15),
                ]);

                // 2. Add cart items to InvoiceItems (full quantity, no stock check)
                foreach ($cart->items as $cartItem) {
                    $item = $cartItem->item;
                    InvoiceItem::create([
                        'invoice_id'    => $invoice->id,
                        'item_id'       => $item->id,
                        'order_item_id' => null,
                        'quantity'      => $cartItem->quantity,
                        'unit_price'    => $item->price,
                        'total_price'   => $cartItem->quantity * $item->price,
                    ]);
                }

                // 3. Notify all Sales Executives (in-app notification)
                $salesExecutives = User::join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->join('roles', 'roles.id', '=', 'role_users.role_id')
                    ->where('roles.slug', 'sales-executive')
                    ->select('users.*')
                    ->get();

                foreach ($salesExecutives as $executive) {
                    Notification::create([
                        'user_id'        => $executive->id,
                        'type'           => 'b2b_web_request',
                        'title'          => 'New B2B Web Request',
                        'message'        => "New B2B request from {$user->full_name} (PI: {$invoice->invoice_number})",
                        'reference_type' => 'invoice',
                        'reference_id'   => $invoice->id,
                        'priority'       => 'high',
                        'extra_data'     => json_encode([
                            'invoice_id'      => $invoice->id,
                            'invoice_number'  => $invoice->invoice_number,
                            'customer_name'   => $user->full_name,
                            'customer_email'  => $user->email,
                            'total_amount'    => $invoice->total_amount,
                        ]),
                    ]);
                    // Optional: send email to Sales Exec
                    // Mail::to($executive->email)->send(...);
                }

                // 4. Send proforma invoice email to B2B customer (for reference)
                Mail::to($user->email)->send(new OrderPlacedMail(null, 'proforma', $invoice));
                // if ($user->phone) {
                //     $this->sendWhatsAppPI($user->phone, $invoice);
                // }

                // 5. Clear cart
                CartItem::where('cart_id', $cart->id)->delete();
                $cart->delete();

                DB::commit();

                return response()->json([
                    'status'  => true,
                    'message' => 'Proforma Invoice created. Our sales team will contact you shortly.',
                    'data'    => [
                        'invoice_id'     => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'total_amount'   => $invoice->total_amount,
                        'status'         => 'B2B Web Request',
                        'phone'         => $user->phone,
                        'email'         => $user->email,
                    ]
                ]);
            }

            // ==================== B2C FLOW (Unchanged) ====================
            // (Keep your existing B2C code exactly as it was)

            $subtotal = 0;
            foreach ($cart->items as $cartItem) {
                $subtotal += $cartItem->quantity * $cartItem->item->price;
            }

            $finalAmount = (int) $request->final_amount;
            $payAmount   = (float) $request->pay_amount;
            $dueAmount   = max(0, $finalAmount - $payAmount);
            // dd($cart->promocode_id);
            // Create order
            $order = Order::create([
                'user_id'              => $user->id,
                'order_number'         => 'ORD-' . time(),
                'address_id'           => $address->id,
                'subtotal'             => $subtotal,
                'order_date'           => $request->order_date ?? now()->toDateString(),
                'b2b_discount'         => $request->b2b_discount ?? 0,
                'promocode_id'         => $cart->promocode_id,
                'promocode_discount'   => $request->promo_discount ?? 0,
                'shipping_charges'     => $request->shipping_charges,
                'gst_amount'           => $request->gst_amount ?? 0,
                'total_amount' => round($finalAmount),
                'paid_amount'          => 0,
                'due_amount'           => $finalAmount,
                'payment_status'       => 'pending',
                'order_status'         => 'pending',
                'promocode_id'          => $cart->promocode_id,
                'payment_method'       => $request->payment_method
            ]);

            if ($user->account_type === 'b2c') {

                $admin = DB::table('admins')->first();

                if ($admin) {

                    DB::table('admin_notifications')->insert([
                        'admin_id'       => $admin->id,
                        'type'           => 'new_order',
                        'title'          => 'New Order Received',
                        'message'        => "{$user->full_name} placed order {$order->order_number}",
                        'reference_type' => 'order',
                        'reference_id'   => $order->id,
                        'priority'       => 'high',
                        'extra_data'     => json_encode([
                            'order_id'      => $order->id,
                            'order_number'  => $order->order_number,
                            'customer_id'   => $user->id,
                            'customer_name' => $user->full_name,
                            'customer_email' => $user->email,
                            'total_amount'  => $order->total_amount
                        ]),
                        'created_at'     => now(),
                        'updated_at'     => now()
                    ]);
                }
            }

            // Process items (stock deduction, order items, pending items)
            foreach ($cart->items as $cartItem) {
                $item = $cartItem->item;
                $orderedQty = $cartItem->quantity;
                $stockQty = $item->quantity;
                $fulfilledQty = min($orderedQty, $stockQty);
                $pendingQty = $orderedQty - $fulfilledQty;
                $price = $item->price;

                $orderItem = OrderItem::create([
                    'order_id'    => $order->id,
                    'item_id'     => $item->id,
                    'quantity'    => $fulfilledQty,
                    'unit_price'  => $price,
                    'total_price' => $fulfilledQty * $price,
                    'status'      => $fulfilledQty > 0 ? 'confirmed' : 'pending'
                ]);

                // if ($fulfilledQty > 0) {
                //     $item->decrement('quantity', $fulfilledQty);
                // }

                if ($pendingQty > 0) {
                    DB::table('pending_order_items')->insert([
                        'order_id'      => $order->id,
                        'order_item_id' => $orderItem->id,
                        'item_id'       => $item->id,
                        'item_sku'      => $item->sku,
                        'user_id'       => $user->id,
                        'ordered_qty'   => $orderedQty,
                        'fulfilled_qty' => $fulfilledQty,
                        'pending_qty'   => $pendingQty,
                        'price'         => $price,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
            // Stripe payment session
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'inr',
                        'product_data' => ['name' => 'Order #' . $order->order_number],
                        'unit_amount' => intval($payAmount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url('/verify-payment?session_id={CHECKOUT_SESSION_ID}&order_id=' . $order->id),
                'cancel_url'  => url('/payment-failed'),
            ]);
            // dd($session);
            Payment::create([
                'order_id'       => $order->id,
                'user_id'        => $user->id,
                'payment_method' => $request->payment_method,
                'amount'         => $payAmount,
                'status'         => 'pending',
                'transaction_id' => $session->id
            ]);

            // Invoice (regular invoice for B2C)
            $invoice = Invoice::create([
                'order_id'       => $order->id,
                'invoice_number' => 'INV-' . time(),
                'type'           => 'invoice',
                'total_amount'   => $finalAmount,
                'pi_status'      => 'sent',
                'client_id'      => $order->user_id,
            ]);

            foreach ($order->items as $item) {
                InvoiceItem::create([
                    'invoice_id'    => $invoice->id,
                    'order_item_id' => $item->id,
                    'quantity'      => $item->quantity,
                    'unit_price'    => $item->unit_price,
                    'total_price'   => $item->total_price,
                ]);
            }

            // Send email to B2C customer
            Mail::to($user->email)->send(new OrderPlacedMail($order, 'invoice'));

            // Clear cart
            // CartItem::where('cart_id', $cart->id)->delete();
            // $cart->delete();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Order created, proceed to payment',
                'data'    => [
                    'order_id'      => $order->id,
                    'checkout_url'  => $session->url,
                    'pay_amount'    => $payAmount,
                    'due_amount'    => $dueAmount,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cancelOrder($id)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $order = Order::where('user_id', $user->id)->find($id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found or does not belong to you'
                ], 404);
            }

            if (!in_array($order->order_status, ['pending', 'confirmed'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order cannot be cancelled at this stage'
                ], 400);
            }

            if (in_array($order->order_status, ['cancelled', 'refunded'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order already ' . $order->order_status
                ], 400);
            }

            // Handle refund if paid
            if ($order->order_status === 'confirmed' && $order->paid_amount > 0) {
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

                $stripeSessionId = $order->payments()
                    ->where('status', 'success')
                    ->latest()
                    ->value('transaction_id');

                if (!$stripeSessionId) {
                    throw new \Exception('No Stripe session found');
                }

                $session = \Stripe\Checkout\Session::retrieve($stripeSessionId);

                if (!$session->payment_intent) {
                    throw new \Exception('No payment intent found');
                }

                $paymentIntentId = $session->payment_intent;

                // Create refund using Refund::create()
                \Stripe\Refund::create([
                    'payment_intent' => $paymentIntentId,
                    'amount' => intval($order->paid_amount * 100),
                ]);

                // Update payment record
                $order->payments()->where('transaction_id', $stripeSessionId)
                    ->update(['status' => 'refunded']);

                $order->payment_status = 'refunded';
            }

            // Restore stock
            foreach ($order->items as $orderItem) {
                $orderItem->item()->increment('quantity', $orderItem->quantity);
            }

            $newStatus = ($order->order_status === 'confirmed' && $order->paid_amount > 0) ? 'refunded' : 'cancelled';

            $order->update([
                'order_status' => $newStatus,
                // 'cancelled_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order cancelled successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_status' => $newStatus,
                    'refund_processed' => ($newStatus === 'refunded')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function payRemaining(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);

            $user = Auth::user();

            /*
        --------------------------------
        GET ORDER
        --------------------------------
        */
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ]);
            }

            /*
        --------------------------------
        CHECK FULLY PAID
        --------------------------------
        */
            $pendingExists = DB::table('pending_order_items')
                ->where('order_id', $order->id)
                ->where('pending_qty', '>', 0)
                ->exists();

            if (!$pendingExists && $order->due_amount <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order already completed'
                ]);
            }

            /*
        --------------------------------
        GET PENDING ITEMS
        --------------------------------
        */
            $pendingItems = DB::table('pending_order_items')
                ->where('order_id', $order->id)
                ->where('pending_qty', '>', 0)
                ->get();

            if ($pendingItems->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No pending items to fulfill'
                ]);
            }

            $payableAmount = 0;
            $fulfillableItems = [];

            /*
        --------------------------------
        CALCULATE PAYABLE (ONLY AVAILABLE STOCK)
        --------------------------------
        */
            foreach ($pendingItems as $pending) {

                $item = Item::find($pending->item_id);
                if (!$item) continue;

                $availableStock = $item->quantity;
                if ($availableStock <= 0) continue;

                $canFulfill = min($pending->pending_qty, $availableStock);

                if ($canFulfill <= 0) continue;

                $amount = $canFulfill * $pending->price;

                $payableAmount += $amount;

                $fulfillableItems[] = [
                    'pending_id' => $pending->id,
                    'item_id' => $pending->item_id,
                    'qty' => $canFulfill,
                    'amount' => $amount
                ];
            }

            if ($payableAmount <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No stock available yet for pending items'
                ]);
            }

            /*
        --------------------------------
        STRIPE SESSION (ONLY ITEMS AMOUNT)
        --------------------------------
        */
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'inr',
                        'product_data' => [
                            'name' => 'Pending Items Payment - Order #' . $order->order_number,
                        ],
                        'unit_amount' => intval($payableAmount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => url('/verify-payment?session_id={CHECKOUT_SESSION_ID}&order_id=' . $order->id . '&type=remaining'),
                'cancel_url'  => url('/payment-failed'),
            ]);

            /*
        --------------------------------
        SAVE PAYMENT
        --------------------------------
        */
            Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'payment_method' => $order->payment_method,
                'amount' => $payableAmount,
                'status' => 'pending',
                'transaction_id' => $session->id
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pay for available pending items',
                'data' => [
                    'checkout_url' => $session->url,
                    'payable_amount' => $payableAmount,
                    'items' => $fulfillableItems
                ]
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function verifyPayment(Request $request)
    {
        DB::beginTransaction();

        try {

            $request->validate([
                'session_id' => 'required',
                'order_id'   => 'required|exists:orders,id'
            ]);

            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            /*
        --------------------------------
        GET STRIPE SESSION
        --------------------------------
        */
            $session = \Stripe\Checkout\Session::retrieve($request->session_id);

            if ($session->payment_status !== 'paid') {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment not completed'
                ], 400);
            }

            /*
        --------------------------------
        GET PAYMENT
        --------------------------------
        */
            $payment = Payment::where('transaction_id', $request->session_id)->first();

            if (!$payment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            if ($payment->status === 'success') {
                return response()->json([
                    'status' => true,
                    'message' => 'Payment already verified',
                ]);
            }

            /*
        --------------------------------
        GET ORDER
        --------------------------------
        */
            $order = Order::find($request->order_id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            if ($payment->order_id != $order->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment does not belong to this order'
                ], 400);
            }

            /*
        --------------------------------
        UPDATE PAYMENT
        --------------------------------
        */
            $payment->update([
                'status'  => 'success',
                'paid_at' => now()
            ]);

            /*
        --------------------------------
        UPDATE PAID AMOUNT
        --------------------------------
        */
            $newPaidAmount = $order->paid_amount + $payment->amount;

            /*
        --------------------------------
        FULFILL PENDING ITEMS
        --------------------------------
        */
            $pendingItems = DB::table('pending_order_items')
                ->where('order_id', $order->id)
                ->where('pending_qty', '>', 0)
                ->get();

            $totalAddedAmount = 0;

            foreach ($pendingItems as $pending) {

                $item = Item::find($pending->item_id);
                if (!$item) continue;

                $availableStock = $item->quantity;
                if ($availableStock <= 0) continue;

                $fulfillNow = min($pending->pending_qty, $availableStock);

                if ($fulfillNow <= 0) continue;

                $orderItem = OrderItem::find($pending->order_item_id);

                if ($orderItem) {

                    $addedAmount = $fulfillNow * $orderItem->unit_price;

                    $orderItem->quantity += $fulfillNow;
                    $orderItem->total_price += $addedAmount;
                    $orderItem->status = 'confirmed';
                    $orderItem->save();

                    $totalAddedAmount += $addedAmount;
                }
                /*
                UPDATE STOCK
                */
                // $item->decrement('quantity', $fulfillNow);
                /*
                UPDATE PENDING TABLE
                */
                $newPending = $pending->pending_qty - $fulfillNow;

                if ($newPending <= 0) {

                    DB::table('pending_order_items')
                        ->where('id', $pending->id)
                        ->delete();
                } else {

                    DB::table('pending_order_items')
                        ->where('id', $pending->id)
                        ->update([
                            'fulfilled_qty' => $pending->fulfilled_qty + $fulfillNow,
                            'pending_qty' => $newPending,
                            'updated_at' => now()
                        ]);
                }
            }

            /*
            --------------------------------
            DEDUCT STOCK FOR ORDER ITEMS
            --------------------------------
            */
            foreach ($order->items as $orderItem) {

                $item = Item::find($orderItem->item_id);

                if (!$item) {
                    continue;
                }

                /*
                    DEDUCT ONLY CONFIRMED QUANTITY
                    */
                $deductQty = $orderItem->quantity;

                if ($deductQty < 0) {
                    continue;
                }

                /*
                    PREVENT NEGATIVE STOCK
                    */
                if ($item->quantity < $deductQty) {
                    $deductQty = $item->quantity;
                }

                /*
                    UPDATE STOCK
                    */
                $item->decrement('quantity', $deductQty);

                /*
                    REFRESH ITEM FOR UPDATED QUANTITY
                    */
                $item->refresh();

                /*
                    --------------------------------
                    LOW STOCK ALERT
                    --------------------------------
                    */
                if ($item->quantity < 20) {

                    /*
                    --------------------------------
                    GET SALES EXECUTIVES
                    --------------------------------
                    */
                    $salesExecutives = User::join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->join('roles', 'roles.id', '=', 'role_users.role_id')
                        ->where('roles.slug', 'sales-executive')
                        ->select('users.*')
                        ->get();

                    foreach ($salesExecutives as $executive) {

                        Notification::create([
                            'user_id'        => $executive->id,
                            'type'           => 'low_stock',
                            'title'          => 'Low Stock Alert',
                            'message'        => "{$item->name} stock is running low. Only {$item->quantity} items left.",
                            'reference_type' => 'item',
                            'reference_id'   => $item->id,
                            'priority'       => 'high',
                            'extra_data'     => json_encode([
                                'item_name'     => $item->name,
                                'sku'           => $item->sku,
                                'current_stock' => $item->quantity,
                                'order_id'      => $order->id
                            ])
                        ]);
                    }
                }
            }
            /*
        --------------------------------
        UPDATE ORDER AMOUNTS
        --------------------------------
        */
            $newTotalAmount = $order->total_amount + $totalAddedAmount;

            if ($newPaidAmount > $newTotalAmount) {
                $newPaidAmount = $newTotalAmount;
            }

            $newDueAmount = max(0, $order->total_amount - $newPaidAmount);
            // $newDueAmount = max(0, $order->total_amount - $newPaidAmount  - $order->b2b_discount);
            // $newDueAmount = max(0, $newTotalAmount - $newPaidAmount);

            /*
        --------------------------------
        CHECK REMAINING PENDING ITEMS
        --------------------------------
        */
            $hasPendingItems = DB::table('pending_order_items')
                ->where('order_id', $order->id)
                ->where('pending_qty', '>', 0)
                ->exists();

            /*
        --------------------------------
        FINAL PAYMENT STATUS LOGIC
        --------------------------------
        */
            // if ($hasPendingItems) {
            //     $paymentStatus = 'partial';
            // } elseif ($newDueAmount > 0) {
            //     $paymentStatus = 'partial';
            // } else {
            //     $paymentStatus = 'paid';
            // }
            if ($newDueAmount > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'paid';
            }

            /*
        --------------------------------
        UPDATE ORDER
        --------------------------------
        */
            $order->update([
                'total_amount'   => round($newTotalAmount, 2),
                'paid_amount'    => round($newPaidAmount, 2),
                // 'due_amount'     => round($newDueAmount, 2),
                'due_amount'     => round($newDueAmount, 2),
                'payment_status' => $paymentStatus,
                'order_status'   => 'confirmed'
            ]);
            /*
            --------------------------------
            UPDATE PROMOCODE USAGE COUNT
            --------------------------------
            */
            if ($order->promocode_id) {

                $promocode = Promocode::find($order->promocode_id);

                if ($promocode) {

                    $promocode->increment('used_count');
                }
            }

            /*
        --------------------------------
        CLEAR CART
        --------------------------------
        */
            $cart = Cart::where('user_id', $order->user_id)->first();

            if ($cart) {
                CartItem::where('cart_id', $cart->id)->delete();
                $cart->delete();
            }

            /*
                |--------------------------------------------------------------------------
                | SEND PAYMENT INVOICE MAIL
                |--------------------------------------------------------------------------
                */
            $user = $order->user;

            $isB2B = $user->account_type === 'b2b';


            $invoice = Invoice::where('order_id', $order->id)
                ->latest()
                ->first();

            if ($invoice) {
                Mail::to($user->email)->send(
                    new OrderPlacedMail(
                        $order,
                        'invoice',
                        $payment->amount
                    )
                );
            }



            DB::commit();

            // $frontendUrl = 'http://localhost:5173/morovski-light-web/order-confirmation/';
            $frontendUrl = 'https://www.markupdesigns.net/morovski-light-web/order-confirmation/';

            $queryParams = http_build_query([
                'status' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'paid_amount' => $order->paid_amount,
                'due_amount' => $order->due_amount,
                'total_amount' => $order->total_amount,
                'payment_status' => $order->payment_status,
                'message' => 'Payment verified & order updated'
            ]);

            return redirect($frontendUrl . '?' . $queryParams);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function myOrders(Request $request)
    {
        try {

            $user = Auth::user();

            $orders = Order::with([
                'items.item.images',
                'address',
                'payments',
                'replacementRequests'
            ])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(11);

            /*
            |--------------------------------------------------------------------------
            | TRANSFORM DATA
            |--------------------------------------------------------------------------
            */

            $orders->getCollection()->transform(function ($order) {

                // Convert 0 discount values to null
                $order->b2b_discount = ($order->b2b_discount == 0 || $order->b2b_discount == '0.00')
                    ? null
                    : $order->b2b_discount;

                $order->promocode_discount = ($order->promocode_discount == 0 || $order->promocode_discount == '0.00')
                    ? null
                    : $order->promocode_discount;

                return $order;
            });

            return response()->json([
                'status' => true,
                'message' => 'Orders fetched successfully',
                'data' => $orders,
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'total' => $orders->total()
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function orderDetails($orderId)
    {
        try {
            $user = Auth::user();

            $order = Order::with([
                'items.item.images',
                'payments',
                'address'
            ])
                ->where('user_id', $user->id)
                ->where('id', $orderId)
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ]);
            }

            /*
        --------------------------------
        ITEMS FORMAT
        --------------------------------
        */
            $items = $order->items->map(function ($orderItem) {

                $item = $orderItem->item;

                return [
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'price' => $orderItem->unit_price,
                    'quantity' => $orderItem->quantity,
                    'total' => $orderItem->total_price,

                    'thumbnail' => $item->images->first()
                        ? asset('storage/' . $item->images->first()->image)
                        : null
                ];
            });

            /*
        --------------------------------
        PAYMENTS FORMAT
        --------------------------------
        */
            $payments = $order->payments->map(function ($payment) {
                return [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'method' => $payment->payment_method,
                    'status' => $payment->status,
                    'transaction_id' => $payment->transaction_id,
                    'paid_at' => $payment->paid_at
                ];
            });

            /*
        --------------------------------
        ADDRESS FORMAT
        --------------------------------
        */
            $address = $order->address ? [
                'name' => $order->address->name,
                'phone' => $order->address->phone,
                'address' => $order->address->address_line1,
                'city' => $order->address->city,
                'state' => $order->address->state,
                'pincode' => $order->address->pincode
            ] : null;

            return response()->json([
                'status' => true,
                'message' => 'Order details fetched',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,

                    'subtotal' => $order->subtotal,
                    'discount' => $order->b2b_discount,
                    'shipping' => $order->shipping_charges,
                    'gst' => $order->gst_amount,

                    'total_amount' => $order->total_amount,
                    'paid_amount' => $order->paid_amount,
                    'due_amount' => $order->due_amount,

                    'payment_status' => $order->payment_status,
                    'order_status' => $order->order_status,

                    'items' => $items,
                    'payments' => $payments,
                    'shipping_address' => $address,

                    'created_at' => $order->created_at->format('d M Y, h:i A')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function orderByNumber($orderNumber)
    {
        try {
            $user = Auth::user();

            $order = Order::with(['address'])
                ->where('order_number', $orderNumber)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ]);
            }

            $address = $order->address;

            return response()->json([
                'status' => true,
                'message' => 'Order fetched successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'placed_on' => $order->created_at->format('F d, Y'),

                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,

                    'shipping_address' => [
                        'name' => $address->full_name,
                        'phone' => $address->phone,
                        'email' => $order->user->email,
                        'address' => $address->address_line_1,
                        'city' => $address->city,
                        'pincode' => $address->postal_code
                    ],

                    'shipping_type' => ($order->shipping_charges == 0) ? 'Standard' : 'Express',
                    'estimated_delivery' => now()->addDays(3)->format('d M Y')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function dashboard()
    {
        try {
            $user = Auth::user();

            /*
        --------------------------------
        OPEN PROFORMA INVOICES
        --------------------------------
        */
            $openPIs = Invoice::where('type', 'proforma')
                ->whereHas('order', function ($q) {
                    $q->where('payment_status', '!=', 'paid');
                })
                ->count();

            $newPIsThisWeek = Invoice::where('type', 'proforma')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            /*
        --------------------------------
        ORDERS THIS MONTH
        --------------------------------
        */
            $ordersThisMonth = Order::whereMonth('created_at', now()->month)->count();
            $ordersLastMonth = Order::whereMonth('created_at', now()->subMonth()->month)->count();

            /*
        --------------------------------
        LOW STOCK ALERTS
        --------------------------------
        */
            $lowStockItems = Item::where('quantity', '<=', 20)->count();

            /*
        --------------------------------
        RECENT PROFORMA INVOICES
        --------------------------------
        */
            $recentPIs = Invoice::with('order.user')
                ->where('type', 'proforma')
                ->whereHas('order.user')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($pi) {
                    return [
                        'pi_number' => $pi->invoice_number,
                        'client'    => $pi->order->user->full_name,
                        'total'     => $pi->total_amount,
                        'status'    => $pi->order->payment_status,
                    ];
                });

            /*
        --------------------------------
        B2B REQUESTS
        --------------------------------
        */
            $b2bRequests = User::where('account_type', 'b2b')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'company' => $user->businessProfile->company_name ?? $user->name,
                        'time'    => $user->created_at->diffForHumans(),
                        'status'  => $user->business_status ?? 'pending',
                    ];
                });

            /*
        --------------------------------
        NOTIFICATIONS
        --------------------------------
        */
            $notifications = [];

            // PI Viewed
            $recentViewed = Invoice::where('type', 'proforma')
                ->latest()
                ->take(2)
                ->get();

            foreach ($recentViewed as $pi) {
                $notifications[] = [
                    'message' => "{$pi->invoice_number} viewed",
                    'time'    => $pi->updated_at->diffForHumans()
                ];
            }

            // Expiring PIs (example logic)
            $expiring = Invoice::where('type', 'proforma')
                ->whereDate('created_at', '<=', now()->subDays(7))
                ->take(2)
                ->get();

            foreach ($expiring as $pi) {
                $notifications[] = [
                    'message' => "{$pi->invoice_number} expiring soon",
                    'time'    => $pi->created_at->diffForHumans()
                ];
            }

            // Low stock alerts
            $lowStockList =  Item::where('quantity', '<=', 20)
                ->take(2)
                ->get();

            foreach ($lowStockList as $item) {
                $notifications[] = [
                    'message' => "Low stock - {$item->name}",
                    'time'    => "{$item->quantity} units left"
                ];
            }

            /*
        --------------------------------
        RESPONSE
        --------------------------------
        */
            return response()->json([
                'status' => true,
                'data' => [

                    'stats' => [
                        'open_pis' => [
                            'total' => $openPIs,
                            'new_this_week' => $newPIsThisWeek
                        ],
                        'orders' => [
                            'this_month' => $ordersThisMonth,
                            'last_month' => $ordersLastMonth
                        ],
                        'low_stock' => $lowStockItems
                    ],

                    'recent_pis' => $recentPIs,

                    'b2b_requests' => $b2bRequests,

                    'notifications' => $notifications

                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createProformaInvoice(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'valid_until' => 'required|date|after:today',
                'payment_terms' => 'nullable|string',

                'items' => 'required|array|min:1',
                'items.*.order_item_id' => 'required|exists:order_items,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0|max:100',
            ]);

            $user = Auth::user();

            /*
        --------------------------------
        CHECK SALES EXECUTIVE
        --------------------------------
        */
            if ($user->roles->first()->slug != 'sales-executive') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            /*
        --------------------------------
        GET ORDER
        --------------------------------
        */
            $order = Order::with('items')->find($request->order_id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ]);
            }

            /*
        --------------------------------
        CREATE INVOICE (USE ORDER VALUES)
        --------------------------------
        */
            $invoice = Invoice::create([
                'order_id' => $order->id,
                'client_id' => $order->user_id,
                'sales_executive_id' => $user->id,
                'invoice_number' => 'PI-' . now()->timestamp,
                'type' => 'proforma',
                'pi_status' => 'draft',
                'valid_until' => $request->valid_until,
                'payment_terms' => $request->payment_terms,

                //  DIRECT FROM ORDER
                // 'subtotal' => $order->subtotal,
                // 'b2b_discount' => $order->b2b_discount,
                // 'promo_discount' => $order->promocode_discount,
                // 'shipping_charges' => $order->shipping_charges,
                // 'gst_amount' => $order->gst_amount,

                'total_amount' => $order->total_amount,
                'amount_paid' => $order->paid_amount,
            ]);

            /*
        --------------------------------
        ADD INVOICE ITEMS
        --------------------------------
        */
            foreach ($request->items as $item) {

                $lineTotal = $item['quantity'] * $item['unit_price'];

                $discount = $item['discount'] ?? 0;

                if ($discount > 0) {
                    $lineTotal -= ($lineTotal * $discount / 100);
                }

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'order_item_id' => $item['order_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_discount_percent' => $discount,
                    'total_price' => $lineTotal,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Proforma Invoice created successfully',
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'final_amount' => $invoice->total_amount
                ]
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function sendPI(Request $request)
    {
        try {
            $request->validate([
                'invoice_id' => 'required|exists:invoices,id'
            ]);

            $user = Auth::user();

            /*
        --------------------------------
        GET INVOICE WITH RELATIONS
        --------------------------------
        */
            $invoice = Invoice::with(['order.user', 'items'])
                ->where('id', $request->invoice_id)
                ->where('type', 'proforma')
                ->first();

            if (!$invoice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Proforma Invoice not found'
                ]);
            }

            /*
        --------------------------------
        CHECK CLIENT
        --------------------------------
        */
            $client = $invoice->order?->user;

            if (!$client || !$client->email) {
                return response()->json([
                    'status' => false,
                    'message' => 'Client email not found'
                ]);
            }

            /*
        --------------------------------
        UPDATE STATUS
        --------------------------------
        */
            $invoice->update([
                'pi_status' => 'sent'
            ]);

            /*
        --------------------------------
        SEND EMAIL
        --------------------------------
        */
            Mail::to($client->email)->send(
                new OrderPlacedMail($invoice->order, 'proforma', $invoice)
            );

            /*
        --------------------------------
        OPTIONAL: WHATSAPP
        --------------------------------
        */
            if ($client->phone) {
                $this->sendWhatsAppPI($client->phone, $invoice);
            }

            return response()->json([
                'status' => true,
                'message' => 'Proforma Invoice sent successfully',
                'data' => [
                    'invoice_id' => $invoice->id,
                    'client_email' => $client->email,
                    'status' => 'sent'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function sendWhatsAppPI($phone, $invoice)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');

        $client = new \Twilio\Rest\Client($sid, $token);

        /*
    --------------------------------
    GET ITEMS LIST (FROM INVOICE ITEMS)
    --------------------------------
    */
        $itemsText = '';

        foreach ($invoice->items as $item) {
            $productName = $item->orderItem->product_name ?? 'Item';

            $itemsText .= "- {$productName} (Qty: {$item->quantity}) | ₹{$item->total_price}\n";
        }

        /*
    --------------------------------
    GET CLIENT NAME
    --------------------------------
    */
        $clientName = $invoice->order?->user?->full_name ?? 'Customer';

        /*
    --------------------------------
    PREPARE MESSAGE
    --------------------------------
    */
        $message = " *Proforma Invoice Generated!*\n\n"
            . " Name: {$clientName}\n"
            . " PI Number: {$invoice->invoice_number}\n\n"
            . " *Items:*\n{$itemsText}\n"
            . " Total Amount: ₹{$invoice->total_amount}\n"
            // . " Paid: ₹{$invoice->amount_paid}\n"
            . " Due: ₹" . ($invoice->total_amount - $invoice->amount_paid) . "\n\n"
            . " Valid Till: " . \Carbon\Carbon::parse($invoice->valid_until)->format('d M Y') . "\n"
            // . " Payment Terms: {$invoice->payment_terms}\n\n"
            . "Please proceed with payment. Thank you!";

        /*
    --------------------------------
    SEND WHATSAPP MESSAGE
    --------------------------------
    */
        $client->messages->create(
            "whatsapp:+91" . $phone,
            [
                "from" => "whatsapp:+14155238886",
                "body" => $message
            ]
        );
    }

    public function recordPiPayment(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
                'amount' => 'required|numeric|min:1',
                'payment_reference' => 'required|string'
            ]);

            $user = Auth::user();

            $invoice = Invoice::find($request->invoice_id);

            if (!$invoice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice not found'
                ]);
            }

            /*
        --------------------------------
        VALIDATE AMOUNT
        --------------------------------
        */
            $remaining = $invoice->total_amount - $invoice->amount_paid;

            if ($request->amount > $remaining) {
                return response()->json([
                    'status' => false,
                    'message' => 'Amount exceeds remaining balance'
                ]);
            }

            /*
        --------------------------------
        CREATE PAYMENT
        --------------------------------
        */
            PiPayment::create([
                'invoice_id' => $invoice->id,
                'amount' => $request->amount,
                'payment_reference' => $request->payment_reference,
                'payment_date' => now(),
                'recorded_by' => $user->id,
            ]);

            /*
        --------------------------------
        UPDATE INVOICE
        --------------------------------
        */
            $newPaid = $invoice->amount_paid + $request->amount;

            $status = $newPaid >= $invoice->total_amount
                ? 'paid'
                : 'partial';

            $invoice->update([
                'amount_paid' => $newPaid,
                'pi_status' => $status
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment recorded successfully',
                'data' => [
                    'paid' => $newPaid,
                    'remaining' => $invoice->total_amount - $newPaid,
                    'status' => $status
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function reorder($orderId)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            // 1. Find the user's past order
            $order = Order::with('items.item')->where('user_id', $user->id)->find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // 2. Get or create the user's current cart
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            $itemsAdded = [];
            $stockIssues = [];

            // 3. Loop through order items and add to cart
            foreach ($order->items as $orderItem) {
                $item = $orderItem->item;
                $requestedQty = $orderItem->quantity;

                // Check current stock
                $availableStock = $item->quantity;

                if ($availableStock <= 0) {
                    $stockIssues[] = [
                        'item_name' => $item->name,
                        'requested' => $requestedQty,
                        'available' => 0,
                        'added' => 0
                    ];
                    continue;
                }

                $addQty = min($requestedQty, $availableStock);

                // Update or create cart item
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('item_id', $item->id)
                    ->first();

                if ($cartItem) {
                    $cartItem->increment('quantity', $addQty);
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'item_id' => $item->id,
                        'quantity' => $addQty
                    ]);
                }

                $itemsAdded[] = [
                    'item_name' => $item->name,
                    'added_qty' => $addQty,
                    'requested_qty' => $requestedQty
                ];

                // Log if partial addition
                if ($addQty < $requestedQty) {
                    $stockIssues[] = [
                        'item_name' => $item->name,
                        'requested' => $requestedQty,
                        'available' => $availableStock,
                        'added' => $addQty
                    ];
                }
            }

            DB::commit();

            // Reload cart with items
            $cart->load('items.item.images');

            return response()->json([
                'status' => true,
                'message' => count($itemsAdded) > 0 ? 'Items added to cart' : 'No items could be added due to stock',
                'data' => [
                    'cart' => $cart,
                    'items_added' => $itemsAdded,
                    'stock_issues' => $stockIssues
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to reorder: ' . $e->getMessage()
            ], 500);
        }
    }

    public function counts()
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Total Orders
        |--------------------------------------------------------------------------
        */
        $totalOrders = Order::where('user_id', $user->id)
            ->where('order_status', '!=', 'pending')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Wishlist Items
        |--------------------------------------------------------------------------
        */
        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Open Replacement Requests
        |--------------------------------------------------------------------------
        */
        $openReplacements = ReplacementRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        return response()->json([
            'status' => true,
            'message' => 'Dashboard counts fetched successfully',
            'data' => [
                'total_orders' => $totalOrders,
                'wishlist_items' => $wishlistItems,
                'open_replacements' => $openReplacements,
            ]
        ]);
    }

    public function pendingOrderDetails($order_id)
    {
        try {

            $authUser = Auth::user();

            $warehouse = Warehouse::find($authUser->warehouse_id);

            if (!$warehouse) {

                return response()->json([
                    'status'  => false,
                    'message' => 'Warehouse not assigned to this user'
                ], 404);
            }

            $order = Order::with([
                'user',
                'items.item.images',
                'items.packedOrder',
                'items.allocations' => function ($query) use ($warehouse) {
                    $query->where('warehouse_id', $warehouse->id);
                }
            ])
            ->where('id', $order_id)
            ->whereHas('items.allocations', function ($query) use ($warehouse) {
                $query->where('warehouse_id', $warehouse->id);
            })
            ->first();

            if (!$order) {

                return response()->json([
                    'status'  => false,
                    'message' => 'Order not found for this warehouse'
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | ONLY CURRENT WAREHOUSE ITEMS
            |--------------------------------------------------------------------------
            */

            $order->setRelation(
                'items',
                $order->items
                    ->filter(function ($item) use ($warehouse) {

                        return $item->allocations
                            ->where('warehouse_id', $warehouse->id)
                            ->count() > 0;
                    })
                    ->values()
            );

            $order->items->transform(function ($orderItem) {

                $orderItem->is_packed = $orderItem->status === 'packed';

                $orderItem->packing_details = $orderItem->packedOrder
                    ? [
                        'packed_order_id' => $orderItem->packedOrder->id,
                        'number_of_boxes' => $orderItem->packedOrder->number_of_boxes,
                        'total_weight'    => $orderItem->packedOrder->total_weight,
                        'packed_at'       => $orderItem->packedOrder->packed_at,
                    ]
                    : null;

                unset($orderItem->packedOrder);

                return $orderItem;
            });

            return response()->json([
                'status'  => true,
                'message' => 'Order details fetched successfully',
                'warehouse_id' => $warehouse->id,
                'data'    => $order
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine()
            ], 500);
        }
    }
}
