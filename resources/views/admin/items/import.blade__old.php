@extends('layouts.admin')

@section('title', 'Import Items from CSV')
<style>
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.7rem 1.2rem;
        background: transparent;
        border: 1.5px solid #d4c4b4;
        border-radius: 40px;
        color: #5c4b3a;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }
    
    @media (max-width: 768px) {
        .btn-back {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            white-space: normal;
            word-break: keep-all;
        }
    }
</style>

@section('content')
<div class="space-y-8 min-h-screen p-4 md:p-6" style="padding: 1rem;">
    @php
        $isMobile = true; // This will be handled by CSS, but kept for reference
    @endphp

    {{-- HEADER - FULLY RESPONSIVE --}}
    <div style="display: flex; flex-direction: column; gap: 1rem; width: 100%;">
        <div style="flex: 1;">
            <h2 style="font-size: clamp(1.5rem, 6vw, 1.875rem); font-weight: 700; color: #2a1a05; font-family: Georgia, serif; margin: 0; line-height: 1.2;">
                Import Items from CSV
            </h2>
            <p style="margin-top: 0.5rem; font-size: 0.813rem; color: #57534e;">Bulk upload products using CSV file + local images</p>
        </div>
      
        {{-- BUTTONS GROUP - STACK ON MOBILE, ROW ON DESKTOP --}}
        <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 100%;">
            {{-- First row on desktop, stacked on mobile --}}
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; width: 100%;">
                {{-- DOWNLOAD TEMPLATE BUTTON --}}
                <a href="{{ asset('csv/product_import_template.csv') }}" 
                    download
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1rem; border-radius: 0.5rem; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; text-decoration: none; font-weight: 500; font-size: 0.875rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease; flex: 1 1 auto; min-width: 140px;">
                    <svg style="width: 1.125rem; height: 1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download CSV
                </a>
                
                {{-- UPLOAD IMAGES FIRST BUTTON --}}
                <a href="{{ route('admin.items.import.images') }}"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1rem; border-radius: 0.5rem; background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); color: white; text-decoration: none; font-weight: 500; font-size: 0.875rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease; flex: 1 1 auto; min-width: 140px;">
                    <svg style="width: 1.125rem; height: 1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Upload Images
                </a>
            </div>
            
            {{-- Second row for Back button --}}
            <div style="display: flex; width: 100%;">
                <a href="{{ route('admin.items.index') }}" class="btn-back" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1rem; background: transparent; border: 1.5px solid #d4c4b4; border-radius: 40px; color: #5c4b3a; font-weight: 600; text-decoration: none; transition: all 0.2s; font-size: 0.875rem; width: 100%;">
                    ← Back to Items
                </a>
            </div>
        </div>
    </div>

    {{-- INFO BOX - FULLY RESPONSIVE --}}
    <div style="border-radius: 0.75rem; background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 1rem; margin-top: 0.5rem;">
        <div style="display: flex;">
            <div style="flex-shrink: 0;">
                <svg style="height: 1.25rem; width: 1.25rem; color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div style="margin-left: 0.75rem; flex: 1;">
                <h3 style="font-size: 0.875rem; font-weight: 500; color: #92400e; margin-bottom: 0.5rem;">CSV Format Instructions</h3>
                <div style="font-size: 0.813rem; color: #b45309;">
                    <ul style="list-style: disc; padding-left: 1.25rem; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li>First upload all images using <strong>"Upload Images First"</strong> button.</li>
                        <li>CSV must have columns: <code style="background: #ffedd5; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">name, category, type, model, price, sku, warehouses, description, specifications, images</code></li>
                        <li><strong>warehouses</strong> format: <code style="background: #ffedd5; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">WH1:20,WH2:10</code> (codes must match your warehouse codes)</li>
                        <li><strong>specifications</strong> format: <code style="background: #ffedd5; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">Brand:Logitech;Color:Black</code></li>
                        <li><strong>images</strong> column: comma separated filenames (e.g., <code style="background: #ffedd5; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">mouse1.jpg,mouse2.jpg</code>)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- IMPORT FORM - FULLY RESPONSIVE --}}
    <div style="background-color: white; border-radius: 1rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); border: 1px solid #f5f5f4; overflow: hidden;">
        <div style="padding: 0.75rem 1rem; border-bottom: 1px solid #f5f5f4; background: rgba(22, 12, 0, 0.02);">
            <h3 style="font-size: 1rem; font-weight: 600; display: flex; align-items: center; color: #160c00;">
                <svg style="width: 1.125rem; height: 1.125rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload CSV File
            </h3>
        </div>
        <form action="{{ route('admin.items.import') }}" method="POST" enctype="multipart/form-data" style="padding: 1rem;">
            @csrf
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; color: #160c00;">Choose CSV File</label>
                <div style="position: relative;">
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                        style="width: 100%; padding: 0.75rem 1rem; background-color: #fafaf9; border: 1px solid #e7e5e4; border-radius: 0.75rem; color: #1c1917; font-size: 0.875rem; cursor: pointer;">
                </div>
                <p style="font-size: 0.75rem; color: #78716c; margin-top: 0.25rem;">Max file size: 5MB</p>
                @error('csv_file')
                    <p style="margin-top: 0.25rem; font-size: 0.75rem; color: #e11d48;">{{ $message }}</p>
                @enderror
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.75rem; justify-content: flex-end;">
                <a href="{{ route('admin.items.index') }}" style="display: inline-block; text-align: center; padding: 0.6rem 1rem; border-radius: 0.75rem; color: #44403c; background-color: #f5f5f4; text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: all 0.2s;">Cancel</a>
                <button type="submit" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1rem; border-radius: 0.75rem; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%); color: white; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s ease; font-size: 0.875rem;">
                    <svg style="width: 1.125rem; height: 1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Start Import
                </button>
            </div>
        </form>
    </div>

    {{-- ERROR DISPLAY - FULLY RESPONSIVE --}}
    @if(session('errors') && count(session('errors')) > 0)
        <div style="border-radius: 0.75rem; background-color: #fff1f2; border-left: 4px solid #e11d48; padding: 1rem;">
            <div style="display: flex;">
                <div style="flex-shrink: 0;">
                    <svg style="height: 1.25rem; width: 1.25rem; color: #fb7185;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div style="margin-left: 0.75rem; flex: 1;">
                    <h3 style="font-size: 0.875rem; font-weight: 500; color: #9f1239;">Import Errors</h3>
                    <ul style="margin-top: 0.5rem; font-size: 0.813rem; color: #be123c; list-style: disc; padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.25rem;">
                        @foreach(session('errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Add responsive media queries inline --}}
<style>
    @media (min-width: 768px) {
        [style*="padding: 1rem"] {
            padding: 1.5rem;
        }
        
        .btn-back {
            width: auto !important;
        }
        
        .space-y-8 > div:first-child > div:last-child > div:first-child {
            flex-direction: row !important;
        }
    }
    
    @media (min-width: 640px) {
        form > div:last-child {
            flex-direction: row !important;
            justify-content: flex-end !important;
        }
        
        form > div:last-child a {
            width: auto !important;
        }
        
        .space-y-8 > div:first-child > div:last-child {
            flex-direction: row !important;
            flex-wrap: wrap !important;
        }
        
        .space-y-8 > div:first-child > div:last-child > div:first-child {
            flex: 2 !important;
        }
        
        .space-y-8 > div:first-child > div:last-child > div:last-child {
            flex: 1 !important;
        }
    }
    
    button, a {
        transition: all 0.2s ease;
    }
    
    button:active, .btn-back:active, a:active {
        transform: scale(0.98);
    }
    
    input[type="file"]:hover {
        background-color: #f5f5f4;
        border-color: #d6d3d1;
    }
    
    /* Improve touch targets on mobile */
    @media (max-width: 640px) {
        button, .btn-back, a[style*="padding"] {
        min-height: 44px;
        }
        
        input, select, textarea {
            font-size: 16px !important;
        }
    }
</style>
@endsection