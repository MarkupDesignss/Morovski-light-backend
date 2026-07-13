@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 style="
                font-family: 'Cormorant Garamond', serif;
                font-size: 2.1rem;
                font-weight: 700;
                color: var(--charcoal);
                letter-spacing: -0.5px;
                margin: 0 0 4px;
                line-height: 1;">Contact Page Settings</h4>
        <a href="{{ route('admin.contact-settings.edit') }}" class="btn" style="background-color: #2a1a05;color:white;">
            Edit Contact Settings
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        {{-- Email --}}
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header text-white"style="background-color: #2a1a05;">Email</div>
                <div class="card-body">
                    <h6>{{ $settings->email->title ?? 'N/A' }}</h6>
                    <p>{{ $settings->email->short_description ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        
        {{-- Call Us --}}
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header text-white"style="background-color: #2a1a05;">Call Us</div>
                <div class="card-body">
                    <h6>{{ $settings->call_us->title ?? 'N/A' }}</h6>
                    <p>{{ $settings->call_us->short_description ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- Business Hours --}}
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header  text-white"style="background-color: #2a1a05;">Business Hours</div>
                <div class="card-body">
                    <h6>{{ $settings->business_hours->title ?? 'N/A' }}</h6>
                    <p>{{ $settings->business_hours->short_description ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- Visit Us --}}
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header  text-white"style="background-color: #2a1a05;color:white;">Visit Us</div>
                <div class="card-body">
                    <h6>{{ $settings->visit_us->title ?? 'N/A' }}</h6>
                    <p>{{ $settings->visit_us->short_description ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection