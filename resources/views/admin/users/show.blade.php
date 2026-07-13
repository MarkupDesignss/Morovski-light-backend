@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
    <div class="space-y-8 min-h-screen">
        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div id="success-message" class="relative mb-4 overflow-hidden rounded-xl p-4 shadow-lg"
                style="background: linear-gradient(90deg, #160c00, #3a2819); color: white; border: 1px solid rgba(255,255,255,0.08);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="mr-3 rounded-full bg-amber-800/40 p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold">{{ session('success') }}</p>
                            <p class="text-sm text-white/80">Success</p>
                        </div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-white/80 hover:text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="absolute bottom-0 left-0 h-1 w-full" style="background: rgba(255,255,255,0.06)">
                    <div class="h-full bg-amber-400 progress-bar"></div>
                </div>
            </div>
        @endif

        {{-- HEADER WITH BACK BUTTON --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold"
                    style="

        color: #2a1a05;
        font-family: Georgia, serif;
        margin: 0; ">
                    {{ __('admin.user_details') }}
                </h2>
                <p class="mt-2 text-sm text-stone-600">{{ __('admin.view_user_info') }}</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-stone-700 bg-white border border-stone-200 hover:bg-stone-50 hover:border-stone-300 transition-all duration-300 shadow-sm hover:shadow">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('admin.back_to_list') }}
                </a>
            </div>
        </div>

        {{-- USER PROFILE CARD --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Basic Info Card --}}
            <div class="lg:col-span-1">
                <div class="rounded-2xl bg-white shadow-sm border border-stone-100 overflow-hidden">
                    <div class="h-24" style="background: linear-gradient(90deg, #3a2819, #160c00)"></div>
                    <div class="px-6 pb-6">
                        <div class="flex justify-center -mt-12">
                            <div style="background: linear-gradient(135deg, #3a2819, #160c00);"
                                class="h-24 w-24 rounded-full flex items-center justify-center text-white text-3xl font-bold border-4 border-white shadow-lg">
                                {{ strtoupper(substr($user->full_name, 0, 2)) }}
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <h3 class="text-xl font-bold text-stone-800">{{ $user->full_name }}</h3>
                            <p class="text-sm text-stone-500 mt-1">{{ $user->email }}</p>
                            <div class="mt-3">
                                @if ($user->account_type == 'business')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        {{ __('admin.business_account') }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-stone-100 text-stone-600 border border-stone-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                        {{ __('admin.personal_account') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detailed Info Card --}}
            <div class="lg:col-span-2">
                <div class="rounded-2xl bg-white shadow-sm border border-stone-100 p-6">
                    <h3 class="text-lg font-semibold text-stone-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        {{ __('admin.personal_information') }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-stone-500 mb-1">{{ __('admin.name') }}</label>
                            <p class="text-stone-800 bg-stone-50 p-3 rounded-lg border border-stone-200">
                                {{ $user->full_name }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-500 mb-1">{{ __('admin.email') }}</label>
                            <p class="text-stone-800 bg-stone-50 p-3 rounded-lg border border-stone-200">
                                {{ $user->email }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-500 mb-1">{{ __('admin.contact') }}</label>
                            <p class="text-stone-800 bg-stone-50 p-3 rounded-lg border border-stone-200">
                                {{ $user->phone ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-500 mb-1">{{ __('admin.Country') }}</label>
                            <p class="text-stone-800 bg-stone-50 p-3 rounded-lg border border-stone-200">
                                {{ $user->country ?? 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BUSINESS PROFILE SECTION --}}
        @if ($user->account_type == 'business' && $user->businessProfile)
            <div class="rounded-2xl bg-white shadow-sm border border-stone-100 overflow-hidden">
                <div class="px-4 py-3" style="background: linear-gradient(90deg, #3a2819, #160c00);">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                        {{ __('admin.business_profile') }}
                    </h3>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-stone-500 mb-1">
                                {{ __('admin.company_name') }}</label>
                            <p class="text-stone-800 bg-stone-50 p-3 rounded-lg border border-stone-200">
                                {{ $user->businessProfile->company_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-500 mb-1">
                                {{ __('admin.gst_number') }}</label>
                            <p class="text-stone-800 bg-stone-50 p-3 rounded-lg border border-stone-200">
                                {{ $user->businessProfile->gst_number }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-stone-500 mb-1">
                                {{ __('admin.business_address') }}</label>
                            <p class="text-stone-800 bg-stone-50 p-3 rounded-lg border border-stone-200">
                                {{ $user->businessProfile->business_address }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-500 mb-1">
                                {{ __('admin.document') }}</label>
                            <a href="{{ asset('storage/' . $user->businessProfile->document_path) }}" target="_blank"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white transition-all duration-300 shadow-md hover:shadow-lg"
                                style="background: linear-gradient(90deg, #3a2819, #160c00);">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                {{ __('admin.view_document') }}
                            </a>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-500 mb-1">{{ __('admin.Status') }}</label>
                            <div>
                                @if ($user->business_status == 'approved')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-2 h-2 mr-1 bg-emerald-500 rounded-full"></span>
                                        {{ __('admin.approved') }}
                                    </span>
                                @elseif($user->business_status == 'rejected')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-2 h-2 mr-1 bg-rose-500 rounded-full"></span>
                                        {{ __('admin.rejected') }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-2 h-2 mr-1 bg-amber-500 rounded-full"></span>
                                        {{ __('admin.pending') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

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
        });
    </script>
@endsection
