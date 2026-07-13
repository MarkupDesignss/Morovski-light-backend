@extends('layouts.admin')

@section('content')
<div class="container">
    <h4 class="mb-3" style="
                font-family: 'Cormorant Garamond', serif;
                font-size: 2.1rem;
                font-weight: 700;
                color: var(--charcoal);
                letter-spacing: -0.5px;
                margin: 0 0 4px;
                line-height: 1;"> Edit Contact Settings</h4>

    <form action="{{ route('admin.contact-settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Email --}}
        <div class="card mb-3">
            <div class="card-header  text-white"style="background-color: #2a1a05;color:white;">Email</div>
            <div class="card-body">
                <div class="mb-2">
                    <label>Title</label>
                    <input type="text" name="email_title" class="form-control" 
                           value="{{ old('email_title', $settings->email->title ?? '') }}">
                </div>
                <div class="mb-2">
                    <label>Description</label>
                    <textarea name="email_description" class="form-control" rows="3">{{ old('email_description', $settings->email->short_description ?? '') }}</textarea>
                </div>
            </div>
        </div>
        
        {{-- Call Us --}}
        <div class="card mb-3">
            <div class="card-header text-white" style="background-color: #2a1a05;color:white;">Call Us</div>
            <div class="card-body">
                <div class="mb-2">
                    <label>Title</label>
                    <input type="text" name="call_us_title" class="form-control" 
                           value="{{ old('call_us_title', $settings->call_us->title ?? '') }}">
                </div>
                <div class="mb-2">
                    <label>Description</label>
                    <textarea name="call_us_description" class="form-control" rows="3">{{ old('call_us_description', $settings->call_us->short_description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Business Hours --}}
        <div class="card mb-3">
            <div class="card-header text-white" style="background-color: #2a1a05;color:white;">Business Hours</div>
            <div class="card-body">
                <div class="mb-2">
                    <label>Title</label>
                    <input type="text" name="business_hours_title" class="form-control" 
                           value="{{ old('business_hours_title', $settings->business_hours->title ?? '') }}">
                </div>
                <div class="mb-2">
                    <label>Description</label>
                    <textarea name="business_hours_description" class="form-control" rows="3">{{ old('business_hours_description', $settings->business_hours->short_description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Visit Us --}}
        <div class="card mb-3">
            <div class="card-header text-white"style="background-color: #2a1a05;color:white;">Visit Us</div>
            <div class="card-body">
                <div class="mb-2">
                    <label>Title</label>
                    <input type="text" name="visit_us_title" class="form-control" 
                           value="{{ old('visit_us_title', $settings->visit_us->title ?? '') }}">
                </div>
                <div class="mb-2">
                    <label>Description</label>
                    <textarea name="visit_us_description" class="form-control" rows="3">{{ old('visit_us_description', $settings->visit_us->short_description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn " style="background-color: #2a1a05;color:white;">Update</button>
        <a href="{{ route('admin.contact-settings.index') }}" class="btn " style="background-color: #2a1a05;color:white;">Back</a>
    </form>
</div>
@endsection