<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ShippingAddressController extends Controller
{
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|min:10|max:15',
                'address_line_1' => 'required|string',
                'address_line_2' => 'nullable|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'postal_code' => 'required|string',
                'country' => 'required|string',
                'is_default' => 'nullable|boolean'
            ]);

            DB::beginTransaction();

            // If setting default → remove old default
            if ($request->boolean('is_default')) {
                ShippingAddress::where('user_id', $user->id)
                    ->update(['is_default' => false]);
            }

            $address = ShippingAddress::create([
                'user_id' => $user->id,
                ...$validated,
                'is_default' => $request->boolean('is_default', false)
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => __('messages.address_added_successfully'),
                'data' => $address
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
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

            $addresses = ShippingAddress::where('user_id', $user->id)
                ->orderByDesc('is_default')
                ->latest()
                ->get();

            return response()->json([
                'status' => true,
                'data' => $addresses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $validated = $request->validate([
                'full_name' => 'sometimes|required|string|max:255',
                'phone' => 'sometimes|required|string|min:10|max:15',
                'address_line_1' => 'sometimes|required|string',
                'address_line_2' => 'nullable|string',
                'city' => 'sometimes|required|string',
                'state' => 'sometimes|required|string',
                'postal_code' => 'sometimes|required|string',
                'country' => 'sometimes|required|string',
                'is_default' => 'nullable|boolean'
            ]);

            $address = ShippingAddress::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$address) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.address_not_found')
                ], 200);
            }

            DB::beginTransaction();

            if ($request->boolean('is_default')) {
                ShippingAddress::where('user_id', $user->id)
                    ->update(['is_default' => false]);
            }

            $address->update($validated);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => __('messages.address_updated'),
                'data' => $address
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            $address = ShippingAddress::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$address) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.address_not_found')
                ], 200);
            }

            // If deleting default → set another as default
            if ($address->is_default) {
                $next = ShippingAddress::where('user_id', $user->id)
                    ->where('id', '!=', $id)
                    ->first();

                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }

            $address->delete();

            return response()->json([
                'status' => true,
                'message' => __('messages.address_deleted')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function methods()
    {
        $shippingMethods = ShippingMethod::where('is_active', 1)
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'slug',
                'description',
                'delivery_time',
                'price',
                'is_free'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Shipping methods fetched successfully',
            'data' => $shippingMethods
        ]);
    }
}