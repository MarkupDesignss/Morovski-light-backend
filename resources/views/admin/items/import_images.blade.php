@extends('layouts.admin')

@section('title', 'Upload Images for CSV Import')

@section('content')
    <div class="space-y-8 min-h-screen p-6">

        {{-- HEADER --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold" style="color: #2a1a05; font-family: Georgia, serif; margin: 0;">
                    Upload Images for CSV Import
                </h2>
                <p class="mt-2 text-sm text-stone-600">All images will be stored temporarily for import</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.items.import.form') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300"
                    style="background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to CSV Import
                </a>
            </div>
        </div>

        {{-- UPLOAD FORM --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100" style="background: rgba(22, 12, 0, 0.02);">
                <h3 class="text-lg font-semibold flex items-center" style="color: #160c00;">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Upload Images
                </h3>
            </div>
            <form action="{{ route('admin.items.import.images.upload') }}" method="POST" enctype="multipart/form-data"
                class="p-6">
                @csrf
                <div class="mb-6">
                    <div class="dropzone-area border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all duration-300 hover:border-stone-400"
                        style="background: rgba(22, 12, 0, 0.02); border-color: #e5e5e5;">
                        <input type="file" name="images[]" id="images" class="hidden" multiple
                            accept="image/jpeg,image/png,image/jpg,image/webp">
                        <svg class="w-12 h-12 mx-auto mb-4 text-stone-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-stone-600 mb-1">Click or drag images here to upload</p>
                        <p class="text-xs text-stone-400">JPG, PNG, WEBP (Max 5MB each)</p>
                    </div>
                    @error('images.*')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                    <div id="image-preview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"></div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                        class="px-6 py-2 rounded-xl text-white font-semibold transition-all duration-300 transform hover:scale-[1.02] shadow-md hover:shadow-lg"
                        style="background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Upload All
                    </button>
                </div>
            </form>
        </div>

        {{-- UPLOADED IMAGES LIST --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100" style="background: rgba(22, 12, 0, 0.02);">

                <div
                    style="display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">

                    <h3 class="text-lg font-semibold flex items-center" style="color: #160c00; margin: 0;">
                        Uploaded Images ({{ $imageNames->total() }})
                    </h3>

                    {{-- Search Form --}}
                    <form method="GET" action="{{ route('admin.items.import.images') }}"
                        style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">

                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search image name..."
                                class="pl-10 pr-4 py-2 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 focus:outline-none transition w-64">

                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <button type="submit" class="px-4 py-2 rounded-xl text-white transition-all duration-300"
                            style="background: linear-gradient(135deg, #3a2819, #160c00);">
                            Search
                        </button>

                        @if (request()->filled('search'))
                            <a href="{{ route('admin.items.import.images') }}"
                                class="px-4 py-2 rounded-xl text-stone-700 bg-stone-100 hover:bg-stone-200 transition">
                                Reset
                            </a>
                        @endif

                    </form>

                </div>

            </div>
            <div class="p-6">
                @if ($imageNames->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach ($imageNames->items() as $img)
                            <div class="relative group border rounded-lg p-2 bg-stone-50">
                                <img src="{{ asset('import_images/' . $img) }}"
                                    class="w-full h-24 object-cover rounded cursor-pointer view-image"
                                    data-src="{{ asset('import_images/' . $img) }}" data-name="{{ $img }}">
                                <p class="text-xs truncate mt-1">{{ $img }}</p>
                                {{-- DELETE BUTTON (always visible with slight opacity) --}}
                                <button type="button"
                                    class="delete-image absolute top-1 right-1 bg-rose-600 text-white rounded-full p-1 opacity-80 hover:opacity-100 transition"
                                    data-filename="{{ $img }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $imageNames->links('pagination::tailwind') }}
                    </div>
                @else
                    <p class="text-stone-500 text-center py-8">No images uploaded yet.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- IMAGE PREVIEW MODAL --}}
    <div id="image-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center"
        style="background: rgba(0,0,0,0.75);">
        <div class="relative max-w-3xl w-full mx-4">
            <button type="button" id="close-image-modal"
                class="absolute -top-4 -right-4 bg-white text-stone-800 rounded-full p-2 shadow-lg hover:bg-rose-600 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img id="modal-image" src=""
                class="w-full max-h-[80vh] object-contain rounded-xl shadow-2xl bg-white">
            <p id="modal-image-name" class="text-center text-white text-sm mt-3"></p>
        </div>
    </div>

    {{-- JavaScript (same as before, no change) --}}
    <script>
        // Drag & drop and preview
        const dropzone = document.querySelector('.dropzone-area');
        const fileInput = document.getElementById('images');
        if (dropzone) {
            dropzone.addEventListener('click', () => fileInput.click());
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('drag-over');
            });
            dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('drag-over');
                fileInput.files = e.dataTransfer.files;
                previewImages(fileInput.files);
            });
        }

        function previewImages(files) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            for (let file of files) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const col = document.createElement('div');
                        col.className = 'relative group';
                        col.innerHTML =
                            `<div class="rounded-lg overflow-hidden shadow-sm border border-stone-200"><img src="${e.target.result}" class="w-full h-32 object-cover"><div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"><button type="button" class="remove-preview text-white bg-rose-600 p-1 rounded-full"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div><div class="p-2 bg-white"><p class="text-xs text-stone-600 truncate">${file.name}</p></div></div>`;
                        preview.appendChild(col);
                        col.querySelector('.remove-preview').addEventListener('click', () => col.remove());
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
        if (fileInput) {
            fileInput.addEventListener('change', () => previewImages(fileInput.files));
        }

        // Delete image AJAX
        document.querySelectorAll('.delete-image').forEach(btn => {
            btn.addEventListener('click', function() {
                const filename = this.dataset.filename;
                if (confirm('Delete this image? It will be removed from import folder.')) {
                    const deleteUrl =
                        '{{ route('admin.items.import.images.delete', ['filename' => '__filename__']) }}'
                        .replace('__filename__', filename);
                    fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) location.reload();
                            else alert('Failed to delete');
                        })
                        .catch(() => alert('Something went wrong.'));
                }
            });
        });

        // Image preview modal (click thumbnail to view bigger)
        const imageModal = document.getElementById('image-view-modal');
        const modalImage = document.getElementById('modal-image');
        const modalImageName = document.getElementById('modal-image-name');
        const closeImageModal = document.getElementById('close-image-modal');

        document.querySelectorAll('.view-image').forEach(img => {
            img.addEventListener('click', function() {
                modalImage.src = this.dataset.src;
                modalImageName.textContent = this.dataset.name;
                imageModal.classList.remove('hidden');
                imageModal.classList.add('flex');
            });
        });

        function hideImageModal() {
            imageModal.classList.add('hidden');
            imageModal.classList.remove('flex');
            modalImage.src = '';
        }

        if (closeImageModal) {
            closeImageModal.addEventListener('click', hideImageModal);
        }
        // click outside image to close
        if (imageModal) {
            imageModal.addEventListener('click', function(e) {
                if (e.target === imageModal) hideImageModal();
            });
        }
        // Esc key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') hideImageModal();
        });
    </script>
@endsection
