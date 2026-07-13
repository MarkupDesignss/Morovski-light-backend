@extends('layouts.admin')

@section('title', __('admin.edit_shipping_charge_title'))

@section('content')
    <div class="space-y-6">

        {{-- ERROR MESSAGES --}}
        @if ($errors->any())
            <div class="relative overflow-hidden rounded-lg p-3 shadow-lg"
                style="background: linear-gradient(90deg, #991b1b, #dc2626); color: white;">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold mb-1">{{ __('admin.fix_errors_title') }}</p>
                        <ul class="text-sm opacity-90 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()"
                        class="text-white/80 hover:text-white flex-shrink-0">
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
                    {{ __('admin.edit_shipping_charge_header') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('admin.edit_shipping_charge_subheader') }}</p>
            </div>
            <a href="{{ route('admin.shipping.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-all shadow-sm text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                {{ __('admin.back_to_charges_btn') }}
            </a>
        </div>

        {{-- EDIT FORM CARD --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="relative">
                <div class="absolute top-0 left-0 right-0 h-1"
                    style="background: linear-gradient(90deg, #0B1A20, #2a5f6e, #0B1A20);"></div>

                {{-- Card Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        <h3 class="font-semibold text-gray-800">{{ __('admin.edit_charge_prefix') }} #{{ $charge->id }}
                        </h3>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-6">
                    <form action="{{ route('admin.shipping.update', $charge->id) }}" method="POST" id="editForm">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Type --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.type_label') }}
                                    *</label>
                                <select name="type" id="type"
                                    class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-[#0B1A20]/20 focus:border-[#0B1A20] transition-all bg-white">
                                    <option value="flat" {{ $charge->type == 'flat' ? 'selected' : '' }}>
                                        {{ __('admin.type_flat_rate') }}</option>
                                    <option value="weight" {{ $charge->type == 'weight' ? 'selected' : '' }}>
                                        {{ __('admin.type_weight_based') }}</option>
                                    <option value="price" {{ $charge->type == 'price' ? 'selected' : '' }}>
                                        {{ __('admin.type_price_based') }}</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">{{ __('admin.type_hint') }}</p>
                            </div>

                            {{-- Charge Amount --}}
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.charge_amount_label') }}
                                    *</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">{{ __('admin.currency_symbol') }}</span>
                                    <input type="number" step="0.01" name="charge"
                                        value="{{ old('charge', $charge->charge) }}"
                                        class="w-full border border-gray-300 rounded-lg p-2.5 pl-8 text-sm focus:ring-2 focus:ring-[#0B1A20]/20 focus:border-[#0B1A20] transition-all bg-white @error('charge') border-red-500 @enderror"
                                        required placeholder="0.00">
                                </div>
                                @error('charge')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @else
                                    <p class="text-xs text-gray-500 mt-1">{{ __('admin.charge_amount_hint') }}</p>
                                @enderror
                            </div>

                            {{-- Min Value --}}
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.min_value_label') }}</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">{{ __('admin.currency_symbol') }}</span>
                                    <input type="number" step="0.01" name="min_value"
                                        value="{{ old('min_value', $charge->min_value) }}"
                                        class="w-full border border-gray-300 rounded-lg p-2.5 pl-8 text-sm focus:ring-2 focus:ring-[#0B1A20]/20 focus:border-[#0B1A20] transition-all bg-white @error('min_value') border-red-500 @enderror"
                                        placeholder="0.00">
                                </div>
                                @error('min_value')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @else
                                    <p class="text-xs text-gray-500 mt-1">{{ __('admin.min_value_hint') }}</p>
                                @enderror
                            </div>

                            {{-- Max Value --}}
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.max_value_label') }}</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">{{ __('admin.currency_symbol') }}</span>
                                    <input type="number" step="0.01" name="max_value"
                                        value="{{ old('max_value', $charge->max_value) }}"
                                        class="w-full border border-gray-300 rounded-lg p-2.5 pl-8 text-sm focus:ring-2 focus:ring-[#0B1A20]/20 focus:border-[#0B1A20] transition-all bg-white @error('max_value') border-red-500 @enderror"
                                        placeholder="0.00">
                                </div>
                                @error('max_value')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @else
                                    <p class="text-xs text-gray-500 mt-1">{{ __('admin.max_value_hint') }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="mt-6 flex gap-3 pt-2">
                            <button type="submit"
                                class="px-6 py-2.5 rounded-lg text-white text-sm font-medium transition-all transform hover:scale-[1.02] active:scale-[0.98] shadow-sm"
                                style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                        </path>
                                    </svg>
                                    {{ __('admin.update_charge_btn') }}
                                </div>
                            </button>

                            <a href="{{ route('admin.shipping.index') }}"
                                class="px-6 py-2.5 rounded-lg bg-gray-500 text-white text-sm font-medium hover:bg-gray-600 transition-all shadow-sm">
                                {{ __('admin.cancel_btn') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Current Values Summary Card (Optional) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <h3 class="font-semibold text-gray-800">{{ __('admin.current_values_title') }}</h3>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('admin.type_label') }}</p>
                        <p class="text-lg font-semibold text-gray-800 mt-1 capitalize">
                            @if ($charge->type == 'flat')
                                {{ __('admin.type_flat_rate_short') }}
                            @elseif($charge->type == 'weight')
                                {{ __('admin.type_weight_short') }}
                            @else
                                {{ __('admin.type_price_short') }}
                            @endif
                        </p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('admin.charge_label') }}</p>
                        <p class="text-lg font-semibold text-gray-800 mt-1">
                            {{ __('admin.currency_symbol') }}{{ number_format($charge->charge, 2) }}</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('admin.min_value_label') }}</p>
                        <p class="text-lg font-semibold text-gray-800 mt-1">
                            {{ $charge->min_value ? __('admin.currency_symbol') . number_format($charge->min_value, 2) : '—' }}
                        </p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('admin.max_value_label') }}</p>
                        <p class="text-lg font-semibold text-gray-800 mt-1">
                            {{ $charge->max_value ? __('admin.currency_symbol') . number_format($charge->max_value, 2) : '—' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Auto-dismiss error messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const errorMessage = document.querySelector('[style*="linear-gradient(90deg, #991b1b"]');
            if (errorMessage) {
                setTimeout(function() {
                    errorMessage.style.transition = 'opacity 0.5s ease';
                    errorMessage.style.opacity = '0';
                    setTimeout(function() {
                        errorMessage.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });

        // Form validation before submit
        document.getElementById('editForm')?.addEventListener('submit', function(e) {
            const charge = parseFloat(document.querySelector('input[name="charge"]').value);
            const minValue = document.querySelector('input[name="min_value"]').value;
            const maxValue = document.querySelector('input[name="max_value"]').value;
            const currencySymbol = '{{ __('admin.currency_symbol') }}';

            if (isNaN(charge) || charge <= 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: '{{ __('admin.invalid_charge_title') }}',
                    text: '{{ __('admin.invalid_charge_text') }}',
                    confirmButtonColor: '#0B1A20',
                    customClass: {
                        popup: 'rounded-xl'
                    }
                });
                return false;
            }

            if (minValue && maxValue && parseFloat(minValue) >= parseFloat(maxValue)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: '{{ __('admin.invalid_range_title') }}',
                    text: '{{ __('admin.invalid_range_text') }}',
                    confirmButtonColor: '#0B1A20',
                    customClass: {
                        popup: 'rounded-xl'
                    }
                });
                return false;
            }

            // Show confirmation before update
            e.preventDefault();

            const form = this;
            const chargeAmount = parseFloat(document.querySelector('input[name="charge"]').value);
            const type = document.querySelector('select[name="type"] option:checked').text;

            Swal.fire({
                title: '{{ __('admin.update_confirm_title') }}',
                html: `{{ __('admin.update_confirm_html_prefix') }}<br><br>
                       <strong>{{ __('admin.type_label') }}:</strong> ${type}<br>
                       <strong>{{ __('admin.charge_label') }}:</strong> ${currencySymbol}${chargeAmount.toFixed(2)}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0B1A20',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '{{ __('admin.update_confirm_yes') }}',
                cancelButtonText: '{{ __('admin.update_confirm_cancel') }}',
                customClass: {
                    popup: 'rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: '{{ __('admin.updated_title') }}',
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
