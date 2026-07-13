@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h2 style="color: #2a1a05; font-family: Georgia, serif; margin: 0;" class="text-3xl font-bold">{{ __('admin.create_page_title') }}</h2>
                <p class="mt-2 text-sm" style="color: #4B5565;">{{ __('admin.create_page_subtitle') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.pages.index') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg transition-all duration-300"
                    style="border: 1px solid #162E38; color: #162E38; background: transparent;">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to list
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-lg"
                        style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545;">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5" style="color: #dc3545;" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium" style="color: #dc3545;">{{ __('admin.fix_errors_title') }}
                                </h3>
                                <ul class="mt-2 text-sm list-disc list-inside" style="color: #721c24;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- TITLE -->
                    <div class="mb-5">
                        <label class="block font-medium mb-2" style="color: #0B1A20;">
                            <span class="text-danger">*</span> Title
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}"
                            class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 transition-all"
                            style="border-color: #e5e7eb;" placeholder="Place your title">
                    </div>

                    <!-- Heading -->
                    <div class="mb-5">
                        <label class="block font-medium mb-2" style="color: #0B1A20;">Heading</label>
                        <input type="text" name="heading" value="{{ old('heading') }}"
                            class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 transition-all"
                            style="border-color: #e5e7eb;" placeholder="Place your heading">
                    </div>

                    <!-- CONTENT with CKEditor -->
                    <div class="mb-5">
                        <label class="block font-medium mb-2" style="color: #0B1A20;">Content</label>
                        <textarea name="content" id="editor" rows="10"
                            class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 transition-all font-mono"
                            style="border-color: #e5e7eb;" placeholder="">{{ old('content') }}</textarea>
                    </div>

                    <!-- IMAGES & VIDEOS -->
                    <div class="mb-5">
                        <label class="block font-medium mb-2" style="color: #0B1A20;">Media (Images & Videos)</label>
                        <div class="border-2 border-dashed rounded-lg p-6 text-center transition-all"
                            style="border-color: #e5e7eb;" ondrop="dropHandler(event)" ondragover="dragOverHandler(event)">
                            <input type="file" name="images[]" multiple id="fileInput" class="hidden" accept="image/*,video/*">
                            <label for="fileInput" class="cursor-pointer">
                                <svg class="w-12 h-12 mx-auto mb-3" style="color: #162E38; opacity: 0.5;" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <p class="text-sm" style="color: #6B7280;">Upload Images or Videos</p>
                                <p class="text-xs text-gray-400 mt-1">Supported: JPG, PNG, WEBP, MP4, MOV, AVI, WEBM (Max 50MB each)</p>
                            </label>
                        </div>
                        <div id="mediaPreview" class="mt-3 flex flex-wrap gap-2"></div>
                    </div>

                    <!-- STATUS -->
                    <div class="mb-6 p-4 rounded-lg" style="background: rgba(22, 46, 56, 0.05);">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" checked class="w-4 h-4"
                                style="accent-color: #162E38;">
                            <span class="font-medium" style="color: #0B1A20;">{{ __('admin.active_label') }}</span>
                        </label>
                    </div>

                    <!-- FORM ACTIONS -->
                    <div class="flex justify-end gap-3 pt-4 border-t" style="border-color: #e5e7eb;">
                        <a href="{{ route('admin.pages.index') }}"
                            class="px-6 py-2 rounded-lg transition-all duration-300"
                            style="border: 1px solid #e5e7eb; color: #6B7280; background: transparent;">
                            {{ __('admin.cancel_btn') }}
                        </a>
                        <button type="submit"
                            class="px-6 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"
                            style="color: #2a1a05; font-family: Georgia, serif; margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Create Page
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .transition-all {
            transition: all 0.3s ease;
        }

        input:focus,
        textarea:focus {
            border-color: #162E38 !important;
            box-shadow: 0 0 0 2px rgba(22, 46, 56, 0.1) !important;
            outline: none;
        }

        .border-dashed:hover {
            border-color: #162E38 !important;
            background: rgba(22, 46, 56, 0.02);
        }

        /* Media preview styling */
        .preview-media {
            position: relative;
            display: inline-block;
        }

        .preview-media video,
        .preview-media img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            transition: all 0.2s ease;
        }

        .preview-media video:hover,
        .preview-media img:hover {
            transform: scale(1.05);
            border-color: #162E38;
        }

        .preview-media .remove-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s ease;
            z-index: 10;
        }

        .preview-media .remove-btn:hover {
            transform: scale(1.1);
        }

        .preview-media .media-type-badge {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: rgba(0,0,0,0.7);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* CKEditor styling */
        .ck-editor__editable_inline {
            min-height: 300px;
        }
        
        .ck-editor__editable:not(.ck-editor__nested-editable) {
            border-radius: 8px !important;
            border-color: #e5e7eb !important;
        }
        
        .ck-editor__top {
            border-radius: 8px 8px 0 0 !important;
        }
        
        .ck.ck-editor__editable:focus {
            border-color: #162E38 !important;
            box-shadow: 0 0 0 2px rgba(22, 46, 56, 0.1) !important;
        }
    </style>

    <!-- Include CKEditor 5 CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

    <script>
        // Initialize CKEditor
        let editorInstance = null;
        
        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'bulletedList', 'numberedList', '|',
                        'alignment', '|',
                        'link', 'blockQuote', 'insertTable', '|',
                        'undo', 'redo'
                    ]
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                        { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' }
                    ]
                },
                language: 'en',
                placeholder: 'Write your page content here...'
            })
            .then(editor => {
                editorInstance = editor;
                const oldContent = @json(old('content'));
                if (oldContent) {
                    editor.setData(oldContent);
                }
            })
            .catch(error => {
                console.error('CKEditor initialization error:', error);
            });

        // Media preview functionality
        const fileInput = document.getElementById('fileInput');
        const mediaPreview = document.getElementById('mediaPreview');
        let selectedFiles = [];

        fileInput.addEventListener('change', function(e) {
            selectedFiles = Array.from(e.target.files);
            updatePreview();
        });

        function updatePreview() {
            mediaPreview.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                const isVideo = file.type.startsWith('video/');
                
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-media';
                    
                    if (isVideo) {
                        div.innerHTML = `
                            <video src="${e.target.result}" muted></video>
                            <div class="media-type-badge">🎬 Video</div>
                            <div class="remove-btn" data-index="${index}">×</div>
                        `;
                    } else {
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <div class="media-type-badge">🖼️ Image</div>
                            <div class="remove-btn" data-index="${index}">×</div>
                        `;
                    }
                    
                    mediaPreview.appendChild(div);

                    // Add remove functionality
                    div.querySelector('.remove-btn').addEventListener('click', function() {
                        selectedFiles.splice(index, 1);
                        updatePreview();
                        // Update file input
                        const dt = new DataTransfer();
                        selectedFiles.forEach(f => dt.items.add(f));
                        fileInput.files = dt.files;
                    });
                };
                reader.readAsDataURL(file);
            });
        }

        // Drag and drop handlers
        function dragOverHandler(e) {
            e.preventDefault();
            e.target.closest('.border-dashed').style.borderColor = '#162E38';
            e.target.closest('.border-dashed').style.background = 'rgba(22, 46, 56, 0.02)';
        }

        function dropHandler(e) {
            e.preventDefault();
            const files = Array.from(e.dataTransfer.files).filter(f => 
                f.type.startsWith('image/') || f.type.startsWith('video/')
            );
            selectedFiles = [...selectedFiles, ...files];
            updatePreview();

            // Update file input
            const dt = new DataTransfer();
            selectedFiles.forEach(f => dt.items.add(f));
            fileInput.files = dt.files;

            e.target.closest('.border-dashed').style.borderColor = '#e5e7eb';
            e.target.closest('.border-dashed').style.background = 'transparent';
        }

        // Reset drag styles
        document.querySelector('.border-dashed')?.addEventListener('dragleave', function(e) {
            this.style.borderColor = '#e5e7eb';
            this.style.background = 'transparent';
        });

        // Ensure form submits the updated CKEditor content
        document.querySelector('form').addEventListener('submit', function(e) {
            if (editorInstance) {
                const editorData = editorInstance.getData();
                document.querySelector('#editor').value = editorData;
            }
        });
    </script>
@endsection