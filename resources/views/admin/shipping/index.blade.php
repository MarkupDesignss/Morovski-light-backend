@extends('layouts.admin')

@section('title', __('admin.shipping_charges_title'))

@section('content')
    <div class="space-y-6">

        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div id="success-message" class="relative overflow-hidden rounded-lg p-3 shadow-lg"
                style="background: linear-gradient(90deg, #0B1A20, #162E38); color: white;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-white/80 hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- HEADER --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold tracking-tight" style="color:#1a3a47">
                    {{ __('admin.shipping_charges_header') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('admin.shipping_charges_subheader') }}</p>
            </div>
            {{-- <div class="flex gap-2">
                <button onclick="document.getElementById('addForm').scrollIntoView({behavior: 'smooth'})"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-medium transition-all transform hover:scale-[1.02] active:scale-[0.98] shadow-sm"
                    style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Charge
                </button>
            </div> --}}
        </div>

        {{-- STATS CARDS (commented out) --}}

        {{-- CHARGES TABLE CARD --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="relative">
                <div class="absolute top-0 left-0 right-0 h-1"
                    style="background: linear-gradient(90deg, #0B1A20, #2a5f6e, #0B1A20);"></div>

                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M6 14h6m-6 4h12M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z">
                                </path>
                            </svg>
                            <h3 class="font-semibold text-gray-800">{{ __('admin.shipping_charges_list_title') }}</h3>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ __('admin.total_records_label') }}: <span
                                class="font-semibold text-gray-700">{{ $total ?? $charges->count() }}</span>
                            {{ __('admin.records_count') }}
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('admin.table_serial') }}</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('admin.table_type') }}</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('admin.table_charge') }}</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('admin.table_min_value') }}</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('admin.table_max_value') }}</th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    {{ __('admin.table_actions') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($charges as $key => $charge)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ $charges->firstItem() ? $charges->firstItem() + $key : $key + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                                            @if ($charge->type == 'flat') bg-blue-100 text-blue-700
                                            @elseif($charge->type == 'weight') bg-green-100 text-green-700
                                            @else bg-purple-100 text-purple-700 @endif">
                                            @if ($charge->type == 'flat')
                                                {{ __('admin.type_flat') }}
                                            @elseif($charge->type == 'weight')
                                                {{ __('admin.type_weight') }}
                                            @else
                                                {{ __('admin.type_price') }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="font-semibold text-gray-800">€{{ number_format($charge->charge, 2) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $charge->min_value ? '€' . number_format($charge->min_value, 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $charge->max_value ? '€' . number_format($charge->max_value, 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.shipping.edit', $charge->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                                {{ __('admin.edit_btn') }}
                                            </a>

                                            <form action="{{ route('admin.shipping.destroy', $charge->id) }}"
                                                method="POST" class="delete-form" data-id="{{ $charge->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="delete-btn inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-all"
                                                    data-id="{{ $charge->id }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                    {{ __('admin.delete_btn') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <div
                                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                                </path>
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 text-base font-medium">{{ __('admin.no_charges_found') }}
                                        </p>
                                        <p class="text-sm text-gray-400 mt-1">{{ __('admin.no_charges_hint') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                @if (method_exists($charges, 'hasPages') && $charges->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                        {{ $charges->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Auto-dismiss success message
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

        // Delete confirmation with SweetAlert
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const form = this.closest('.delete-form');
                const id = this.dataset.id;

                Swal.fire({
                    title: '{{ __('admin.delete_confirm_title') }}',
                    text: '{{ __('admin.delete_confirm_text') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '{{ __('admin.delete_confirm_yes') }}',
                    cancelButtonText: '{{ __('admin.delete_confirm_cancel') }}',
                    customClass: {
                        popup: 'rounded-xl'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: '{{ __('admin.success_title') }}',
                text: '{{ session('success') }}',
                timer: 2000,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-xl'
                },
                heightAuto: false
            });
        @endif
    </script>
@endsection
