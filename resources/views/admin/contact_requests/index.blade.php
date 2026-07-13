@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                    style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 style="color:#2f5365" class="text-2xl font-bold">{{ __('admin.contact_requests_title') }}</h2>
                    <p class="text-sm" style="color: #4B5565;">{{ __('admin.contact_requests_subtitle') }}</p>
                </div>
            </div>
        </div>


        <!-- Table Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border" style="border-color: #e5e7eb;">
            <div class="border-b px-6 py-4"
                style="border-color: #e5e7eb; background: linear-gradient(135deg, #f8f9fa, #ffffff);">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold flex items-center" style="color: #0B1A20;">
                        <svg class="w-5 h-5 mr-2" style="color: #162E38;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                        {{ __('admin.contact_requests_list_title') }}
                    </h3>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="{{ __('admin.search_placeholder') }}"
                            class="pl-10 pr-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 transition-all"
                            style="border-color: #e5e7eb;">
                        <svg class="absolute left-3 top-2.5 w-4 h-4" style="color: #9CA3AF;" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                style="color: #0B1A20;">Sr.No.</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                style="color: #0B1A20;">{{ __('admin.table_name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                style="color: #0B1A20;">{{ __('admin.table_email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                style="color: #0B1A20;">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                style="color: #0B1A20;">Topic</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                style="color: #0B1A20;">{{ __('admin.table_subject') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                style="color: #0B1A20;">{{ __('admin.table_date') }}</th>
                           
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                style="color: #0B1A20;">{{ __('admin.table_action') }}</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                        @forelse ($requests as $key => $req)
                            <tr class="hover:bg-gray-50 transition-all duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" style="color: #162E38;">
                                    {{ $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                            style="background: rgba(22, 46, 56, 0.1);">
                                            <span class="text-sm font-medium"
                                                style="color: #162E38;">{{ substr($req->first_name, 0, 1) }}{{ substr($req->last_name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium" style="color: #0B1A20;">{{ $req->first_name }}
                                                {{ $req->last_name }}</p>
                                           
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                                        {{ $req->email ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                                        {{ $req->phone ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                                        {{  $req->category_id }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm" style="color: #4B5565; max-width: 250px;">
                                    <div class="truncate">{{ $req->subject }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: #6B7280;">
                                    {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y') }}
                                    <span
                                        class="text-xs block">{{ \Carbon\Carbon::parse($req->created_at)->diffForHumans() }}</span>
                                </td>
                             
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('admin.contact_requests.show', $req->id) }}"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm transition-all duration-300 hover:scale-105"
                                        style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                        {{ __('admin.view_details_btn') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 mb-4" style="color: #162E38; opacity: 0.3;" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                            </path>
                                        </svg>
                                        <p class="text-sm" style="color: #9CA3AF;">{{ __('admin.no_requests_found') }}
                                        </p>
                                        <p class="text-xs mt-1" style="color: #6B7280;">
                                            {{ __('admin.no_requests_hint') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="border-t px-6 py-4" style="border-color: #e5e7eb; background: #f9fafb;">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            .transition-all {
                transition: all 0.3s ease;
            }

            /* Custom scrollbar */
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

            /* Search input focus */
            #searchInput:focus {
                border-color: #162E38 !important;
                box-shadow: 0 0 0 3px rgba(22, 46, 56, 0.1) !important;
                outline: none;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // Search functionality
            document.getElementById('searchInput')?.addEventListener('keyup', function() {
                let searchText = this.value.toLowerCase();
                let tableRows = document.querySelectorAll('#tableBody tr');

                tableRows.forEach(row => {
                    let text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchText) ? '' : 'none';
                });
            });
        </script>
    @endpush
@endsection
