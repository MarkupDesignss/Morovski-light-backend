@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 py-4 sm:py-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold" style="color: #2a1a05; font-family: Georgia, serif; margin: 0;">
                    {{ __('admin.edit_header_title') }}
                </h2>
                <p class="mt-1 sm:mt-2 text-xs sm:text-sm" style="color: #4B5565;">{{ __('admin.edit_header_subtitle') }}</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('admin.header.index') }}"
                    class="inline-flex items-center px-3 sm:px-4 py-2 rounded-lg transition-all duration-300 text-sm sm:text-base"
                    style="border: 1px solid #162E38; color: #162E38; background: transparent;">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('admin.back_to_settings_btn') }}
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-4 sm:p-6">
                <form action="{{ route('admin.header.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- LOGO SECTION -->
                    <div class="mb-8">
                        <h3 class="text-base sm:text-lg font-semibold mb-4 flex items-center" style="color: #0B1A20;">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0" style="color: #162E38;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            {{ __('admin.logo_section_title') }}
                        </h3>

                        <div class="border-2 border-dashed rounded-lg p-4 sm:p-6 text-center transition-all"
                            style="border-color: #e5e7eb;" ondrop="dropHandler(event)" ondragover="dragOverHandler(event)">
                            <input type="file" name="logo" id="logoInput" class="hidden" accept="image/*">
                            <label for="logoInput" class="cursor-pointer block">
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 sm:mb-3" style="color: #162E38; opacity: 0.5;" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <p class="text-xs sm:text-sm" style="color: #6B7280;">{{ __('admin.upload_logo_text') }}</p>
                                <p class="text-xs mt-1" style="color: #9CA3AF;">{{ __('admin.upload_logo_hint') }}</p>
                            </label>
                        </div>

                        <div id="logoPreview" class="mt-4">
                            @if ($logo && $logo->logo)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 p-3 sm:p-4 rounded-lg"
                                    style="background: rgba(22, 46, 56, 0.05);">
                                    <img src="{{ asset('storage/' . $logo->logo) }}" id="currentLogo"
                                        class="h-12 sm:h-16 w-auto object-contain self-start sm:self-auto">
                                    <div>
                                        <p class="text-sm font-medium" style="color: #0B1A20;">
                                            {{ __('admin.current_logo_label') }}
                                        </p>
                                        <p class="text-xs" style="color: #6B7280;">{{ __('admin.replace_logo_hint') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- FAVICON SECTION -->
                    <div class="mb-8">
                        <h3 class="text-base sm:text-lg font-semibold mb-4 flex items-center" style="color: #0B1A20;">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ __('Favicon') }}
                        </h3>
                    
                        <div class="border-2 border-dashed rounded-lg p-4 sm:p-6 text-center">
                            <input type="file"
                                   name="favicon"
                                   id="faviconInput"
                                   class="hidden"
                                   accept=".ico,.png,.jpg,.jpeg,.svg">
                    
                            <label for="faviconInput" class="cursor-pointer block">
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 sm:mb-3"
                                     style="color: #162E38; opacity: 0.5;"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16">
                                    </path>
                                </svg>
                    
                                <p class="text-xs sm:text-sm" style="color: #6B7280;">
                                    {{ __('Upload Favicon') }}
                                </p>
                    
                                <p class="text-xs mt-1" style="color: #9CA3AF;">
                                    {{ __('Recommended: 32x32 or .ico file') }}
                                </p>
                            </label>
                        </div>
                    
                        <div id="faviconPreview" class="mt-4">
                            @if($logo && $logo->favicon)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 p-3 sm:p-4 rounded-lg"
                                     style="background: rgba(22, 46, 56, 0.05);">
                                    <img src="{{ asset('storage/'.$logo->favicon) }}"
                                         class="h-8 w-8 sm:h-10 sm:w-10 object-contain">
                    
                                    <div>
                                        <p class="text-sm font-medium">
                                            {{ __('Current Favicon') }}
                                        </p>
                                        <p class="text-xs" style="color: #6B7280;">
                                            {{ __('Upload new file to replace') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- MENUS SECTION -->
                    <div class="mb-8">
                        <h3 class="text-base sm:text-lg font-semibold mb-4 flex items-center" style="color: #0B1A20;">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0" style="color: #162E38;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            {{ __('admin.navigation_menus_section_title') }}
                        </h3>

                        <div id="menu-wrapper" class="space-y-3">
                            @if ($menus->count())
                                @foreach ($menus as $i => $menu)
                                    <div class="menu-row flex flex-col sm:flex-row sm:items-center gap-3 p-3 rounded-lg transition-all"
                                        style="background: rgba(22, 46, 56, 0.03);">
                                        <div class="flex-1 w-full">
                                            <label class="block text-xs font-medium mb-1" style="color: #4B5565;">{{ __('Menu Name') }}</label>
                                            <input type="text" name="menus[{{ $i }}][title]"
                                                value="{{ $menu->getRawOriginal('title') }}" 
                                                placeholder="{{ __('Menu Name') }}"
                                                class="w-full border rounded-lg px-3 sm:px-4 py-2 text-sm sm:text-base focus:outline-none focus:ring-2 transition-all"
                                                style="border-color: #e5e7eb;">
                                        </div>
                                        <div class="flex items-center gap-4 sm:gap-6">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" name="menus[{{ $i }}][status]"
                                                    {{ $menu->status ? 'checked' : '' }} class="w-4 h-4"
                                                    style="accent-color: #162E38;">
                                                <span class="text-sm" style="color: #6B7280;">{{ __('admin.active_label') }}</span>
                                            </label>
                                            <button type="button"
                                                class="remove-menu text-white rounded-full w-8 h-8 flex items-center justify-center transition-all hover:scale-110 flex-shrink-0"
                                                style="background: #dc3545;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="menu-row flex flex-col sm:flex-row sm:items-center gap-3 p-3 rounded-lg"
                                    style="background: rgba(22, 46, 56, 0.03);">
                                    <div class="flex-1 w-full">
                                        <label class="block text-xs font-medium mb-1" style="color: #4B5565;">{{ __('Menu Name') }}</label>
                                        <input type="text" name="menus[0][title]" 
                                            placeholder="{{ __('Menu Name') }}"
                                            class="w-full border rounded-lg px-3 sm:px-4 py-2 text-sm sm:text-base focus:outline-none focus:ring-2 transition-all"
                                            style="border-color: #e5e7eb;">
                                    </div>
                                    <div class="flex items-center gap-4 sm:gap-6">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="menus[0][status]" checked class="w-4 h-4"
                                                style="accent-color: #162E38;">
                                            <span class="text-sm" style="color: #6B7280;">{{ __('admin.active_label') }}</span>
                                        </label>
                                        <button type="button"
                                            class="remove-menu text-white rounded-full w-8 h-8 flex items-center justify-center transition-all hover:scale-110 flex-shrink-0"
                                            style="background: #dc3545;">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <button type="button" id="add-menu"
                            class="inline-flex items-center px-3 sm:px-4 py-2 rounded-lg transition-all duration-300 mt-4 text-sm sm:text-base"
                            style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            {{ __('admin.add_menu_btn') }}
                        </button>
                    </div>

                    <!-- FORM ACTIONS -->
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t" style="border-color: #e5e7eb;">
                        <a href="{{ route('admin.header.index') }}"
                            class="px-4 sm:px-6 py-2 rounded-lg transition-all duration-300 text-center text-sm sm:text-base"
                            style="border: 1px solid #e5e7eb; color: #6B7280; background: transparent;">
                            {{ __('admin.cancel_btn') }}
                        </a>
                        <button type="submit"
                            class="px-4 sm:px-6 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02] text-sm sm:text-base"
                            style="color: #2a1a05; font-family: Georgia, serif; margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                            <svg class="inline-block w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            {{ __('admin.save_changes_btn') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .transition-all {
                transition: all 0.3s ease;
            }

            input:focus {
                border-color: #162E38 !important;
                box-shadow: 0 0 0 2px rgba(22, 46, 56, 0.1) !important;
                outline: none;
            }

            .border-dashed:hover {
                border-color: #162E38 !important;
                background: rgba(22, 46, 56, 0.02);
            }

            .menu-row {
                transition: all 0.2s ease;
            }

            .menu-row:hover {
                transform: translateX(4px);
            }

            /* Mobile optimizations */
            @media (max-width: 640px) {
                .container {
                    padding-left: 1rem;
                    padding-right: 1rem;
                }
                
                .menu-row {
                    transform: none !important;
                }
                
                .menu-row:hover {
                    transform: none !important;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            let index = {{ $menus->count() ?? 1 }};

            // Add menu functionality
            document.getElementById('add-menu').addEventListener('click', function() {
                let html = `
                <div class="menu-row flex flex-col sm:flex-row sm:items-center gap-3 p-3 rounded-lg transition-all" style="background: rgba(22, 46, 56, 0.03);">
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-medium mb-1" style="color: #4B5565;">{{ __('Menu Name') }}</label>
                        <input type="text" name="menus[${index}][title]" placeholder="{{ __('Menu Name') }}"
                               class="w-full border rounded-lg px-3 sm:px-4 py-2 text-sm sm:text-base focus:outline-none focus:ring-2 transition-all"
                               style="border-color: #e5e7eb;">
                    </div>
                    <div class="flex items-center gap-4 sm:gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="menus[${index}][status]" checked
                                   class="w-4 h-4" style="accent-color: #162E38;">
                            <span class="text-sm" style="color: #6B7280;">{{ __('admin.active_label') }}</span>
                        </label>
                        <button type="button"
                                class="remove-menu text-white rounded-full w-8 h-8 flex items-center justify-center transition-all hover:scale-110 flex-shrink-0"
                                style="background: #dc3545;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
                document.getElementById('menu-wrapper').insertAdjacentHTML('beforeend', html);
                index++;
            });

            // Remove menu functionality
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-menu')) {
                    e.target.closest('.menu-row').remove();
                    // Renumber remaining menus (title, status)
                    document.querySelectorAll('.menu-row').forEach((row, idx) => {
                        const titleInput = row.querySelector('input[name$="[title]"]');
                        if (titleInput) {
                            titleInput.name = `menus[${idx}][title]`;
                        }
                        const checkbox = row.querySelector('input[type="checkbox"]');
                        if (checkbox) {
                            checkbox.name = `menus[${idx}][status]`;
                        }
                    });
                    // Update index for next additions
                    const remainingMenus = document.querySelectorAll('.menu-row').length;
                    index = remainingMenus;
                }
            });

            // Logo preview functionality
            const logoInput = document.getElementById('logoInput');
            const logoPreview = document.getElementById('logoPreview');

            if (logoInput) {
                logoInput.addEventListener('change', function(e) {
                    if (e.target.files && e.target.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewHtml = `
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 p-3 sm:p-4 rounded-lg" style="background: rgba(22, 46, 56, 0.05);">
                                <img src="${e.target.result}" class="h-12 sm:h-16 w-auto object-contain self-start sm:self-auto">
                                <div>
                                    <p class="text-sm font-medium" style="color: #0B1A20;">{{ __('admin.new_logo_preview') }}</p>
                                    <p class="text-xs" style="color: #6B7280;">{{ __('admin.new_logo_hint') }}</p>
                                </div>
                            </div>
                        `;
                            logoPreview.innerHTML = previewHtml;
                        };
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            }

            // Drag and drop handlers
            window.dragOverHandler = function(e) {
                e.preventDefault();
                const target = e.target.closest('.border-dashed');
                if (target) {
                    target.style.borderColor = '#162E38';
                    target.style.background = 'rgba(22, 46, 56, 0.02)';
                }
            }

            window.dropHandler = function(e) {
                e.preventDefault();
                const target = e.target.closest('.border-dashed');
                if (target) {
                    const file = e.dataTransfer.files[0];
                    if (file && file.type.startsWith('image/')) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        logoInput.files = dt.files;

                        // Trigger change event
                        const event = new Event('change');
                        logoInput.dispatchEvent(event);
                    }
                    target.style.borderColor = '#e5e7eb';
                    target.style.background = 'transparent';
                }
            }

            // Reset drag styles
            document.querySelectorAll('.border-dashed').forEach(el => {
                el.addEventListener('dragleave', function(e) {
                    this.style.borderColor = '#e5e7eb';
                    this.style.background = 'transparent';
                });
            });
            
            // Favicon preview functionality
            const faviconInput = document.getElementById('faviconInput');
            const faviconPreview = document.getElementById('faviconPreview');
            
            if (faviconInput) {
                faviconInput.addEventListener('change', function(e) {
                    if (e.target.files && e.target.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            faviconPreview.innerHTML = `
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 p-3 sm:p-4 rounded-lg"
                                     style="background: rgba(22,46,56,0.05);">
                                    <img src="${event.target.result}"
                                         class="h-8 w-8 sm:h-10 sm:w-10 object-contain">
                                    <div>
                                        <p class="text-sm font-medium">
                                            {{ __('New Favicon Preview') }}
                                        </p>
                                    </div>
                                </div>
                            `;
                        };
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            }
        </script>
    @endpush
@endsection