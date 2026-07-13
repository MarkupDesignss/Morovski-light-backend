@extends('layouts.admin')

@section('content')

<div class="container py-4">

    {{-- Header Section --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="mb-0" style="
                font-family: 'Cormorant Garamond', serif;
                font-size: 2.1rem;
                font-weight: 700;
                color: var(--charcoal);
                letter-spacing: -0.5px;
                margin: 0 0 4px;
                line-height: 1;">Coupons</h2>
            <p class="text-muted mb-0 mt-1">Manage your promotional discount coupons</p>
        </div>

        <a href="{{ route('admin.coupons.create') }}" class="btn  px-4 py-2" style="background-color: #2a1a05;color:white;border-radius: 10px; font-weight: 500;">
            <i class="bi bi-plus-circle me-2"></i> Add Coupon
        </a>
    </div>

    {{-- Search Form --}}
    <div class="mb-4">
        <form method="GET" class="position-relative">
            <i class="bi bi-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #adb5bd;"></i>
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Search coupon code..."
                   value="{{ request('search') }}"
                   style="padding-left: 45px; border-radius: 12px; border: 1px solid #e9ecef; height: 48px;">
        </form>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 12px; border-left: 4px solid #198754;">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Coupons Table Card --}}
    <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
        <div class="table-responsive">

            <table class="table align-middle mb-0" style="background: #ffffff;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 1rem; font-weight: 600; color: #495057;">Sr. No.</th>
                        <th style="padding: 1rem; font-weight: 600; color: #495057;">Code</th>
                        <th style="padding: 1rem; font-weight: 600; color: #495057;">Type</th>
                        <th style="padding: 1rem; font-weight: 600; color: #495057;">Value</th>
                        <th style="padding: 1rem; font-weight: 600; color: #495057;">User Type</th>
                        <th style="padding: 1rem; font-weight: 600; color: #495057;">Usage</th>
                        <th style="padding: 1rem; font-weight: 600; color: #495057;">Status</th>
                        <th style="padding: 1rem; font-weight: 600; color: #495057;" width="180">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($coupons as $coupon)
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 1rem; color: #6c757d;">{{ $loop->iteration }}</td>

                            <td style="padding: 1rem;">
                                <span style="background: #e8f0fe; color: #0d6efd; padding: 0.4rem 0.8rem; border-radius: 8px; font-family: monospace; font-weight: 600; font-size: 0.875rem;">
                                    {{ $coupon->code }}
                                </span>
                            </td>

                            <td style="padding: 1rem;">
                                @if($coupon->type == 'percentage')
                                    <span style="background: #fff3e0; color: #fd7e14; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                        {{ ucfirst($coupon->type) }}
                                    </span>
                                @else
                                    <span style="background: #e0f7e8; color: #198754; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                        {{ ucfirst($coupon->type) }}
                                    </span>
                                @endif
                            </td>

                            <td style="padding: 1rem; font-weight: 600; color: #2c3e50;">
                                @if($coupon->type == 'percentage')
                                    {{ $coupon->value }}%
                                @else
                                    ₹{{ number_format($coupon->value, 2) }}
                                @endif
                            </td>

                            <td style="padding: 1rem;">
                                <span style="color: #6c757d; background: #f8f9fa; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem;">
                                    {{ $coupon->user_type ?? 'All Users' }}
                                </span>
                            </td>

                            <td style="padding: 1rem;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 70px; height: 6px; border-radius: 10px;">
                                        @php
                                            $usagePercent = $coupon->usage_limit ? min(100, ($coupon->used_count / $coupon->usage_limit) * 100) : 0;
                                        @endphp
                                        <div class="progress-bar" role="progressbar"
                                             style="width: {{ $usagePercent }}%; background: #0d6efd; border-radius: 10px;"
                                             aria-valuenow="{{ $usagePercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="small" style="color: #495057;">
                                        {{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}
                                    </span>
                                </div>
                            </td>

                            <td style="padding: 1rem;">
                                @if($coupon->status)
                                    <span class="badge" style="background: #198754; padding: 0.4rem 0.8rem; border-radius: 20px; font-weight: 500;">
                                        <i class="bi bi-check-circle-fill me-1" style="font-size: 0.7rem;"></i> Active
                                    </span>
                                @else
                                    <span class="badge" style="background: #dc3545; padding: 0.4rem 0.8rem; border-radius: 20px; font-weight: 500;">
                                        <i class="bi bi-x-circle-fill me-1" style="font-size: 0.7rem;"></i> Inactive
                                    </span>
                                @endif
                            </td>

                            <td style="padding: 1rem;">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}"
                                       class="btn btn-sm"
                                       style="background-color: #2a1a05;color:white; border-radius: 8px; padding: 0.35rem 0.9rem; font-weight: 500;">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>

                                    <form action="{{ route('admin.coupons.destroy', $coupon->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Delete coupon? This action cannot be undone.')"
                                                class="btn btn-sm"
                                                style="background: #dc3545; color: #fff; border-radius: 8px; padding: 0.35rem 0.9rem; font-weight: 500;">
                                            <i class="bi bi-trash3 me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5" style="color: #6c757d;">
                                <i class="bi bi-ticket-perforated" style="font-size: 3rem; display: block; margin-bottom: 1rem; color: #dee2e6;"></i>
                                No coupons found
                                <br>
                                <small class="text-muted">Click "Add Coupon" to create your first discount coupon.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4 d-flex justify-content-center">
        {{ $coupons->links() }}
    </div>

</div>

@endsection
