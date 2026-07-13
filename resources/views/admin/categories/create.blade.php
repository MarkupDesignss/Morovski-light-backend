@extends('layouts.admin')

@section('title', 'Create Category')
<style>
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2rem;
        padding: 0.7rem 1.6rem;
        background: transparent;
        border: 1.5px solid #d4c4b4;
        border-radius: 40px;
        color: #5c4b3a;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
</style>

@section('content')
    <div class="space-y-8 min-h-screen p-6">

        {{-- HEADER --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold"style="

                                        color: #2a1a05;
                                        font-family: Georgia, serif;
                                        margin: 0; ">
                    {{ __('admin.create_new_category') }}</h2>
                <p class="mt-2 text-sm text-stone-600">{{ __('admin.add_category_desc') }}</p>
            </div>

            <a href="{{ route('admin.categories.index') }}" class="btn-back">← Back to Categories</a>
        </div>

        {{-- ERROR MESSAGES --}}
        @if ($errors->any())
            <div class="rounded-xl bg-rose-50 border-l-4 p-4" style="border-left-color: #e11d48;">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-rose-800">Please fix the following errors:</h3>
                        <ul class="mt-2 text-sm text-rose-700 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- CREATE FORM --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100" style="background: rgba(22, 12, 0, 0.02);">
                <h3 class="text-lg font-semibold flex items-center" style="color: #160c00;">
                    <svg class="w-5 h-5 mr-2" style="color: #3a2819;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    {{ __('admin.category_information') }}
                </h3>
            </div>

            <form action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" method="POST"
                class="p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Category Name (EN) --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2" style="color: #160c00;">Category Name<span
                                class="text-rose-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z">
                                    </path>
                                </svg>
                            </div>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:outline-none focus:ring-2 transition @error('name') border-rose-500 @enderror"
                                style="focus:ring-color: #3a2819;">
                        </div>
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                    
                        <label class="block text-sm font-medium mb-2" style="color: #160c00;">
                            Category Image
                        </label>
                    
                        <input
                            type="file"
                            name="image"
                            id="imageInput"
                            accept="image/*"
                            value="{{ old('image') }}"
                            class="w-full px-4 py-2 bg-stone-50 border border-stone-200 rounded-xl"
                        >
                    
                        {{-- Preview --}}
                        <div class="mt-3">
                    
                            <img
                                id="previewImage"
                                src="#"
                                alt="Preview"
                                class="hidden w-32 h-32 object-cover rounded-xl border border-stone-200"
                            >
                    
                        </div>
                    
                        @error('image')
                            <p class="mt-1 text-xs text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    
                    </div>

                    {{-- Category Name (DE) --}}
                    {{-- <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2"
                                style="color: #160c00;">{{ __('admin.category_name_de') }}<span
                                    class="text-rose-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129">
                                        </path>
                                    </svg>
                                </div>
                                <input type="text" name="name_de" value="{{ old('name_de') }}" required
                                    class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:outline-none focus:ring-2 transition @error('name_de') border-rose-500 @enderror"
                                    style="focus:ring-color: #3a2819;">
                            </div>
                            @error('name_de')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div> --}}

                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-sm font-medium mb-2"
                            style="color: #160c00;">{{ __('admin.sort_order') }}</label>
                        <div class="relative"> 
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </div>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"  min="0"
                                class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:outline-none focus:ring-2 transition"
                                style="focus:ring-color: #3a2819;">
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-2"
                            style="color: #160c00;">{{ __('admin.Status') }}</label>
                        <div class="flex items-center space-x-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="is_active" value="1" class="form-radio h-4 w-4"
                                    style="color: #3a2819;" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-stone-700">{{ __('admin.active') }}</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="is_active" value="0" class="form-radio h-4 w-4"
                                    style="color: #3a2819;" {{ old('is_active') == '0' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-stone-700">{{ __('admin.inactive') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="mt-8 flex items-center space-x-4">
                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 rounded-xl text-white font-semibold transition-all duration-300 transform hover:scale-[1.02] shadow-md hover:shadow-lg"
                        style="

        color: #2a1a05;
        font-family: Georgia, serif;
        margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                            </path>
                        </svg>
                        Create
                    </button>

                    <a href="{{ route('admin.categories.index') }}"
                        class="inline-flex items-center px-3 py-2 rounded-xl text-stone-700 bg-stone-100 hover:bg-stone-200 transition-all duration-300">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        {{ __('admin.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.getElementById('imageInput').addEventListener('change', function (e) {

        const file = e.target.files[0];

        if (file) {
            console.log("hello");
            const reader = new FileReader();

            reader.onload = function (event) {

                const preview = document.getElementById('previewImage');

                preview.src = event.target.result;

                preview.classList.remove('hidden');
            };

            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
