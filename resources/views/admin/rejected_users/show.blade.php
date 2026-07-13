@extends('layouts.admin')

@section('title', 'Rejected Application Details')

@section('content')
    <div class="space-y-8 min-h-screen p-6">

        {{-- HEADER WITH GRADIENT --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold  text-transparent" style="color:#2f5365">
                    {{ __('admin.rejected_application_details') }}
                </h2>
                <p class="mt-2 text-sm text-gray-700">
                    {{ __('admin.review_rejected_business') }}
                </p>
            </div>

            <div class="flex items-center gap-4">
                {{-- Date Badge --}}
                <div class="px-4 py-2 rounded-lg border text-white"
                    style="background: linear-gradient(90deg, #376377, #2f5365);">
                    <div class="text-sm">{{ __('admin.rejected_on') }}
                        {{ \Carbon\Carbon::parse($rejectedUser->rejected_at)->format('M d, Y') }}</div>
                </div>

                {{-- Back Button --}}
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300"
                    style="background: linear-gradient(90deg, #376377, #2f5365);">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('admin.back_to_list') }}
                </a>
            </div>
        </div>

        {{-- MAIN CONTENT GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT COLUMN - USER PROFILE CARD --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Header Gradient --}}
                    <div class="h-24" style="background: linear-gradient(135deg, #376377, #2f5365);"></div>

                    {{-- Profile Content --}}
                    <div class="px-6 pb-6">
                        {{-- Avatar - Centered Overlapping --}}
                        <div class="flex justify-center -mt-12">
                            <div class="h-24 w-24 rounded-full flex items-center justify-center text-white font-bold text-2xl border-4 border-white shadow-lg"
                                style="background: linear-gradient(135deg, #376377, #2f5365);">
                                {{ strtoupper(substr($rejectedUser->email, 0, 2)) }}
                            </div>
                        </div>

                        {{-- User Info --}}
                        <div class="text-center mt-4">
                            <h3 class="text-xl font-bold text-gray-900">{{ $rejectedUser->email }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ __('admin.business_account_application') }}</p>

                            {{-- Status Badge --}}
                            <div class="mt-3">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                    <span class="w-2 h-2 mr-1 bg-red-500 rounded-full"></span>
                                    {{ __('admin.rejected') }}
                                </span>
                            </div>
                        </div>

                        {{-- Quick Stats --}}
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs text-gray-500"> {{ __('admin.application_id') }}</p>
                                    <p class="text-sm font-semibold text-gray-900">#{{ $rejectedUser->id }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">{{ __('admin.contact') }}</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $rejectedUser->phone ?? 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN - DETAILS CARDS --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- REJECTION REASON CARD --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200"
                        style="background: linear-gradient(90deg, rgba(55,99,119,0.1), rgba(47,83,101,0.1));">
                        <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" style="color: #376377;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            {{ __('admin.rejected_reason') }}
                        </h4>
                    </div>
                    <div class="p-6">
                        <div class="bg-red-50 border-l-4 border-red-500 rounded-r-xl p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-800">
                                        {{ $rejectedUser->reason }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- APPLICATION DETAILS CARD --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200"
                        style="background: linear-gradient(90deg, rgba(55,99,119,0.1), rgba(47,83,101,0.1));">
                        <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" style="color: #376377;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ __('admin.application_information') }}
                        </h4>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Email --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">{{ __('admin.email') }}</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span class="text-gray-900">{{ $rejectedUser->email }}</span>
                                </div>
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500 mb-2">{{ __('admin.contact') }}</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                        </path>
                                    </svg>
                                    <span class="text-gray-900">{{ $rejectedUser->phone ?? 'Not provided' }}</span>
                                </div>
                            </div>

                            {{-- Rejected Date & Time --}}
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500 mb-2">{{ __('admin.rejection_date') }}</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span
                                        class="text-gray-900">{{ \Carbon\Carbon::parse($rejectedUser->rejected_at)->format('d M Y') }}</span>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-500 mb-2">{{ __('admin.rejection_time') }}</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span
                                        class="text-gray-900">{{ \Carbon\Carbon::parse($rejectedUser->rejected_at)->format('h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ADDITIONAL INFO CARD (if you have more business details) --}}
                @if (isset($rejectedUser->business_name) || isset($rejectedUser->business_type))
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200"
                            style="background: linear-gradient(90deg, rgba(55,99,119,0.1), rgba(47,83,101,0.1));">
                            <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2" style="color: #376377;" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                                {{ __('admin.business_type') }}
                            </h4>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @if (isset($rejectedUser->business_name))
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-500 mb-2">{{ __('admin.blogs') }}</label>
                                        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                            {{ $rejectedUser->business_name }}</p>
                                    </div>
                                @endif
                                @if (isset($rejectedUser->business_type))
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-500 mb-2">{{ __('admin.blogs') }}</label>
                                        <p class="text-gray-900 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                            {{ $rejectedUser->business_type }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ACTIONS SECTION --}}
        <div class="flex flex-wrap gap-4 justify-end">
            {{-- Back Button --}}
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center px-5 py-2.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-300">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('admin.back_to_list') }}
            </a>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success message if exists
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
        });
    </script>
@endsection
