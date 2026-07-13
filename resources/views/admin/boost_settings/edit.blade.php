@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 fw-bold" style="color: #0B1A20;">
                    <i class="fas fa-pen-alt me-2" style="color: #162E38;"></i>{{ __('admin.edit_boost_plan_title') }}
                </h4>
                <p class="mt-2 mb-0" style="color: #6B7280;">{{ __('admin.edit_boost_plan_subtitle') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.boost-settings.index') }}" class="btn"
                    style="border: 1px solid #162E38; color: #162E38; background: transparent;">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('admin.back_to_list_btn') }}
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert"
                style="border-left: 4px solid #dc3545;">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-times-circle fa-2x" style="color: #dc3545;"></i>
                    </div>
                    <div>
                        <strong class="d-block mb-1" style="color: #0B1A20;">{{ __('admin.fix_errors_title') }}</strong>
                        <ul class="mb-0" style="color: #6B7280;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <!-- Main Form Card -->
                <div class="card border-0 shadow-lg rounded-3">

                    <div class="card-body p-4">
                        <form action="{{ route('admin.boost-settings.update', $setting->id) }}" method="POST"
                            id="editForm">
                            @csrf
                            @method('PUT')

                            <div class="tab-content">
                                <!-- Basic Information Tab -->
                                <div class="tab-pane fade show active" id="basicInfo" role="tabpanel">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold" style="color: #0B1A20;">
                                            <i class="fas fa-calendar-day me-2" style="color: #162E38;"></i>
                                            {{ __('admin.duration_days_label') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"
                                                style="background: #f8f9fa;">
                                                <i class="fas fa-clock" style="color: #162E38;"></i>
                                            </span>
                                            <input type="number" name="days" id="days"
                                                class="form-control form-control-lg border-start-0"
                                                value="{{ $setting->days }}" required min="1"
                                                placeholder="{{ __('admin.duration_days_placeholder') }}"
                                                style="border-color: #e5e7eb;">
                                        </div>
                                        <small class="text-muted">{{ __('admin.duration_days_hint') }}</small>
                                        @error('days')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold" style="color: #0B1A20;">
                                            {{ __('admin.price_label') }} ({{ __('admin.currency_symbol') }})
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"
                                                style="background: #f8f9fa;">
                                                <i class="fas fa-tag" style="color: #162E38;"></i>
                                            </span>
                                            <input type="number" step="0.01" name="price" id="price"
                                                class="form-control form-control-lg border-start-0"
                                                value="{{ $setting->price }}" required
                                                placeholder="{{ __('admin.price_placeholder') }}"
                                                style="border-color: #e5e7eb;">
                                        </div>
                                        <small class="text-muted">{{ __('admin.price_hint') }}</small>
                                        @error('price')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold" style="color: #0B1A20;">
                                            <i class="fas fa-toggle-on me-2" style="color: #162E38;"></i>
                                            {{ __('admin.status_label') }}
                                        </label>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" name="is_active" class="form-check-input"
                                                id="statusSwitch" value="1" {{ $setting->is_active ? 'checked' : '' }}
                                                style="width: 3rem; height: 1.5rem;">
                                            <label class="form-check-label ms-2 fw-bold" id="statusLabel"
                                                style="color: {{ $setting->is_active ? '#162E38' : '#6B7280' }};">
                                                {{ $setting->is_active ? __('admin.active_label') : __('admin.inactive_label') }}
                                            </label>
                                        </div>
                                        <small class="text-muted">{{ __('admin.status_hint') }}</small>
                                    </div>
                                </div>

                                <!-- Preview Tab -->
                                <div class="tab-pane fade" id="preview" role="tabpanel">
                                    <div class="text-center mb-4">
                                        <div class="d-inline-block text-white px-4 py-2 rounded-pill"
                                            style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                                            <i class="fas fa-crown me-2"></i>{{ __('admin.plan_preview_title') }}
                                        </div>
                                    </div>

                                    <div class="card border-0 shadow-sm"
                                        style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                                        <div class="card-body text-white p-4 text-center">
                                            <div class="mb-3">
                                                <i class="fas fa-rocket fa-3x"></i>
                                            </div>
                                            <h3 class="fw-bold mb-2" id="previewDays">
                                                {{ $setting->days }}-{{ __('admin.day_boost') }}</h3>
                                            <div class="display-4 fw-bold mb-3" id="previewPrice">
                                                {{ __('admin.currency_symbol') }}{{ number_format($setting->price, 2) }}
                                            </div>
                                            <div class="mb-3">
                                                <span class="badge px-3 py-2"
                                                    style="background: rgba(255, 255, 255, 0.2); color: white;"
                                                    id="previewDaily">
                                                    {{ __('admin.currency_symbol') }}{{ number_format($setting->price / $setting->days, 2) }}/{{ __('admin.per_day') }}
                                                </span>
                                            </div>
                                            <div class="mt-3">
                                                <div class="d-flex justify-content-center gap-3">
                                                    <div>
                                                        <i class="fas fa-chart-line"></i>
                                                        <small class="d-block">{{ __('admin.more_views') }}</small>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-users"></i>
                                                        <small class="d-block">{{ __('admin.more_reach') }}</small>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-trophy"></i>
                                                        <small class="d-block">{{ __('admin.top_position') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 p-3 rounded-3" style="background: rgba(22, 46, 56, 0.05);">
                                        <div class="row text-center">
                                            <div class="col">
                                                <small class="d-block"
                                                    style="color: #6B7280;">{{ __('admin.total_views') }}</small>
                                                <strong class="fs-5" style="color: #162E38;">10x+</strong>
                                            </div>
                                            <div class="col">
                                                <small class="d-block"
                                                    style="color: #6B7280;">{{ __('admin.engagement') }}</small>
                                                <strong class="fs-5" style="color: #162E38;">↑ 50%</strong>
                                            </div>
                                            <div class="col">
                                                <small class="d-block"
                                                    style="color: #6B7280;">{{ __('admin.roi') }}</small>
                                                <strong class="fs-5" style="color: #162E38;">300%</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4" style="border-color: #e5e7eb;">

                            <div class="d-flex justify-content-end gap-3">
                                <a href="{{ route('admin.boost-settings.index') }}" class="btn btn-light btn-lg px-4"
                                    style="background: #f8f9fa; color: #6B7280; border: 1px solid #e5e7eb;">
                                    <i class="fas fa-times me-2"></i>{{ __('admin.cancel_btn') }}
                                </a>
                                <button type="submit"
                                    class="btn btn-lg px-5 text-white shadow-md hover:shadow-lg transition-all duration-300"
                                    style="background: linear-gradient(135deg, #0B1A20, #162E38); border: none;">
                                    <i class="fas fa-save me-2"></i>{{ __('admin.update_plan_btn') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .bg-gradient-primary {
                background: linear-gradient(135deg, #0B1A20, #162E38);
            }

            .form-control-lg,
            .input-group-text {
                border-radius: 0.5rem;
            }

            .input-group .form-control:focus {
                border-color: #162E38;
                box-shadow: 0 0 0 0.2rem rgba(22, 46, 56, 0.25);
            }

            .form-check-input:checked {
                background-color: #28a745;
                border-color: #28a745;
            }

            .form-check-input:focus {
                border-color: #162E38;
                box-shadow: 0 0 0 0.2rem rgba(22, 46, 56, 0.25);
            }

            .nav-tabs .nav-link {
                border: none;
                color: #6c757d;
                padding: 0.75rem 1.5rem;
                position: relative;
            }

            .nav-tabs .nav-link.active {
                color: #162E38;
                background: transparent;
            }

            .nav-tabs .nav-link.active::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 3px;
                background: linear-gradient(135deg, #0B1A20, #162E38);
                border-radius: 3px;
            }

            .btn {
                transition: all 0.3s ease;
            }

            .btn:hover {
                transform: translateY(-2px);
            }

            .btn-primary:hover {
                box-shadow: 0 8px 20px rgba(22, 46, 56, 0.4);
            }

            .alert-info {
                background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
                color: #0c4a6e;
            }

            .card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02) !important;
            }

            /* Smooth transitions */
            .transition-all {
                transition: all 0.3s ease;
            }

            /* Custom focus styles */
            input:focus,
            textarea:focus,
            select:focus {
                border-color: #162E38 !important;
                box-shadow: 0 0 0 0.2rem rgba(22, 46, 56, 0.25) !important;
            }

            /* Number input spinner styling */
            input[type="number"]::-webkit-inner-spin-button,
            input[type="number"]::-webkit-outer-spin-button {
                opacity: 0.5;
            }

            input[type="number"]:hover::-webkit-inner-spin-button,
            input[type="number"]:hover::-webkit-outer-spin-button {
                opacity: 1;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // Live preview updates
            const daysInput = document.getElementById('days');
            const priceInput = document.getElementById('price');
            const previewDays = document.getElementById('previewDays');
            const previewPrice = document.getElementById('previewPrice');
            const previewDaily = document.getElementById('previewDaily');
            const statusSwitch = document.getElementById('statusSwitch');
            const statusLabel = document.getElementById('statusLabel');
            const currencySymbol = '{{ __('admin.currency_symbol') }}';

            function updatePreview() {
                let days = parseInt(daysInput.value) || 0;
                let price = parseFloat(priceInput.value) || 0;

                if (days > 0 && price > 0) {
                    let dailyRate = price / days;
                    previewDays.innerHTML = `${days}-{{ __('admin.day_boost') }}`;
                    previewPrice.innerHTML = `${currencySymbol}${price.toFixed(2)}`;
                    previewDaily.innerHTML = `${currencySymbol}${dailyRate.toFixed(2)}/{{ __('admin.per_day') }}`;
                } else {
                    previewDays.innerHTML = days > 0 ? `${days}-{{ __('admin.day_boost') }}` :
                    '--{{ __('admin.day_boost') }}';
                    previewPrice.innerHTML = price > 0 ? `${currencySymbol}${price.toFixed(2)}` : `${currencySymbol}0.00`;
                    previewDaily.innerHTML = (days > 0 && price > 0) ?
                        `${currencySymbol}${(price / days).toFixed(2)}/{{ __('admin.per_day') }}` :
                        `${currencySymbol}0.00/{{ __('admin.per_day') }}`;
                }
            }

            if (daysInput) daysInput.addEventListener('input', updatePreview);
            if (priceInput) priceInput.addEventListener('input', updatePreview);

            if (statusSwitch) {
                statusSwitch.addEventListener('change', function() {
                    const isActive = this.checked;
                    statusLabel.textContent = isActive ? '{{ __('admin.active_label') }}' :
                        '{{ __('admin.inactive_label') }}';
                    statusLabel.style.color = isActive ? '#162E38' : '#6B7280';
                });
            }

            // Trigger initial update to ensure preview is correct
            updatePreview();
        </script>
    @endpush
@endsection
