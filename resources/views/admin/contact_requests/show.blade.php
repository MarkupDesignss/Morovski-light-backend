@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                    style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 style="color:#2f5365" class="text-2xl font-bold">{{ __('admin.contact_request_details_title') }}
                    </h2>
                    <p class="text-sm" style="color: #4B5565;">{{ __('admin.contact_request_details_subtitle') }}</p>
                </div>
            </div>
            <div>
                <a href="{{ route('admin.contact_requests.index') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg transition-all duration-300"
                    style="border: 1px solid #162E38; color: #162E38; background: transparent;">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('admin.back_to_list_btn') }}
                </a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Contact Info -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Customer Information Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border" style="border-color: #e5e7eb;">
                    <div class="border-b px-6 py-4"
                        style="border-color: #e5e7eb; background: linear-gradient(135deg, #f8f9fa, #ffffff);">
                        <h3 class="text-lg font-semibold flex items-center" style="color: #0B1A20;">
                            <svg class="w-5 h-5 mr-2" style="color: #162E38;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ __('admin.customer_info_title') }}
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold uppercase mb-1"
                                    style="color: #9CA3AF;">{{ __('admin.full_name_label') }}</label>
                                <p class="text-lg font-medium" style="color: #0B1A20;">{{ $request->first_name }}
                                    {{ $request->last_name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-semibold uppercase mb-1"
                                    style="color: #9CA3AF;">{{ __('admin.email_label') }}</label>
                                <p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                        style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                                        {{ $request->email ?? '_' }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase mb-1"
                                    style="color: #9CA3AF;">Topic</label>
                                <p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                        style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                                        {{ $request->category->name ?? __('admin.category_prefix') . $request->category_id }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase mb-1"
                                    style="color: #9CA3AF;">{{ __('admin.submitted_date_label') }}</label>
                                <p class="text-lg font-medium" style="color: #0B1A20;">
                                    {{ \Carbon\Carbon::parse($request->created_at)->format('d M Y, h:i A') }}</p>
                                <p class="text-xs" style="color: #6B7280;">
                                    {{ \Carbon\Carbon::parse($request->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments Section -->
               

                <!-- Message Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border" style="border-color: #e5e7eb;">
                    <div class="border-b px-6 py-4"
                        style="border-color: #e5e7eb; background: linear-gradient(135deg, #f8f9fa, #ffffff);">
                        <h3 class="text-lg font-semibold flex items-center" style="color: #0B1A20;">
                            <svg class="w-5 h-5 mr-2" style="color: #162E38;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                                </path>
                            </svg>
                            {{ __('admin.message_title') }}
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="block text-xs font-semibold uppercase mb-2"
                                style="color: #9CA3AF;">{{ __('admin.subject_label') }}</label>
                            <p class="text-base font-medium" style="color: #0B1A20;">{{ $request->subject }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase mb-2"
                                style="color: #9CA3AF;">{{ __('admin.message_content_label') }}</label>
                            <div class="p-4 rounded-lg" style="background: rgba(22, 46, 56, 0.03);">
                                <p class="text-sm leading-relaxed" style="color: #4B5565;">{{ $request->message }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column - Attachments & Actions -->
        </div>
    </div>

    @push('styles')
        <style>
            .transition-all {
                transition: all 0.3s ease;
            }

            @media print {
                .container {
                    padding: 0;
                    margin: 0;
                }

                .btn,
                a:not(.no-print) {
                    display: none !important;
                }
            }
        </style>
    @endpush
@endsection
