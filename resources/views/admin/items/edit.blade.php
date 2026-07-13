@extends('layouts.admin')

@section('title', 'Edit Item')
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #28a745;
    }

    input:checked+.slider:before {
        transform: translateX(24px);
    }
    
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2rem;
        padding: 0.7rem 1.6rem;
        background: transparent;
        border: 1.5px solid #d4c4b4;
        border-radius: 40px;
        color: #5c4b3a;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
</style>

@section('content')
    <div class="space-y-8 min-h-screen p-6">

        {{-- HEADER --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold" style="color: #2a1a05; font-family: Georgia, serif; margin: 0;">Edit Item</h2>
                <p class="mt-2 text-sm text-stone-600">Update product information for: <span class="font-semibold"
                        style="color: #3a2819;">{{ $item->name }}</span></p>
            </div>
              <a href="{{ route('admin.items.index') }}" class="btn-back">← Back to Items</a>
        </div>

        {{-- ERROR MESSAGES --}}
        @if ($errors->any())
            <div class="rounded-xl bg-rose-50 border-l-4 p-4" style="border-left-color: #e11d48;">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-rose-800">Please fix the following errors:</h3>
                        <ul class="mt-2 text-sm text-rose-700 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- EDIT FORM --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
            <form action="{{ route('admin.items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="px-6 py-4 border-b border-stone-100" style="background: rgba(22, 12, 0, 0.02);">
                    <h3 class="text-lg font-semibold flex items-center" style="color: #160c00;">
                        <svg class="w-5 h-5 mr-2" style="color: #3a2819;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Edit Item: {{ $item->name }}
                    </h3>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- LEFT COLUMN --}}
                        <div class="space-y-6">
                            {{-- Name Field --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #160c00;">
                                    Item Name <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <input type="text" name="name" id="name"
                                        value="{{ old('name', $item->name) }}"
                                        class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:outline-none focus:ring-2 transition @error('name') border-rose-500 @enderror"
                                        style="focus:ring-color: #3a2819;" placeholder="Enter item name" required>
                                </div>
                                @error('name')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Category Field --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #160c00;">
                                    Category <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                            </path>
                                        </svg>
                                    </div>
                                    <select name="category_id" id="category_id"
                                        class="w-full pl-10 pr-10 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:outline-none focus:ring-2 transition appearance-none @error('category_id') border-rose-500 @enderror"
                                        style="focus:ring-color: #3a2819;" required>
                                        <option value="">-- Select Category --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('category_id')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- SKU Field --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #160c00;">
                                    SKU (Stock Keeping Unit)
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <input type="text" name="sku" id="sku"
                                        value="{{ old('sku', $item->sku) }}"
                                        class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:outline-none focus:ring-2 transition @error('sku') border-rose-500 @enderror"
                                        style="focus:ring-color: #3a2819;" placeholder="Enter SKU code">
                                </div>
                                {{-- WARNING MESSAGE AREA --}}
                                <div id="sku-warning" class="mt-2 text-sm hidden"></div>
                                @error('sku')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Featured Toggle --}}
                            <div class="form-group">
                                <label class="form-label d-block mb-2">Featured</label>
                                <input type="hidden" name="is_featured" value="0">
                                <label class="switch">
                                    <input type="checkbox" name="is_featured" value="1"
                                        {{ $item->is_featured ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                            </div>

                            {{-- Price Field --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #160c00;">
                                    Price <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 5h12M6 8h10c2.2 0 3 1.5 3 3s-.8 3-3 3H6l5 5M10 14h6" />
                                        </svg>
                                    </div>
                                    <input type="number" step="0.01" min="1" name="price" id="price"
                                        value="{{ old('price', $item->price) }}"
                                        class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:outline-none focus:ring-2 transition @error('price') border-rose-500 @enderror"
                                        placeholder="0.00" required>
                                </div>
                                @error('price')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- RIGHT COLUMN --}}
                        <div class="space-y-6">
                            {{-- Current Images --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #160c00;">
                                    Current Images
                                </label>
                                @if ($item->images->count() > 0)
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                        <!--   @foreach ($item->images as $image)-->
                                        <!--    <div id="image-{{ $image->id }}" class="relative group">-->
                                        <!--        <div class="rounded-lg overflow-hidden shadow-sm border border-stone-200">-->
                                        <!--            <img src="{{ asset('storage/' . $image->image) }}"-->
                                        <!--                class="w-full h-32 object-cover">-->
                                        <!--            <div-->
                                        <!--                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">-->
                                        <!--                <button type="button"-->
                                        <!--                    class="delete-image p-2 bg-rose-600 rounded-full text-white hover:bg-rose-700 transition"-->
                                        <!--                    data-id="{{ $image->id }}">-->
                                        <!--                    <svg class="w-5 h-5" fill="none" stroke="currentColor"-->
                                        <!--                        viewBox="0 0 24 24">-->
                                        <!--                        <path stroke-linecap="round" stroke-linejoin="round"-->
                                        <!--                            stroke-width="2"-->
                                        <!--                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">-->
                                        <!--                        </path>-->
                                        <!--                    </svg>-->
                                        <!--                </button>-->
                                        <!--            </div>-->
                                        <!--        </div>-->
                                        <!--    </div>-->
                                        <!--@endforeach-->
                                     @foreach ($item->images as $image)
                                    <div id="image-{{ $image->id }}" class="relative">
                                        <div class="rounded-lg overflow-hidden shadow-sm border border-stone-200">
                                            <img src="{{ asset('storage/' . $image->image) }}"
                                                 class="w-full h-32 object-cover">
                                
                                            <!-- Delete Button -->
                                            <button type="button"
                                                class="delete-image absolute top-2 right-2 w-6 h-6 flex items-center justify-center bg-red-600 text-white rounded-full shadow hover:bg-red-700 transition"
                                                data-id="{{ $image->id }}"
                                                title="Delete Image">
                                                &times;
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                                    </div>
                                @else
                                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-amber-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-amber-700 text-sm">No images uploaded yet.</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Add New Images --}}
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: #160c00;">
                                    Add New Images
                                </label>
                                <div class="dropzone-area border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all duration-300 hover:border-stone-400"
                                    style="background: rgba(22, 12, 0, 0.02); border-color: #e5e5e5;">
                                    <input type="file" name="images[]" id="images" class="hidden" multiple
                                        accept="image/jpeg,image/png,image/jpg,image/webp">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-stone-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <p class="text-stone-600 mb-1">Click or drag images here to upload</p>
                                    <p class="text-xs text-stone-400">Existing images will remain. You can add more.</p>
                                </div>
                                @error('images.*')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                                <div id="image-preview" class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Description Field --}}
                    <div class="mt-8">
                        <label class="block text-sm font-medium mb-2" style="color: #160c00;">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="5"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:outline-none focus:ring-2 transition @error('description') border-rose-500 @enderror"
                            style="focus:ring-color: #3a2819;" placeholder="Enter product description...">{{ old('description', $item->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Item Type --}}
                    <div class="form-group mt-4">
                        <label for="type">Item Type <span class="required-star">*</span></label>
                        <select name="type" id="type" class="form-control @error('type') is-invalid @enderror">
                            <option value="">— Select Item Type —</option>
                            <option value="online" {{ old('type', $item->type) == 'online' ? 'selected' : '' }}>Online
                            </option>
                            <option value="offline" {{ old('type', $item->type) == 'offline' ? 'selected' : '' }}>Offline
                            </option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Item Modle --}}
                    <div class="form-group mt-4">
                        <label for="model">Item model <span class="required-star">*</span></label>
                        <select name="model" id="model" class="form-control @error('model') is-invalid @enderror">
                            <option value="">— Select Item model —</option>
                            <option value="New" {{ old('model', $item->model) == 'New' ? 'selected' : '' }}>New
                            </option>
                            <option value="Best Seller"
                                {{ old('model', $item->model) == 'Best Seller' ? 'selected' : '' }}>
                                Best Seller</option>
                            <option value="Hot Deal" {{ old('model', $item->model) == 'Hot Deal' ? 'selected' : '' }}>
                                Hot Deal</option>
                        </select>
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ========== WAREHOUSE STOCK ALLOCATION (NEW) ========== --}}
                    <div class="mt-8">
                        <label class="block text-sm font-medium mb-3" style="color: #160c00;">
                            Warehouse Stock Allocation <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-3">
                            @forelse($warehouses as $warehouse)
                                @php
                                    $currentQty = $warehouseStocks[$warehouse->id]->quantity ?? 0;
                                @endphp
                                <div class="flex items-center gap-4 p-3 bg-stone-50 rounded-xl border border-stone-200">
                                    <div class="w-1/3">
                                        <span class="font-medium">{{ $warehouse->name }}</span>
                                        <span class="text-xs text-stone-500 ml-2">({{ $warehouse->code }})</span>
                                    </div>
                                    <div class="flex-1">
                                        <input type="number" name="warehouse_quantities[{{ $warehouse->id }}]"
                                            value="{{ old('warehouse_quantities.' . $warehouse->id, $currentQty) }}"
                                            class="w-full px-4 py-2 bg-white border border-stone-300 rounded-lg focus:ring-2 focus:ring-stone-400"
                                            min="0" placeholder="Quantity">
                                    </div>
                                </div>
                            @empty
                                <p class="text-amber-600 text-sm">No active warehouses found. Please <a
                                        href="{{ route('admin.warehouses.create') }}" class="underline">create
                                        warehouses</a> first.</p>
                            @endforelse
                        </div>
                        <p class="text-xs text-stone-500 mt-2">Set the available quantity for each warehouse. Total stock
                            will be sum of all warehouses.</p>
                        @error('warehouse_quantities')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dynamic Specifications --}}
                    <div class="mt-8">
                        <label class="block text-sm font-medium mb-2" style="color: #160c00;">
                            Specifications
                        </label>
                        <div id="specifications-container" class="space-y-3">
                            @php
                                $specs = is_array($item->specifications)
                                    ? $item->specifications
                                    : json_decode($item->specifications, true);
                            @endphp
                            @if ($specs && count($specs) > 0)
                                @foreach ($specs as $key => $value)
                                    <div class="spec-row flex flex-col md:flex-row gap-3">
                                        <div class="flex-1">
                                            <input type="text" name="spec_key[]"
                                                class="w-full px-4 py-2 bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:ring-2"
                                                style="focus:ring-color: #3a2819;" placeholder="Specification name"
                                                value="{{ $key }}">
                                        </div>
                                        <div class="flex-1">
                                            <input type="text" name="spec_value[]"
                                                class="w-full px-4 py-2 bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:ring-2"
                                                style="focus:ring-color: #3a2819;" placeholder="Value"
                                                value="{{ $value }}">
                                        </div>
                                        <div class="md:w-auto">
                                            <button type="button"
                                                class="remove-spec w-full md:w-auto px-4 py-2 rounded-lg text-rose-600 hover:bg-rose-50 transition">
                                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" id="add-spec"
                            class="mt-3 inline-flex items-center px-4 py-2 rounded-lg text-white transition-all duration-300"
                            style="color: #2a1a05; font-family: Georgia, serif; margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Add Specification
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-stone-100" style="background: rgba(22, 12, 0, 0.02);">
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.items.index') }}"
                            class="px-6 py-2 rounded-xl text-stone-700 bg-stone-100 hover:bg-stone-200 transition-all duration-300">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 rounded-xl text-white font-semibold transition-all duration-300 transform hover:scale-[1.02] shadow-md hover:shadow-lg"
                            style="color: #2a1a05; font-family: Georgia, serif; margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Update Item
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <form id="delete-image-form" action="{{ url('admin/item-image') }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .spec-row {
            animation: fadeInUp 0.3s ease;
        }

        .dropzone-area.drag-over {
            border-color: #3a2819;
            background: rgba(58, 40, 25, 0.05);
        }
    </style>

    <script>
        // Drag and drop functionality
        const dropzone = document.querySelector('.dropzone-area');
        const fileInput = document.getElementById('images');

        if (dropzone) {
            dropzone.addEventListener('click', () => fileInput.click());
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('drag-over');
            });
            dropzone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                dropzone.classList.remove('drag-over');
            });
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('drag-over');
                const files = e.dataTransfer.files;
                fileInput.files = files;
                previewImages(files);
            });
        }

        function previewImages(files) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'relative group';
                        col.innerHTML = `
                            <div class="rounded-lg overflow-hidden shadow-sm border border-stone-200">
                                <img src="${e.target.result}" class="w-full h-32 object-cover">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button type="button" class="remove-preview text-white bg-rose-600 p-1 rounded-full">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="p-2 bg-white">
                                    <p class="text-xs text-stone-600 truncate">${file.name}</p>
                                </div>
                            </div>
                        `;
                        preview.appendChild(col);
                        col.querySelector('.remove-preview').addEventListener('click', () => col.remove());
                    }
                    reader.readAsDataURL(file);
                }
            }
        }

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                previewImages(this.files);
            });
        }

        // Delete existing image with confirmation
        document.querySelectorAll('.delete-image').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm(
                        '⚠️ Are you sure you want to delete this image?\n\nThis action cannot be undone!'
                    )) {
                    const imageId = this.dataset.id;
                    const form = document.getElementById('delete-image-form');
                    form.action = "{{ url('admin/item-image') }}" + '/' + imageId;
                    form.submit();
                }
            });
        });

        // Dynamic specifications
        document.getElementById('add-spec').addEventListener('click', function() {
            const container = document.getElementById('specifications-container');
            const row = document.createElement('div');
            row.className = 'spec-row flex flex-col md:flex-row gap-3';
            row.innerHTML = `
                <div class="flex-1">
                    <input type="text" name="spec_key[]" class="w-full px-4 py-2 bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:ring-2" style="focus:ring-color: #3a2819;" placeholder="Specification name (e.g., Brand)">
                </div>
                <div class="flex-1">
                    <input type="text" name="spec_value[]" class="w-full px-4 py-2 bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:ring-2" style="focus:ring-color: #3a2819;" placeholder="Value (e.g., Nike)">
                </div>
                <div class="md:w-auto">
                    <button type="button" class="remove-spec w-full md:w-auto px-4 py-2 rounded-lg text-rose-600 hover:bg-rose-50 transition">
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Remove
                    </button>
                </div>
            `;
            container.appendChild(row);
            row.querySelector('.remove-spec').addEventListener('click', () => row.remove());
        });

        document.querySelectorAll('.remove-spec').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.spec-row').remove();
            });
        });

        const skuInput = document.getElementById('sku');
        const skuWarning = document.getElementById('sku-warning');

        let warningTimeout = null;

        skuInput.addEventListener('blur', function() {
            const sku = this.value.trim();
            if (sku.length < 2) {
                skuWarning.classList.add('hidden');
                return;
            }

            // Clear previous timeout
            if (warningTimeout) clearTimeout(warningTimeout);

            warningTimeout = setTimeout(() => {
                fetch('{{ route('admin.items.checkSkuSimilar') }}?sku=' + encodeURIComponent(sku))
                    .then(response => response.json())
                    .then(data => {
                        if (data.similar && data.similar.length > 0) {
                            skuWarning.innerHTML = `
                                <div class="flex items-start p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800">
                                    <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div>
                                        <strong class="font-semibold">Similar SKU exists:</strong>
                                        <ul class="list-disc list-inside text-sm mt-1">
                                            ${data.similar.map(s => `<li>${escapeHtml(s)}</li>`).join('')}
                                        </ul>
                                        <p class="text-xs mt-1">This is only a warning – you can still save.</p>
                                    </div>
                                </div>
                            `;
                            skuWarning.classList.remove('hidden');
                        } else {
                            skuWarning.classList.add('hidden');
                        }
                    })
                    .catch(err => {
                        console.warn('SKU check failed:', err);
                        skuWarning.classList.add('hidden');
                    });
            }, 400); // debounce to avoid excessive requests
        });

        // Helper to escape HTML
        function escapeHtml(str) {
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }
    </script>
@endsection
