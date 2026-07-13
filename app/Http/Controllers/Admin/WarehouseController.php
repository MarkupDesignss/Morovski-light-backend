<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index()
    {
        $warehouses = Warehouse::orderBy('name')->paginate(15);
        return view('admin.warehouses.index', compact('warehouses'));
    }

    /**
     * Show form to create a new warehouse.
     */
    public function create()
    {
        return view('admin.warehouses.create');
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pin_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:191',
            'contact_phone' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $warehouse = Warehouse::create($request->all());

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    /**
     * Show form to edit warehouse.
     */
    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        return view('admin.warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified warehouse.
     */
    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pin_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:191',
            'contact_phone' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $warehouse->update($request->all());

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        // Check if any staff is assigned to this warehouse
        if ($warehouse->users()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete warehouse with assigned staff.');
        }
        
        // Check if any stock exists in warehouse_items
        if ($warehouse->items()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete warehouse with existing stock.');
        }
        
        $warehouse->delete();
        
        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
    
    /**
     * Toggle warehouse active status (optional).
     */
    public function toggleStatus($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->is_active = !$warehouse->is_active;
        $warehouse->save();
        
        $status = $warehouse->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Warehouse {$status} successfully.");
    }
}