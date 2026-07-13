<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promocode;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = Promocode::query();

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        $coupons = $query->latest()->paginate(10);

        return view('admin.coupons.index', compact('coupons'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE FORM
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('admin.coupons.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:promocodes,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:1',
            'user_type' => 'nullable|in:b2b,b2c',
            'min_cart_amount' => 'nullable|numeric',
            'max_discount_amount' => 'nullable|numeric',
            'usage_limit' => 'nullable|integer',
            'per_user_limit' => 'nullable|integer',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'status' => 'required|boolean',
        ]);

        Promocode::create([
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'value' => $request->value,
            'user_type' => $request->user_type,
            'min_cart_amount' => $request->min_cart_amount,
            'max_discount_amount' => $request->max_discount_amount,
            'usage_limit' => $request->usage_limit,
            'used_count' => 0,
            'per_user_limit' => $request->per_user_limit,
            'starts_at' => $request->starts_at,
            'expires_at' => $request->expires_at,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT FORM
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $coupon = Promocode::findOrFail($id);

        return view('admin.coupons.edit', compact('coupon'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $coupon = Promocode::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:promocodes,code,' . $coupon->id,
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:1',
            'user_type' => 'nullable|in:b2b,b2c',
            'min_cart_amount' => 'nullable|numeric',
            'max_discount_amount' => 'nullable|numeric',
            'usage_limit' => 'nullable|integer',
            'per_user_limit' => 'nullable|integer',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'status' => 'required|boolean',
        ]);

        $coupon->update([
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'value' => $request->value,
            'user_type' => $request->user_type,
            'min_cart_amount' => $request->min_cart_amount,
            'max_discount_amount' => $request->max_discount_amount,
            'usage_limit' => $request->usage_limit,
            'per_user_limit' => $request->per_user_limit,
            'starts_at' => $request->starts_at,
            'expires_at' => $request->expires_at,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $coupon = Promocode::findOrFail($id);

        $coupon->delete();

        return redirect()
            ->back()
            ->with('success', 'Coupon deleted successfully');
    }
}