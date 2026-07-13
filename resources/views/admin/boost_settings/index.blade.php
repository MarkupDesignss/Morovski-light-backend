@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert"
                style="background: linear-gradient(135deg, #0B1A20, #162E38); border: none; color: white;">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Main Table Card -->
        <div class="card border-0 shadow-lg rounded-3">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold" style="color: #0B1A20;">
                        <i class="fas fa-crown me-2" style="color: #162E38;"></i>{{ __('admin.boost_plans_title') }}
                    </h5>
                    <div>
                        <a href="{{ route('admin.boost-settings.create') }}"
                            class="btn text-white shadow-md hover:shadow-lg transition-all duration-300"
                            style="background: linear-gradient(135deg, #0B1A20, #162E38); border: none;">
                            <i class="fas fa-plus-circle me-2"></i>{{ __('admin.add_new_boost_btn') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                @if ($settings->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-inbox fa-4x" style="color: #162E38; opacity: 0.25;"></i>
                        </div>
                        <h5 style="color: #4B5565;">{{ __('admin.no_boost_settings_found') }}</h5>
                        <p style="color: #6B7280;">{{ __('admin.no_boost_settings_hint') }}</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="boostTable">
                            <thead style="background: linear-gradient(135deg, #f8f9fa, #f1f3f5);">
                                <tr>
                                    <th class="border-0 rounded-start py-3" style="color: #0B1A20;">
                                        {{ __('admin.table_id') }}</th>
                                    <th class="border-0 py-3" style="color: #0B1A20;">
                                        <i class="fas fa-calendar-day me-1"></i>{{ __('admin.table_duration') }}
                                    </th>
                                    <th class="border-0 py-3" style="color: #0B1A20;">
                                        {{ __('admin.table_price') }}
                                    </th>
                                    <th class="border-0 py-3" style="color: #0B1A20;">
                                        <i class="fas fa-chart-simple me-1"></i>{{ __('admin.table_daily_rate') }}
                                    </th>
                                    <th class="border-0 py-3" style="color: #0B1A20;">
                                        <i class="fas fa-bolt me-1"></i>{{ __('admin.table_status') }}
                                    </th>
                                    <th class="border-0 rounded-end py-3 text-center" style="color: #0B1A20;">
                                        {{ __('admin.table_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($settings as $item)
                                    <tr class="border-bottom">
                                        <td class="fw-bold" style="color: #162E38;">#{{ $item->id }}</td>
                                        <td>
                                            <span class="fw-bold" style="color: #0B1A20;">{{ $item->days }}</span>
                                            <span
                                                style="color: #6B7280;">{{ $item->days > 1 ? __('admin.days_plural') : __('admin.day_singular') }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold fs-5"
                                                style="color: #162E38;">{{ __('admin.currency_symbol') }}{{ number_format($item->price, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge px-3 py-2"
                                                style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                                                {{ __('admin.currency_symbol') }}{{ number_format($item->price / $item->days, 2) }}/{{ __('admin.per_day') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($item->is_active)
                                                <span class="badge px-3 py-2"
                                                    style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                                    <i class="fas fa-circle small me-1" style="font-size: 8px;"></i>
                                                    {{ __('admin.active_status') }}
                                                </span>
                                            @else
                                                <span class="badge px-3 py-2"
                                                    style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                                                    <i class="fas fa-circle small me-1" style="font-size: 8px;"></i>
                                                    {{ __('admin.inactive_status') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('admin.boost-settings.edit', $item->id) }}"
                                                    class="btn btn-sm rounded-pill px-3 transition-all duration-300"
                                                    style="border: 1px solid #162E38; color: #162E38; background: transparent;">
                                                    <i class="fas fa-edit me-1"></i>{{ __('admin.edit_btn') }}
                                                </a>
                                                <button type="button"
                                                    class="btn btn-sm rounded-pill px-3 transition-all duration-300"
                                                    style="border: 1px solid #dc3545; color: #dc3545; background: transparent;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $item->id }}">
                                                    <i class="fas fa-trash-alt me-1"></i>{{ __('admin.delete_btn') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Modals -->
    @foreach ($settings as $item)
        <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0" style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                        <h5 class="modal-title fw-bold text-white">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ __('admin.delete_confirm_title') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <i class="fas fa-trash-alt fa-4x" style="color: #162E38; opacity: 0.5;"></i>
                        </div>
                        <h5 class="mb-3" style="color: #0B1A20;">{{ __('admin.delete_boost_plan_prefix') }}
                            #{{ $item->id }}?</h5>
                        <p style="color: #6B7280;">
                            {{ __('admin.delete_boost_plan_message_prefix') }} <strong
                                style="color: #162E38;">{{ $item->days }}-{{ __('admin.day_boost') }}</strong>?<br>
                            {{ __('admin.delete_confirm_warning') }}
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                            style="background: #f8f9fa; color: #6B7280;">{{ __('admin.cancel_btn') }}</button>
                        <form action="{{ route('admin.boost-settings.destroy', $item->id) }}" method="POST"
                            class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn px-4 text-white"
                                style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                                <i class="fas fa-trash-alt me-2"></i>{{ __('admin.delete_btn') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0B1A20, #162E38);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(22, 46, 56, 0.02);
            transition: all 0.3s ease;
            transform: scale(1.01);
        }

        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #0B1A20, #162E38) !important;
            border-color: transparent !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(22, 46, 56, 0.3);
        }

        .btn-outline-danger:hover {
            background: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #162E38;
            box-shadow: 0 0 0 0.2rem rgba(22, 46, 56, 0.25);
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02) !important;
        }

        /* Custom badge styles */
        .badge {
            font-weight: 500;
            border-radius: 8px;
        }

        /* Smooth table row animations */
        tbody tr {
            transition: all 0.2s ease;
        }
    </style>

    <script>
        // Search functionality
        document.getElementById('searchInput')?.addEventListener('keyup', function() {
            let searchText = this.value.toLowerCase();
            let tableRows = document.querySelectorAll('#boostTable tbody tr');

            tableRows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Add hover effect to buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });

            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>

@endsection
