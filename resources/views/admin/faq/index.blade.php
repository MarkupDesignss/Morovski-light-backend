@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h2 style="color:#2f5365" class="text-3xl font-bold">{{ __('admin.faqs_title') }}</h2>
                <p class="mt-2 text-sm" style="color: #4B5565;">{{ __('admin.faqs_subtitle') }}</p>
            </div>
            <!--<div>-->
            <!--    <a href="{{ route('admin.faq.create') }}"-->
            <!--        class="inline-flex items-center px-4 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"-->
            <!--        style="background: linear-gradient(135deg, #0B1A20, #162E38);">-->
            <!--        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
            <!--            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>-->
            <!--        </svg>-->
            <!--        Add FAQs-->
            <!--    </a>-->
            <!--</div>-->
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 rounded-lg" style="background: rgba(40, 167, 69, 0.1); border-left: 4px solid #28a745;">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" style="color: #28a745;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span style="color: #28a745;">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($faqs->count())
            <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium  tracking-wider"
                                style="color: #0B1A20;">Sr.No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                style="color: #0B1A20;">{{ __('admin.table_question') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                style="color: #0B1A20;">{{ __('admin.table_answer') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                style="color: #0B1A20;">{{ __('admin.table_status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($faqs as $faq)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" style="color: #162E38;">
                                    {{ $loop->iteration }}</td>
                                <td class="px-6 py-4 text-sm" style="color: #4B5565;">{{ $faq->question }}</td>
                                <td class="px-6 py-4 text-sm" style="color: #6B7280;">{{ Str::limit($faq->answer, 80) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($faq->is_active)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1" style="background: #28a745;"></span>
                                            {{ __('admin.active_status') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                            <span class="w-1.5 h-1.5 rounded-full mr-1" style="background: #dc3545;"></span>
                                            {{ __('admin.inactive_status') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <a href="{{ route('admin.faq.edit') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg transition-all duration-300 hover:scale-[1.02]"
                    style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    {{ __('admin.edit_faqs_btn') }}
                </a>
            </div>
        @endif
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
        </style>
    @endpush
@endsection
