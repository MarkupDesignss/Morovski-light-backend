<?php

namespace App\Listeners;

use App\Events\AuctionEnded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ShippingCharge;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AuctionPaymentLinkNotification;

class SendAuctionPaymentLink
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AuctionEnded $event)
    {
        $auction = $event->auction;

        DB::beginTransaction();

        try {
            $winner = $auction->winner; // relation hona chahiye
            $item = $auction->item;

            if (!$winner || !$item) return;

            // prevent duplicate order
            if (Order::where('item_id', $item->id)
                ->where('buyer_id', $winner->id)
                ->whereIn('status', ['pending', 'funds_held'])
                ->exists()
            ) {
                return;
            }

            $unitPrice = $auction->current_bid;
            $quantity = 1;

            $shippingCharge = ShippingCharge::where('status', 1)->value('charge') ?? 0;
            $total = $unitPrice + $shippingCharge;

            // ===== ORDER =====
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'buyer_id' => $winner->id,
                'seller_id' => $item->user_id,
                'item_id' => $item->id,
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'total_amount' => $total,
                'shipping_charge' => $shippingCharge,
                'status' => 'pending',
                'payment_holder' => 'admin',
                'payout_status' => 'pending'
            ]);

            // ===== STRIPE =====
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'success_url' => url('/payment-success?session_id={CHECKOUT_SESSION_ID}&order_number=' . $order->order_number),
                'cancel_url' => url('/cancel'),
                'metadata' => [
                    'order_id' => $order->id,
                ],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'inr',
                            'product_data' => [
                                'name' => $item->title . ' (Auction Won)',
                            ],
                            'unit_amount' => (int) round($unitPrice * 100),
                        ],
                        'quantity' => 1,
                    ],
                    [
                        'price_data' => [
                            'currency' => 'inr',
                            'product_data' => [
                                'name' => 'Shipping Charge',
                            ],
                            'unit_amount' => (int) round($shippingCharge * 100),
                        ],
                        'quantity' => 1,
                    ]
                ],
            ]);

            // ===== PAYMENT =====
            Payment::create([
                'order_id' => $order->id,
                'stripe_session_id' => $session->id,
                'amount' => $total,
                'status' => 'pending',
                'paid_to' => 'admin'
            ]);

            DB::commit();

            //  SEND NOTIFICATION + MAIL
            Notification::send($winner, new AuctionPaymentLinkNotification($order, $session->url));
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
}