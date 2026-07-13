<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\Promocode;
use App\Models\PromocodeUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function getCart()
    {
        try {
            $user = Auth::user();

            $cart = Cart::with(['items.item.images'])
                ->where('user_id', $user->id)
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Cart is empty',
                    'data' => []
                ]);
            }

            $totalAmount = 0;
            $totalQuantity = 0;

            $items = $cart->items->map(function ($cartItem) use (&$totalAmount, &$totalQuantity) {

                $price = $cartItem->item->price;
                $qty = $cartItem->quantity;
                $itemTotal = $price * $qty;

                $totalAmount += $itemTotal;
                $totalQuantity += $qty;

                return [
                    'cart_item_id' => $cartItem->id,
                    'item_id' => $cartItem->item->id,
                    'name' => $cartItem->item->name,
                    'slug' => $cartItem->item->slug,
                    'price' => $price,
                    'quantity' => $qty,
                    'total_price' => $itemTotal,
                    'thumbnail' => optional($cartItem->item->images->first())->image
                        ? asset('storage/' . $cartItem->item->images->first()->image)
                        : null,
                    'stock' => $cartItem->item->quantity
                ];
            });

            /*
        --------------------------------
        PROMOCODE APPLY + REVALIDATE
        --------------------------------
        */
            $discount = 0;
            $finalAmount = $totalAmount;
            $appliedPromo = null;

            if ($cart->promocode_id) {

                $promo = Promocode::find($cart->promocode_id);
                $now = now();

                // revalidate
                if (
                    !$promo ||
                    !$promo->status ||
                    ($promo->expires_at && $now > $promo->expires_at)
                ) {
                    // remove invalid promo
                    $cart->promocode_id = null;
                    $cart->save();
                } else {

                    $appliedPromo = $promo->code;

                    if ($promo->type == 'percentage') {
                        $discount = ($totalAmount * $promo->value) / 100;
                    } else {
                        $discount = $promo->value;
                    }

                    if ($promo->max_discount_amount && $discount > $promo->max_discount_amount) {
                        $discount = $promo->max_discount_amount;
                    }

                    $finalAmount = $totalAmount - $discount;
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Cart fetched successfully',
                'data' => [
                    'items' => $items,
                    'total_quantity' => $totalQuantity,
                    'subtotal' => $totalAmount,
                    'discount' => $discount,
                    'final_amount' => $finalAmount,
                    'promocode' => $appliedPromo
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function addToCart(Request $request)
    {
        try {
            $request->validate([
                'item_id' => 'required|exists:items,id',
                'quantity' => 'nullable|integer|min:1'
            ]);

            $userId = Auth::user()->id;
            $quantity = $request->quantity ?? 1;

            /*
        --------------------------------
        GET ITEM (FOR STOCK CHECK)
        --------------------------------
        */
            $item = Item::find($request->item_id);
            
            // if($item->quantity){
            //     return response()->json([
            //     'status' => false,
            //     'message' => "Order is not in stock"
            //      ],422);
            // }

            /*
        --------------------------------
        GET OR CREATE CART
        --------------------------------
        */
            $cart = Cart::firstOrCreate([
                'user_id' => $userId
            ]);

            /*
        --------------------------------
        CHECK ITEM IN CART_ITEMS
        --------------------------------
        */
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('item_id', $request->item_id)
                ->first();

            $existingQty = $cartItem ? $cartItem->quantity : 0;
            $totalRequestedQty = $existingQty + $quantity;

            /*
        --------------------------------
        STOCK VALIDATION
        --------------------------------
        */
            // if ($totalRequestedQty > $item->quantity) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Only ' . $item->quantity . ' items available in stock'
            //     ]);
            // }

            if ($cartItem) {
                // increase quantity
                $cartItem->quantity = $totalRequestedQty;
                $cartItem->save();

                $message = 'Cart updated (quantity increased)';
            } else {
                // add new item
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'item_id' => $request->item_id,
                    'quantity' => $quantity
                ]);

                $message = 'Item added to cart';
            }

            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => $cartItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function updateCartQuantity(Request $request)
    {
        try {
    
            $request->validate([
                'item_id'  => 'required|exists:items,id',
                'type'     => 'required|in:increase,decrease,update',
                'quantity' => 'nullable|integer|min:1'
            ]);
    
            $userId = Auth::user()->id;
    
            /*
            --------------------------------
            GET CART
            --------------------------------
            */
            $cart = Cart::where('user_id', $userId)->first();
    
            if (!$cart) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Cart not found'
                ]);
            }
    
            /*
            --------------------------------
            GET CART ITEM
            --------------------------------
            */
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('item_id', $request->item_id)
                ->first();
    
            if (!$cartItem) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Item not found in cart'
                ]);
            }
    
            /*
            --------------------------------
            GET ITEM
            --------------------------------
            */
            $item = Item::find($request->item_id);
    
            $changeQty = $request->quantity ?? 1;
    
            /*
            --------------------------------
            CALCULATE NEW QUANTITY
            --------------------------------
            */
            if ($request->type == 'increase') {
    
                $newQty = $cartItem->quantity + $changeQty;
                $message = 'Cart updated (quantity increased)';
    
            } elseif ($request->type == 'decrease') {
    
                $newQty = $cartItem->quantity - $changeQty;
                $message = 'Cart updated (quantity decreased)';
    
            } elseif ($request->type == 'update') {
    
                /*
                IMPORTANT:
                DIRECT SET
                */
                $newQty = $request->quantity;
                $message = 'Cart quantity updated';
    
            } else {
    
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid type'
                ]);
            }
    
            /*
            --------------------------------
            REMOVE ITEM
            --------------------------------
            */
            if ($newQty <= 0) {
    
                $cartItem->delete();
    
                return response()->json([
                    'status'  => true,
                    'message' => 'Item removed from cart'
                ]);
            }
    

    
            /*
            --------------------------------
            UPDATE QUANTITY
            --------------------------------
            */
            $cartItem->quantity = $newQty;
    
            /*
            IMPORTANT:
            SAVE ONLY
            DO NOT USE increment()
            DO NOT USE +=
            */
            $cartItem->save();
    
            return response()->json([
                'status'  => true,
                'message' => $message,
                'data'    => $cartItem
            ]);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    // public function updateCartQuantity(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'item_id' => 'required|exists:items,id',
    //             'type' => 'required|in:increase,decrease,set',
    //             'quantity' => 'nullable|integer|min:1'
    //         ]);

    //         $userId = Auth::user()->id;

    //         /*
    //     --------------------------------
    //     GET CART
    //     --------------------------------
    //     */
    //         $cart = Cart::where('user_id', $userId)->first();

    //         if (!$cart) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Cart not found'
    //             ]);
    //         }

    //         /*
    //     --------------------------------
    //     GET CART ITEM
    //     --------------------------------
    //     */
    //         $cartItem = CartItem::where('cart_id', $cart->id)
    //             ->where('item_id', $request->item_id)
    //             ->first();

    //         if (!$cartItem) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Item not found in cart'
    //             ]);
    //         }

    //         /*
    //     --------------------------------
    //     GET ITEM (FOR STOCK)
    //     --------------------------------
    //     */
    //         $item = Item::find($request->item_id);

    //         $changeQty = $request->quantity ?? 1;

    //         /*
    //     --------------------------------
    //     CALCULATE NEW QUANTITY
    //     --------------------------------
    //     */
    //         if ($request->type === 'increase') {
    //             $newQty = $cartItem->quantity + $changeQty;
    //         } elseif ($request->type === 'decrease') {
    //             $newQty = $cartItem->quantity - $changeQty;
    //         } else { // set
    //             $newQty = $request->quantity;
    //         }

    //         /*
    //     --------------------------------
    //     HANDLE ZERO OR NEGATIVE
    //     --------------------------------
    //     */
    //         if ($newQty <= 0) {
    //             $cartItem->delete();

    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Item removed from cart'
    //             ]);
    //         }

    //         /*
    //     --------------------------------
    //     STOCK VALIDATION
    //     --------------------------------
    //     */
    //         // if ($newQty > $item->quantity) {
    //         //     return response()->json([
    //         //         'status' => false,
    //         //         'message' => 'Only ' . $item->quantity . ' items available in stock'
    //         //     ]);
    //         // }

    //         /*
    //     --------------------------------
    //     UPDATE
    //     --------------------------------
    //     */
    //         $cartItem->quantity = $newQty;
    //         $cartItem->save();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Quantity updated',
    //             'data' => $cartItem
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }
    // public function checkout(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'shipping_id' => 'required|in:1,2'
    //         ]);

    //         $user = Auth::user();

    //         $cart = Cart::with(['items.item'])
    //             ->where('user_id', $user->id)
    //             ->first();

    //         if (!$cart || $cart->items->isEmpty()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Cart is empty'
    //             ]);
    //         }

    //         $subtotal = 0;
    //         $payableSubtotal = 0;
    //         $remainingSubtotal = 0;

    //         foreach ($cart->items as $cartItem) {

    //             $item = $cartItem->item;

    //             $price = $item->price;
    //             $orderQty = $cartItem->quantity;
    //             $stockQty = $item->quantity;

    //             $fulfilledQty = min($orderQty, $stockQty);
    //             $remainingQty = $orderQty - $fulfilledQty;

    //             $subtotal += ($orderQty * $price);
    //             $payableSubtotal += ($fulfilledQty * $price);
    //             $remainingSubtotal += ($remainingQty * $price);
    //         }

    //         /*
    //     B2B
    //     */
    //         $b2bDiscount = 0;

    //         if ($user->account_type === 'b2b') {
    //             $profile = BusinessProfile::where('user_id', $user->id)->first();
    //             if ($profile) {
    //                 $b2bDiscount = ($payableSubtotal * $profile->discount_percentage) / 100;
    //                 // dd($b2bDiscount);
    //             }
    //         }

    //         $afterB2B = $payableSubtotal - $b2bDiscount;

    //         /*
    //     PROMO
    //     */
    //         $promoDiscount = 0;
    //         $promoId = $cart->promocode_id ?? null;

    //         if ($promoId) {
    //             $promo = Promocode::find($promoId);
    //             if ($promo && $promo->status) {

    //                 if ($promo->type == 'percentage') {
    //                     $promoDiscount = ($afterB2B * $promo->value) / 100;
    //                 } else {
    //                     $promoDiscount = $promo->value;
    //                 }
    //             }
    //         }

    //         $afterDiscount = $afterB2B - $promoDiscount;

    //         /*
    //     SHIPPING + GST
    //     */
    //         $shippingCharges = ($request->shipping_id == 1) ? 100 : 299;

    //         $gstAmount = (($afterDiscount + $shippingCharges) * 18) / 100;

    //         $finalAmount = $afterDiscount + $shippingCharges + $gstAmount;

    //         return response()->json([
    //             'status' => true,
    //             'data' => [
    //                 'subtotal' => round($subtotal, 2),
    //                 'payable_amount' => round($payableSubtotal, 2),

    //                 'b2b_discount' => round($b2bDiscount, 2),
    //                 'promo_discount' => round($promoDiscount, 2),

    //                 'shipping_charges' => $shippingCharges,
    //                 'gst_amount' => round($gstAmount, 2),

    //                 'final_amount' => round($finalAmount, 2)
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }
    public function checkout(Request $request)
    {
        try {
            $request->validate([
                'shipping_id' => 'required|in:1,2'
            ]);

            $user = Auth::user();

            $cart = Cart::with(['items.item'])
                ->where('user_id', $user->id)
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty'
                ]);
            }

            $subtotal = 0;
            $fulfilledAmount = 0;
            $missingAmount = 0;

            foreach ($cart->items as $cartItem) {

                $item = $cartItem->item;

                $price = $item->price;
                $orderQty = $cartItem->quantity;
                $stockQty = $item->quantity;

                $fulfilledQty = min($orderQty, $stockQty);
                $remainingQty = $orderQty - $fulfilledQty;

                $subtotal += ($orderQty * $price);
                $fulfilledAmount += ($fulfilledQty * $price);
                $missingAmount += ($remainingQty * $price);
            }

            /*
        SHIPPING
        */
            $shippingCharges = ($request->shipping_id == 1) ? 100 : 299;

            /*
        GST (ON FULL SUBTOTAL)
        */
            $gstAmount = ($subtotal * 18) / 100;

            /*
        BASE AMOUNT AFTER REMOVING MISSING ITEMS
        */
            $baseAmount = ($subtotal + $gstAmount + $shippingCharges) - $missingAmount;

            /*
        B2B DISCOUNT (ONLY ON FULFILLED AMOUNT)
        */
            $b2bDiscount = 0;

            if ($user->account_type === 'b2b') {
                $profile = BusinessProfile::where('user_id', $user->id)->first();
                if ($profile) {
                    $b2bDiscount = ($fulfilledAmount * $profile->discount_percentage) / 100;
                }
            }
            $afterB2B = $fulfilledAmount - $b2bDiscount;
            /*
        PROMO DISCOUNT (ONLY ON FULFILLED AMOUNT)
        */
            $promoDiscount = 0;
            $promoId = $cart->promocode_id ?? null;

            if ($promoId) {
                $promo = Promocode::find($promoId);
                if ($promo && $promo->status) {

                    if ($promo->type == 'percentage') {
                        $promoDiscount = ($subtotal * $promo->value) / 100;
                    } else {
                        $promoDiscount = $promo->value;
                    }
                }
            }

            $afterDiscount = $afterB2B - $promoDiscount;

            /*
        FINAL AMOUNT
        */
            $finalAmount = $baseAmount - $b2bDiscount - $promoDiscount;
            $totalAmount = ($subtotal + $gstAmount + $shippingCharges) - $b2bDiscount - $promoDiscount;

            return response()->json([
                'status' => true,
                'data' => [
                    'subtotal' => round($subtotal, 2),
                    'total_amount' => round($totalAmount, 2),

                    'b2b_discount' => round($b2bDiscount, 2),
                    'promo_discount' => round($promoDiscount, 2),

                    'shipping_charges' => $shippingCharges,
                    'gst_amount' => round($gstAmount, 2),

                    'missing_amount' => round($missingAmount, 2),

                    'payable_amount' => round($finalAmount, 2)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
        public function removeCartItem(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
        ]);

        $user = Auth::user();

        $cartItem = CartItem::with('cart')
            ->where('id', $request->cart_item_id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'status' => false,
                'message' => 'Cart item not found',
            ], 404);
        }
        if (!$cartItem->cart || $cartItem->cart->user_id != $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Item not in cart',
            ], 422);
        }

        $cartId = $cartItem->cart_id;

        $cartItem->delete();

        $remainingItems = CartItem::where('cart_id', $cartId)->count();

        if ($remainingItems == 0) {

            // remove promocode records
            PromocodeUsage::where('user_id', $user->id)->delete();

            // optional: delete cart also
            Cart::where('id', $cartId)->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Item removed from cart successfully',
        ]);
    }
}
