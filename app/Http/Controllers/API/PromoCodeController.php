<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Promocode;
use App\Models\PromocodeUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromoCodeController extends Controller
{
    private function formatPromoDescription($promo)
    {
        if ($promo->type === 'percentage') {
            return $promo->value . '% OFF up to ' . $promo->max_discount_amount;
        }

        return 'Flat ₹' . $promo->value . ' OFF';
    }
    // public function allPromocodes()
    // {
    //     try {
    //         $user = Auth::user();
    //         $userId = Auth::user()->id;

    //         $now = now();

    //         $promos = Promocode::where('status', 1)
  
    //             // user type filter
    //             ->where(function ($q) use ($user) {
    //                 $q->where('user_type', 'all')
    //                     ->orWhere('user_type', $user->account_type);
    //             })

    //             // date validity
    //             ->where(function ($q) use ($now) {
    //                 $q->whereNull('starts_at')
    //                     ->orWhere('starts_at', '<=', $now);
    //             })
    //             ->where(function ($q) use ($now) {
    //                 $q->whereNull('expires_at')
    //                     ->orWhere('expires_at', '>=', $now);
    //             })

    //             // usage limit not exceeded
    //             ->where(function ($q) {
    //                 $q->whereNull('usage_limit')
    //                     ->orWhereColumn('used_count', '<', 'usage_limit');
    //             })

    //             ->latest()
    //             ->get();

    //         /*
    //     --------------------------------
    //     FORMAT RESPONSE
    //     --------------------------------
    //     */
    //         $data = $promos->map(function ($promo) {
    //             return [
    //                 'id' => $promo->id,
    //                 'code' => $promo->code,
    //                 'type' => $promo->type,
    //                 'value' => $promo->value,
    //                 'min_cart_amount' => $promo->min_cart_amount,
    //                 'max_discount_amount' => $promo->max_discount_amount,
    //                 'expires_at' => $promo->expires_at,
    //                 'description' => $this->formatPromoDescription($promo)
    //             ];
    //         });

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Promocodes fetched successfully',
    //             'data' => $data
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }
    
    public function allPromocodes()
    {
        try {
    
            $user = Auth::user();
            $now = now();
    
            /*
            |--------------------------------------------------------------------------
            | GET SELECTED PROMOCODE FROM CART
            |--------------------------------------------------------------------------
            */
    
            $selectedPromoId = Cart::where('user_id', $user->id)
                ->whereNotNull('promocode_id')
                ->value('promocode_id');
    
            /*
            |--------------------------------------------------------------------------
            | FETCH PROMOCODES
            |--------------------------------------------------------------------------
            */
    
            $promos = Promocode::where('status', 1)
    
                // user type filter
                ->where(function ($q) use ($user) {
                    $q->where('user_type', 'all')
                        ->orWhere('user_type', $user->account_type);
                })
    
                // start date check
                ->where(function ($q) use ($now) {
                    $q->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', $now);
                })
    
                // expiry date check
                ->where(function ($q) use ($now) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', $now);
                })
    
                // usage limit check
                ->where(function ($q) {
                    $q->whereNull('usage_limit')
                        ->orWhereColumn('used_count', '<', 'usage_limit');
                })
    
                ->latest()
                ->get();
    
            /*
            |--------------------------------------------------------------------------
            | FORMAT RESPONSE
            |--------------------------------------------------------------------------
            */
    
            $data = $promos->map(function ($promo) use ($selectedPromoId) {
    
                return [
                    'id' => $promo->id,
                    'code' => $promo->code,
                    'type' => $promo->type,
                    'value' => $promo->value,
                    'min_cart_amount' => $promo->min_cart_amount,
                    'max_discount_amount' => $promo->max_discount_amount,
                    'expires_at' => $promo->expires_at,
    
                    // selected status
                    'is_selected' => (int) $promo->id === (int) $selectedPromoId,
    
                    'description' => $this->formatPromoDescription($promo),
                ];
            });
    
            return response()->json([
                'status' => true,
                'message' => 'Promocodes fetched successfully',
                'data' => $data
            ]);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    // public function applyPromocode(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'code' => 'required|string'
    //         ]);

    //         $user = Auth::user();

    //         $cart = Cart::with('items.item')
    //             ->where('user_id', $user->id)
    //             ->first();

    //         if (!$cart || $cart->items->isEmpty()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Cart is empty'
    //             ]);
    //         }

    //       $promo = Promocode::where('code', $request->code)
    //             ->latest()
    //             ->first();
    //         if (!$promo || !$promo->status) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Invalid promocode'
    //             ],422);
    //         }

    //         $cartTotal = $cart->items->sum(function ($item) {
    //             return $item->quantity * $item->item->price;
    //         });

    //         // discount
    //         if ($promo->type == 'percentage') {
    //             $discount = ($cartTotal * $promo->value) / 100;
    //         } else {
    //             $discount = $promo->value;
    //         }

    //         if ($promo->max_discount_amount && $discount > $promo->max_discount_amount) {
    //             $discount = $promo->max_discount_amount;
    //         }

    //         $finalAmount = $cartTotal - $discount;


    //         $cart->update([
    //             'promocode_id'   => $promo->id,
    //             'discount_amount' => $discount
    //         ]);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Promocode applied successfully',
    //             'data' => [
    //                 'cart_total' => $cartTotal,
    //                 'discount' => $discount,
    //                 'final_amount' => $finalAmount,
    //                 'promocode' => $promo->code
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }
    
    public function applyPromocode(Request $request)
    {
        try {
    
            $request->validate([
                'code' => 'required|string'
            ]);
    
            $user = Auth::user();
    
            /*
            |--------------------------------------------------------------------------
            | GET CART
            |--------------------------------------------------------------------------
            */
            $cart = Cart::with('items.item')
                ->where('user_id', $user->id)
                ->first();
    
            if (!$cart || $cart->items->isEmpty()) {
    
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty'
                ], 422);
            }
    
            /*
            |--------------------------------------------------------------------------
            | GET PROMOCODE
            |--------------------------------------------------------------------------
            */
            $promo = Promocode::where('code', $request->code)
                ->where('status', 1)
                ->first();
    
            if (!$promo) {
    
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid promocode'
                ], 422);
            }
    
            /*
            |--------------------------------------------------------------------------
            | CHECK DATE VALIDITY
            |--------------------------------------------------------------------------
            */
            if (
                ($promo->starts_at && now()->lt($promo->starts_at)) ||
                ($promo->expires_at && now()->gt($promo->expires_at))
            ) {
    
                return response()->json([
                    'status' => false,
                    'message' => 'Promocode expired or inactive'
                ], 422);
            }
    
            /*
            |--------------------------------------------------------------------------
            | CHECK USAGE LIMIT
            |--------------------------------------------------------------------------
            */
            if (
                $promo->usage_limit &&
                $promo->used_count >= $promo->usage_limit
            ) {
    
                return response()->json([
                    'status' => false,
                    'message' => 'Promocode usage limit exceeded'
                ], 422);
            }
    
            /*
            |--------------------------------------------------------------------------
            | CALCULATE CART TOTAL
            |--------------------------------------------------------------------------
            */
            $cartTotal = $cart->items->sum(function ($cartItem) {
    
                return $cartItem->quantity * $cartItem->item->price;
            });
    
            /*
            |--------------------------------------------------------------------------
            | CHECK MIN CART AMOUNT
            |--------------------------------------------------------------------------
            */
            if (
                $promo->min_cart_amount &&
                $cartTotal < $promo->min_cart_amount
            ) {
    
                return response()->json([
                    'status' => false,
                    'message' => 'Minimum cart amount should be ₹' . $promo->min_cart_amount
                ], 422);
            }
    
            /*
            |--------------------------------------------------------------------------
            | CALCULATE DISCOUNT
            |--------------------------------------------------------------------------
            */
            if ($promo->type === 'percentage') {
    
                $discount = ($cartTotal * $promo->value) / 100;
            } else {
    
                $discount = $promo->value;
            }
    
            /*
            |--------------------------------------------------------------------------
            | MAX DISCOUNT CHECK
            |--------------------------------------------------------------------------
            */
            if (
                $promo->max_discount_amount &&
                $discount > $promo->max_discount_amount
            ) {
    
                $discount = $promo->max_discount_amount;
            }
    
            /*
            |--------------------------------------------------------------------------
            | FINAL AMOUNT
            |--------------------------------------------------------------------------
            */
            $finalAmount = max(0, $cartTotal - $discount);
    
            /*
            |--------------------------------------------------------------------------
            | SAVE PROMOCODE IN CART
            |--------------------------------------------------------------------------
            */
            $cart->promocode_id = $promo->id;
            $cart->discount_amount = round($discount, 2);
            $cart->save();
    
            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */
            return response()->json([
                'status' => true,
                'message' => 'Promocode applied successfully',
                'data' => [
                    'promocode_id' => $promo->id,
                    'promocode' => $promo->code,
                    'cart_total' => round($cartTotal, 2),
                    'discount' => round($discount, 2),
                    'final_amount' => round($finalAmount, 2),
                ]
            ]);
        } catch (\Exception $e) {
    
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function removePromocode()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        $cart->update([
            'promocode_id' => null,
            'discount_amount' => 0
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Promocode removed'
        ]);
    }
}
