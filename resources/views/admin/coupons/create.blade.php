{{-- admin.coupons.create --}}
@extends('layouts.admin')

@section('content')
    <div class="container py-4">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('admin.coupons.index') }}" class="text-decoration-none me-3" style="color: #6c757d;">
                <i class="bi bi-arrow-left-circle" style="font-size: 1.5rem;"></i>
            </a>
            <div>
                <h2 class="mb-0"  style="
                font-family: 'Cormorant Garamond', serif;
                font-size: 2.1rem;
                font-weight: 700;
                color: var(--charcoal);
                letter-spacing: -0.5px;
                margin: 0 0 4px;
                line-height: 1;">Create Coupon</h2>
                <p class="text-muted mb-0 mt-1">Add a new promotional discount coupon</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
            <div class="card-body p-4">

                <form action="{{ route('admin.coupons.store') }}" method="POST">

                    @csrf

                    @include('admin.coupons.form')

                    <div class="mt-4 d-flex gap-3">
                        <button type="submit" class="btn px-4 py-2"
                            style="border-radius: 10px; font-weight: 500;background: #2a1a05;color:white;">
                            <i class="bi bi-plus-circle me-2"></i> Create Coupon
                        </button>
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary px-4 py-2"
                            style="border-radius: 10px; font-weight: 500;">
                            Cancel
                        </a>
                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection
