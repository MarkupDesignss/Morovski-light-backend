@extends('layouts.admin')

@section('title', 'Manage Items')

@section('content')
    <div class="space-y-8 min-h-screen p-6">


        {{-- CSV IMPORT ERRORS --}}
        @if (session('errors') && count(session('errors')) > 0)
            <div class="rounded-xl bg-rose-50 border-l-4 p-4 mb-4" style="border-left-color: #e11d48;">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-rose-800">CSV Import Errors</h3>
                        <ul class="mt-2 text-sm text-rose-700 list-disc list-inside">
                            @foreach (session('errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- HEADER --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold"
                    style="

                        color: #2a1a05;
                        font-family: Georgia, serif;
                        margin: 0;">
                    Items Management</h2>
                <p class="mt-2 text-sm text-stone-600">Manage your product inventory and items</p>
            </div>
            <div class="flex items-center gap-4">
               
                <a href="{{ route('admin.items.create') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"
                    style="

        color: #2a1a05;
        font-family: Georgia, serif;
        margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Item
                </a>
                {{-- NEW: Import CSV Button --}}
                <a href="{{ route('admin.items.import.form') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"
                    style="color: #2a1a05; font-family: Georgia, serif; margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import CSV
                </a>
            </div>
        </div>

        {{-- STATS CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-stone-500">Total Items</p>
                        <p class="text-2xl font-bold mt-1" style="color: #160c00;">{{ $items->total() }}</p>
                    </div>
                    <div class="p-3 rounded-xl" style="background: rgba(22, 12, 0, 0.08);">
                        <svg class="w-6 h-6" style="color: #3a2819;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-stone-500">Low Stock Items</p>
                        <p class="text-2xl font-bold mt-1 text-amber-600">
                            {{ \App\Models\Item::whereBetween('quantity', [1, 20])->count() }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-stone-500">Total Value</p>
                        <p class="text-2xl font-bold mt-1" style="color: #160c00;">
                            ₹{{ number_format($items->sum('price'), 0) }}</p>
                    </div>
                    <div class="p-3 rounded-xl" style="background: rgba(22, 12, 0, 0.08);">
                        <svg class="h-6 w-6 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 5h12M6 8h10c2.2 0 3 1.5 3 3s-.8 3-3 3H6l5 5M10 14h6" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-stone-500">Categories</p>
                        <p class="text-2xl font-bold mt-1" style="color: #160c00;">
                            {{ $items->groupBy('category_id')->count() }}</p>
                    </div>
                    <div class="p-3 rounded-xl" style="background: rgba(22, 12, 0, 0.08);">
                        <svg class="w-6 h-6" style="color: #3a2819;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- ITEMS TABLE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100" style="background: rgba(22, 12, 0, 0.02);">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h3 class="text-lg font-semibold flex items-center" style="color: #160c00;">
                        <svg class="w-5 h-5 mr-2" style="color: #3a2819;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Items List
                    </h3>

                    {{-- Search Form --}}
                    <form method="GET" action="{{ route('admin.items.index') }}" class="flex gap-2">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search by name or SKU..."
                                class="pl-10 pr-4 py-2 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:outline-none focus:ring-2 transition w-64"
                                style="focus:ring-color: #3a2819;">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <select name="stock" class="px-3 py-2 bg-white border border-stone-200 rounded-xl text-sm">
                            <option value="">All Stock</option>
                            <option value="in_stock" {{ request('stock') == 'in_stock' ? 'selected' : '' }}>In Stock
                            </option>
                            <option value="low_stock" {{ request('stock') == 'low_stock' ? 'selected' : '' }}>Low Stock
                            </option>
                            <option value="out_of_stock" {{ request('stock') == 'out_of_stock' ? 'selected' : '' }}>Out of
                                Stock</option>
                        </select>

                        <select name="type" class="px-3 py-2 bg-white border border-stone-200 rounded-xl text-sm">
                            <option value="">All Types</option>
                            <option value="online" {{ request('type') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ request('type') == 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>

                        <button type="submit" class="px-4 py-2 rounded-xl text-white transition-all duration-300"
                            style="background: linear-gradient(135deg, #3a2819, #160c00);">
                            Search
                        </button>
                        @if (request('search') || request('stock') || request('type'))
                            <a href="{{ route('admin.items.index') }}"
                                class="px-4 py-2 rounded-xl text-stone-700 bg-stone-100 hover:bg-stone-200 transition">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-stone-100" style="background: rgba(22, 12, 0, 0.02);">
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">Sr. No.</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">Image</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">Product Details
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">Category Name
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">SKU</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">Stock</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">Price</th>
                            <!--<th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">Model</th>-->
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($items as $item)
                            <tr class="hover:bg-stone-50 transition-colors duration-200">
                                <td class="px-6 py-4 text-sm font-medium" style="color: #160c00;">{{ $loop->iteration }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($item->images->count() > 0)
                                        <div class="relative inline-block">
                                            <img src="{{ asset('storage/' . $item->images->first()->image) }}"
                                                alt="{{ $item->name }}" class="rounded-lg shadow-sm"
                                                style="width: 55px; height: 55px; object-fit: cover;">
                                            @if ($item->images->count() > 1)
                                                <span
                                                    class="absolute -top-2 -right-2 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold text-white rounded-full"
                                                    style="background: #3a2819;">
                                                    +{{ $item->images->count() - 1 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="bg-stone-100 rounded-lg flex items-center justify-center"
                                            style="width: 55px; height: 55px;">
                                            <svg class="w-6 h-6 text-stone-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-stone-800">{{ $item->name }}</p>
                                        @if ($item->description)
                                            <p class="text-sm text-stone-500 mt-1">
                                                {{ Str::limit($item->description, 50) }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-stone-800">{{ $item->category->name }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="px-2 py-1 bg-stone-100 rounded text-sm"
                                        style="color: #3a2819;">{{ $item->sku ?? 'N/A' }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($item->quantity <= 20 && $item->quantity > 0)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                            <span class="w-2 h-2 mr-1 bg-amber-500 rounded-full"></span>
                                            Low: {{ $item->quantity }}
                                        </span>
                                    @elseif($item->quantity <= 0)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700">
                                            <span class="w-2 h-2 mr-1 bg-rose-500 rounded-full"></span>
                                            Out of Stock
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                            <span class="w-2 h-2 mr-1 bg-emerald-500 rounded-full"></span>
                                            {{ $item->quantity }} units
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-lg"
                                        style="color: #160c00;">₹{{ number_format($item->price, 2) }}</span>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.items.show', $item->id) }}"
                                            class="p-2 rounded-lg text-white transition-all duration-300"
                                            style="background: linear-gradient(135deg, #3a2819, #160c00);"
                                            title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.items.edit', $item->id) }}"
                                            class="p-2 rounded-lg text-stone-700 bg-stone-100 hover:bg-stone-200 transition"
                                            title="Edit Item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                        <button type="button"
                                            class="p-2 rounded-lg text-white bg-rose-600 hover:bg-rose-700 transition delete-btn"
                                            data-id="{{ $item->id }}" data-name="{{ $item->name }}"
                                            title="Delete Item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                        <form id="delete-form-{{ $item->id }}"
                                            action="{{ route('admin.items.destroy', $item->id) }}" method="POST"
                                            style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 rounded-full flex items-center justify-center mb-4"
                                            style="background: rgba(22, 12, 0, 0.08);">
                                            <svg class="w-10 h-10" style="color: #3a2819;" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                                                </path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-stone-800 mb-1">No Items Found</h3>
                                        <p class="text-sm text-stone-500 mb-4">Get started by adding your first item</p>
                                        <a href="{{ route('admin.items.create') }}"
                                            class="inline-flex items-center px-4 py-2 rounded-lg text-white shadow-md"
                                            style="background: linear-gradient(135deg, #3a2819, #160c00);">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add First Item
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="px-6 py-4 border-t border-stone-100">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-stone-500">
                            Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of
                            {{ $items->total() }} results
                        </div>
                        <div>
                            {{ $items->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.transition = 'opacity 0.5s ease';
                    successMessage.style.opacity = '0';
                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 500);
                }, 3000);
            }

            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const itemId = this.dataset.id;
                    const itemName = this.dataset.name;

                    Swal.fire({
                        title: 'Delete Item',
                        html: `
                            <div class="text-center">
                                <p class="text-md text-stone-700 mb-2">Are you sure you want to delete:
                                    <span class="font-bold" style="color: #160c00;">"${itemName}"</span>?
                                </p>
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-left">
                                    <p class="text-xs text-amber-700">
                                        <span class="font-bold">⚠️ Warning:</span> This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DC2626',
                        cancelButtonColor: '#78716C',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        customClass: {
                            confirmButton: 'px-4 py-2 rounded-lg text-white font-medium',
                            cancelButton: 'px-4 py-2 rounded-lg text-stone-700 bg-stone-200 font-medium hover:bg-stone-300',
                            popup: 'rounded-2xl'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Deleting...',
                                html: 'Please wait while we delete the item.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            document.getElementById(`delete-form-${itemId}`).submit();
                        }
                    });
                });
            });
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('
                                    success ') }}',
                timer: 3000,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-2xl'
                }
            });
        @endif
    </script>
@endsection
