@extends('layouts.admin')
<style>
    video {
    transition: transform 0.2s ease;
}

video:hover {
    transform: scale(1.05);
}
</style>
@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h2 style="color:#2f5365  font-size: 26px;
        font-weight: 700;
        color: #2a1a05;
        letter-spacing: 1.5px;
        font-family: Georgia, serif;
        margin: 0;"
                    class="text-3xl font-bold">{{ __('admin.pages') }}</h2>
                <p class="mt-2 text-sm" style="color: #4B5565;">{{ __('admin.manage_pages_description') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.pages.create') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"
                    style="

        color: #2a1a05;
        font-family: Georgia, serif;
        margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('admin.add_page') }}
                </a>
            </div>
        </div>

        <!-- Pages Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                            style="color: #0B1A20;">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                            style="color: #0B1A20;">{{ __('admin.title') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                            style="color: #0B1A20;">{{ __('admin.images') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                            style="color: #0B1A20;">{{ __('admin.status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                            style="color: #0B1A20;">{{ __('admin.action') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($pages as $page)
                        <tr class="hover:bg-gray-50 transition">
                             <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" style="color: #162E38;">{{ $loop->iteration }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium" style="color: #162E38;">{{ $page->title }}</div>
                            </td>
                           <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex gap-2">
                            
                                    @forelse ($page->images->take(2) as $img)
                            
                                        @php
                                            $extension = strtolower(pathinfo($img->image, PATHINFO_EXTENSION));
                                            $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);
                                        @endphp
                            
                                        @if ($isVideo)
                                            <video class="w-10 h-10 object-cover rounded-lg shadow-sm"
                                                   muted
                                                   preload="metadata">
                                                <source src="{{ asset('storage/' . $img->image) }}">
                                            </video>
                                        @else
                                            <img src="{{ asset('storage/' . $img->image) }}"
                                                 class="w-10 h-10 object-cover rounded-lg shadow-sm"
                                                 alt="Media">
                                        @endif
                            
                                    @empty
                                        <span class="text-xs" style="color: #9CA3AF;">
                                            {{ __('admin.no_images') }}
                                        </span>
                                    @endforelse
                            
                                    @if ($page->images->count() > 2)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full"
                                            style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                                            +{{ $page->images->count() - 2 }}
                                        </span>
                                    @endif
                            
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($page->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1" style="background: #28a745;"></span>
                                        {{ __('admin.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1" style="background: #dc3545;"></span>
                                        {{ __('admin.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-3">
                                    <a href="{{ route('admin.pages.edit', $page) }}"
                                        class="inline-flex items-center px-3 py-1 rounded-md transition-all duration-300 hover:scale-105"
                                        style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        {{ __('admin.edit') }}
                                    </a>

                                    <form action="{{ route('admin.pages.destroy', $page) }}" method="POST"
                                        onsubmit="return confirm('{{ __('admin.delete_page_confirm') }}')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1 rounded-md transition-all duration-300 hover:scale-105"
                                            style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                            {{ __('admin.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 mb-4" style="color: #162E38; opacity: 0.3;" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <h3 class="text-lg font-medium" style="color: #4B5565;">
                                        {{ __('admin.no_pages_found') }}</h3>
                                    <p class="mt-1 text-sm" style="color: #9CA3AF;">{{ __('admin.create_first_page') }}</p>
                                    <a href="{{ route('admin.pages.create') }}"
                                        class="mt-4 inline-flex items-center px-4 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300"
                                        style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        {{ __('admin.add_page') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if (isset($pages) && method_exists($pages, 'links'))
            <div class="mt-6">
                {{ $pages->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        /* Custom scrollbar for table */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #162E38;
            border-radius: 10px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #0B1A20;
        }

        /* Smooth transitions */
        .transition-all {
            transition: all 0.3s ease;
        }

        /* Table row hover effect */
        tbody tr {
            transition: all 0.2s ease;
        }

        /* Image hover effect */
        img {
            transition: transform 0.2s ease;
        }

        img:hover {
            transform: scale(1.05);
        }
    </style>
@endpush
