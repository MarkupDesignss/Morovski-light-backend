@extends('layouts.admin')

@section('title', 'View Item')
<style>
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
                <h2 class="text-3xl font-bold"
                    style="

        color: #2a1a05;
        font-family: Georgia, serif;
        margin: 0; ">
                    Item Details</h2>
                <p class="mt-2 text-sm text-stone-600">View complete product information</p>
            </div>
            <div class="flex items-center gap-4">

                  <a href="{{ route('admin.items.index') }}" class="btn-back">← Back to Items</a>
        <!--        <a href="{{ route('admin.items.edit', $item->id) }}"-->
        <!--            class="inline-flex items-center px-4 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300"-->
        <!--            style="-->

        <!--color: #2a1a05;-->
        <!--font-family: Georgia, serif;-->
        <!--margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);" >-->
        <!--            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
        <!--                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"-->
        <!--                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">-->
        <!--                </path>-->
        <!--            </svg>-->
        <!--            Edit Item-->
        <!--        </a>-->
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Info Card --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Product Overview --}}
                <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-stone-100" style="background: rgba(22, 12, 0, 0.02);">
                        <h3 class="text-lg font-semibold flex items-center" style="color: #160c00;">
                            <svg class="w-5 h-5 mr-2" style="color: #3a2819;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Product Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-stone-500 mb-1">Product Name</label>
                                <p class="text-stone-800 bg-stone-50 p-3 rounded-lg border border-stone-200 font-medium">
                                    {{ $item->name }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-stone-500 mb-1">SKU</label>
                                <p class="text-stone-800 bg-stone-50 p-3 rounded-lg border border-stone-200">
                                    <code class="bg-transparent">{{ $item->sku ?? 'N/A' }}</code>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-stone-500 mb-1">Price</label>
                                <p class="text-stone-800 bg-stone-50 p-3 rounded-lg border border-stone-200">
                                    <span class="text-2xl font-bold"
                                        style="color: #160c00;">₹{{ number_format($item->price, 2) }}</span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-stone-500 mb-1">Stock Quantity</label>
                                <p class="text-stone-800 bg-stone-50 p-3 rounded-lg border border-stone-200">
                                    @if ($item->quantity <= 10 && $item->quantity > 0)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                            <span class="w-2 h-2 mr-1 bg-amber-500 rounded-full"></span>
                                            Low Stock: {{ $item->quantity }} units
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
                                            {{ $item->quantity }} units available
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-stone-500 mb-1">Description</label>
                                <div class="text-stone-800 bg-stone-50 p-4 rounded-lg border border-stone-200">
                                    {{ $item->description ?? 'No description provided.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Specifications --}}
                @php
                    $specs = is_array($item->specifications)
                        ? $item->specifications
                        : json_decode($item->specifications, true);
                @endphp
                @if ($specs && count($specs) > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-stone-100" style="background: rgba(22, 12, 0, 0.02);">
                            <h3 class="text-lg font-semibold flex items-center" style="color: #160c00;">
                                <svg class="w-5 h-5 mr-2" style="color: #3a2819;" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                Technical Specifications
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($specs as $key => $value)
                                    <div class="bg-stone-50 rounded-lg p-3 border border-stone-200">
                                        <label
                                            class="block text-xs font-medium text-stone-500 mb-1">{{ ucfirst($key) }}</label>
                                        <p class="text-stone-800 font-medium">{{ $value }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Metadata --}}
                <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-center text-sm">
                            <div class="text-stone-500">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Created: {{ $item->created_at->format('M d, Y H:i') }}
                            </div>
                            <div class="text-stone-500">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                Last updated: {{ $item->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Images Card --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden sticky top-6">
                    <div class="px-6 py-4 border-b border-stone-100" style="background: rgba(22, 12, 0, 0.02);">
                        <h3 class="text-lg font-semibold flex items-center justify-between" style="color: #160c00;">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" style="color: #3a2819;" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Product Images
                            </span>
                            @if ($item->images->count() > 0)
                                <span class="text-xs px-2 py-1 rounded-full" style="background: #160c10; color: white;">
                                    {{ $item->model ?? 'Standard' }}
                                </span>
                            @endif
                        </h3>
                    </div>
                    <div class="p-6">
                        @if ($item->images->count() > 0)
                            <div class="space-y-4">
                                @foreach ($item->images as $image)
                                    <div class="relative group cursor-pointer">
                                        <!--onclick="openImageModal('{{ url('storage/' . $image->image) }}', '{{ $item->name }}')">-->
                                        <img src="{{ asset('storage/' . $image->image) }}"
                                            class=" rounded-xl shadow-sm object-cover transition-transform group-hover:scale-[1.02]"
                                            style="height: 200px; object-fit: cover;" alt="{{ $item->name }}">
                                        <div
                                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center">
                                            <div class="bg-white/90 rounded-lg p-2">
                                                <svg class="w-6 h-6" style="color: #160c00;" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7">
                                                    </path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="w-24 h-24 mx-auto rounded-full flex items-center justify-center mb-4"
                                    style="background: rgba(22, 12, 0, 0.08);">
                                    <svg class="w-12 h-12" style="color: #3a2819;" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <p class="text-stone-500">No images uploaded for this item.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Image Modal --}}
    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/75" id="imageModal">
        <div class="relative max-w-4xl mx-4" >
            <div class="bg-white rounded-2xl overflow-hidden shadow-2xl" style="position: absolute;
    right: 40%;width:300px;top:20%">
                <div class="px-6 py-4" style="background: linear-gradient(135deg, #3a2819, #160c00);">
                    <div class="flex justify-between items-center">
                        <h5 class="text-white font-semibold">Product Image</h5>
                        <button onclick="closeImageModal()" class="text-white/80 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6 flex items-center justify-center min-h-[400px]">
                <img 
                    src="" 
                    id="modalImage"
                    class="rounded-xl shadow-lg object-cover"
                    style="width: 300px; height: 300px;"
                    alt="Product Image"
                >
            </div>
                <div class="px-6 py-4 bg-stone-50 flex justify-end">
                    <a href="#" id="downloadImage" download
                        class="px-4 py-2 rounded-lg text-white transition-all duration-300"
                        style="background: linear-gradient(135deg, #3a2819, #160c00);">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openImageModal(imageUrl, itemName) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const downloadLink = document.getElementById('downloadImage');

            modalImage.src = imageUrl;
            downloadLink.href = imageUrl;
            downloadLink.download = itemName + '-image.jpg';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });

        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    </script>
@endsection
