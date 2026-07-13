@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 py-4 sm:py-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 style="font-size: 22px; sm:font-size: 26px; font-weight: 700; color: #2a1a05; letter-spacing: 1.5px; font-family: Georgia, serif; margin: 0;"
                    class="text-2xl sm:text-3xl">
                    {{ __('admin.header_settings_title') }}
                </h2>
                <p class="mt-1 sm:mt-2 text-xs sm:text-sm" style="color: #4B5565;">{{ __('admin.header_settings_subtitle') }}</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('admin.header.edit') }}"
                    class="inline-flex items-center px-3 sm:px-4 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02] text-sm sm:text-base"
                    style="background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    {{ __('admin.edit_header_btn') }}
                </a>
            </div>
        </div>

        <!-- Logo Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="border-b px-4 sm:px-6 py-3 sm:py-4" style="border-color: #e5e7eb;">
                <h3 class="text-base sm:text-lg font-semibold flex items-center" style="color: #0B1A20;">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" style="color: #162E38;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    {{ __('admin.current_logo_title') }}
                </h3>
            </div>
            <div class="p-4 sm:p-6">
                @if ($logo && $logo->logo)
                    <div class="flex flex-col items-center space-y-4">
                        <div class="p-3 sm:p-4 bg-gray-50 rounded-lg">
                            <img src="{{ asset('storage/' . $logo->logo) }}" alt="{{ __('admin.logo_alt') }}"
                                class="max-w-[200px] sm:max-w-xs max-h-24 sm:max-h-32 object-contain">
                        </div>
                        <p class="text-xs sm:text-sm" style="color: #6B7280;">{{ __('admin.current_logo_description') }}</p>
                    </div>
                @else
                    <div class="text-center py-6 sm:py-8">
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 sm:mb-4" style="color: #162E38; opacity: 0.3;" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <p class="text-sm" style="color: #9CA3AF;">{{ __('admin.no_logo_yet') }}</p>
                        <p class="text-xs mt-1" style="color: #6B7280;">{{ __('admin.no_logo_hint') }}</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Favicon Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="border-b px-4 sm:px-6 py-3 sm:py-4" style="border-color: #e5e7eb;">
                <h3 class="text-base sm:text-lg font-semibold flex items-center" style="color: #0B1A20;">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" style="color: #162E38;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16">
                        </path>
                    </svg>
                    {{ __('Favicon') }}
                </h3>
            </div>
        
            <div class="p-4 sm:p-6">
                @if ($logo && $logo->favicon)
                    <div class="flex flex-col items-center space-y-4">
                        <div class="p-3 sm:p-4 bg-gray-50 rounded-lg">
                            <img src="{{ asset('storage/' . $logo->favicon) }}"
                                alt="Favicon"
                                class="w-12 h-12 sm:w-16 sm:h-16 object-contain">
                        </div>
                        <p class="text-xs sm:text-sm" style="color: #6B7280;">
                            {{ __('Current favicon') }}
                        </p>
                    </div>
                @else
                    <div class="text-center py-6 sm:py-8">
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 sm:mb-4"
                            style="color: #162E38; opacity: 0.3;"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16">
                            </path>
                        </svg>
                        <p class="text-sm" style="color: #9CA3AF;">
                            {{ __('No favicon uploaded') }}
                        </p>
                        <p class="text-xs mt-1" style="color: #6B7280;">
                            {{ __('Please upload a favicon.') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Menus Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="border-b px-4 sm:px-6 py-3 sm:py-4" style="border-color: #e5e7eb;">
                <h3 class="text-base sm:text-lg font-semibold flex items-center" style="color: #0B1A20;">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" style="color: #162E38;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                        </path>
                    </svg>
                    {{ __('admin.navigation_menus_title') }}
                </h3>
            </div>
            <div class="p-4 sm:p-6">
                @if ($menus->count())
                    <div class="space-y-2">
                        @foreach ($menus as $menu)
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-3 rounded-lg transition-all hover:shadow-sm"
                                style="background: rgba(22, 46, 56, 0.03);">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: #162E38;"></span>
                                    <span class="font-medium text-sm sm:text-base break-all" style="color: #0B1A20;">{{ $menu->title }}</span>
                                </div>
                                <div class="self-start sm:self-auto">
                                    @if ($menu->status)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1" style="background: #28a745;"></span>
                                            {{ __('admin.active_status') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                            style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1" style="background: #dc3545;"></span>
                                            {{ __('admin.inactive_status') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 sm:py-8">
                        <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 sm:mb-4" style="color: #162E38; opacity: 0.3;" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <p class="text-sm" style="color: #9CA3AF;">{{ __('admin.no_menus_yet') }}</p>
                        <p class="text-xs mt-1" style="color: #6B7280;">{{ __('admin.no_menus_hint') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .transition-all {
            transition: all 0.3s ease;
        }
        
        /* Mobile optimizations */
        @media (max-width: 640px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
@endpush