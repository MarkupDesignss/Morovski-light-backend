<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingCharge;

class ShippingChargeController extends Controller
{
    // List
    public function index()
    {
        // paginate results so view can use firstItem()/links()
        $charges = ShippingCharge::latest()->paginate(10);

        // aggregate counts for the stats cards
        $total = ShippingCharge::count();
        $flatCount = ShippingCharge::where('type', 'flat')->count();
        $weightCount = ShippingCharge::where('type', 'weight')->count();
        $priceCount = ShippingCharge::where('type', 'price')->count();

        return view('admin.shipping.index', compact('charges', 'total', 'flatCount', 'weightCount', 'priceCount'));
    }

    // Store
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:flat,weight,price',
            'charge' => 'required|numeric',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
        ]);

        ShippingCharge::create($request->all());

        return back()->with('success', 'Shipping charge added');
    }

    // Edit
    public function edit($id)
    {
        $charge = ShippingCharge::findOrFail($id);
        return view('admin.shipping.edit', compact('charge'));
    }

    // Update
    public function update(Request $request, $id)
    {
        $charge = ShippingCharge::findOrFail($id);

        $charge->update($request->all());

        return redirect()->route('admin.shipping.index')
            ->with('success', 'Updated successfully');
    }

    // Delete
    public function destroy($id)
    {
        ShippingCharge::findOrFail($id)->delete();

        return back()->with('success', 'Deleted');
    }
}
