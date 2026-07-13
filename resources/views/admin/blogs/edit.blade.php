@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">{{ __('admin.edit_blog_title') }}</h2>
            <div class="space-x-3">
                <a href="{{ route('admin.blogs.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                    {{ __('admin.back_to_blogs_btn') }}
                </a>
                <a href="{{ route('admin.blogs.show', $blog->id) }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200">
                    {{ __('admin.view_blog_btn') }}
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <form method="POST" action="{{ route('admin.blogs.update', $blog->id) }}" enctype="multipart/form-data"
                class="p-6">
                @csrf
                @method('PUT')

                <!-- Blog Entries Section -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('admin.blog_entries_label') }}</label>
                    <div id="blog-entries-wrapper">
                        @if ($blog->entries && count($blog->entries) > 0)
                            @php
                                $entries = $blog->getRawOriginal('entries')
                                    ? json_decode($blog->getRawOriginal('entries'), true)
                                    : [];
                            @endphp

                            @foreach ($entries as $index => $entry)
                                <div class="blog-entry mb-4 border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <div class="flex justify-between items-center mb-3">
                                        <h3 class="text-lg font-semibold text-gray-800">{{ __('admin.blog_entry_label') }}
                                            #{{ $loop->iteration }}
                                        </h3>
                                        @if (!$loop->first)
                                            <button type="button" onclick="removeBlogEntry(this)"
                                                class="bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition duration-200">×</button>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.heading_en_label') }}
                                            </label>
                                            <input type="text" name="heading"
                                                value="{{ $blog->getRawOriginal('heading') ?? '' }}"
                                                placeholder="{{ __('admin.heading_en_placeholder') }}"
                                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.heading_de_label') }}</label>
                                            <input type="text" name="heading_de" value="{{ $blog->heading_de ?? '' }}"
                                                placeholder="{{ __('admin.heading_de_placeholder') }}"
                                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.title_en_label') }}
                                            </label>
                                            <input type="text" name="entries[{{ $index }}][title]"
                                                value="{{ $entry['title'] ?? '' }}"
                                                placeholder="{{ __('admin.title_en_placeholder') }}"
                                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.title_de_label') }}</label>
                                            <input type="text" name="entries[{{ $index }}][title_de]"
                                                value="{{ $entry['title_de'] ?? '' }}"
                                                placeholder="{{ __('admin.title_de_placeholder') }}"
                                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.description_en_label') }}</label>
                                            <textarea name="entries[{{ $index }}][description]" rows="3"
                                                placeholder="{{ __('admin.description_en_placeholder') }}"
                                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">{{ $entry['description'] ?? '' }}</textarea>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.description_de_label') }}</label>
                                            <textarea name="entries[{{ $index }}][description_de]" rows="3"
                                                placeholder="{{ __('admin.description_de_placeholder') }}"
                                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">{{ $entry['description_de'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="blog-entry mb-4 border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">{{ __('admin.blog_entry_label') }} #1
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.title_en_label') }}</label>
                                        <input type="text" name="entries[0][title]"
                                            placeholder="{{ __('admin.title_en_placeholder') }}"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.title_de_label') }}</label>
                                        <input type="text" name="entries[0][title_de]"
                                            placeholder="{{ __('admin.title_de_placeholder') }}"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.description_en_label') }}</label>
                                        <textarea name="entries[0][description]" rows="3" placeholder="{{ __('admin.description_en_placeholder') }}"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.description_de_label') }}</label>
                                        <textarea name="entries[0][description_de]" rows="3" placeholder="{{ __('admin.description_de_placeholder') }}"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="button" onclick="addBlogEntry()"
                        class="mt-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200 text-sm font-medium">
                        {{ __('admin.add_entry_btn') }}
                    </button>
                </div>

                <!-- Category Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.category_label') }}</label>
                    <select name="category_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                        <option value="">{{ __('admin.select_category_option') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $blog->category_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Existing Images -->
                @if ($blog->images && $blog->images->count() > 0)
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-3">{{ __('admin.existing_images_label') }}</label>
                        <div class="flex gap-3 flex-wrap">
                            @foreach ($blog->images as $img)
                                <div class="relative group">
                                    <img src="{{ asset('storage/' . $img->image) }}"
                                        class="w-24 h-24 rounded-lg object-cover shadow-md">
                                    <button type="button" onclick="deleteImage({{ $img->id }})"
                                        class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-lg transition duration-200">
                                        ×
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Add New Images -->
                <div class="mb-6">
                    <label
                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.add_new_images_label') }}</label>
                    <div id="image-wrapper" class="space-y-2">
                        <div class="blog-image flex items-start justify-between mb-2">
                            <div class="w-full">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('admin.blog_image_label') }} #1
                                </h3>
                                <input type="file" name="images[]"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800">
                            </div>
                            <div class="ml-3 mt-6">
                                <button type="button" onclick="removeImage(this)"
                                    class="bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition duration-200">×</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addImage()"
                        class="mt-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200 text-sm font-medium">
                        {{ __('admin.add_more_images_btn') }}
                    </button>
                    <p class="text-xs text-gray-500 mt-2">{{ __('admin.images_hint') }}</p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end border-t border-gray-200 pt-6">
                    <button type="submit"
                        class="bg-black hover:bg-gray-800 text-white px-6 py-2 rounded-md transition duration-200">
                        {{ __('admin.update_blog_submit_btn') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let entryIndex = {{ isset($blog->entries) ? count($blog->entries) : 1 }};
        let imageIndex = 1;

        function addBlogEntry() {
            let html = `
                <div class="blog-entry mb-4 border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('admin.blog_entry_label') }} #${entryIndex + 1}</h3>
                        <button type="button" onclick="removeBlogEntry(this)" class="bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition duration-200">×</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.title_en_label') }}</label>
                            <input type="text" name="entries[${entryIndex}][title]" placeholder="{{ __('admin.title_en_placeholder') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.title_de_label') }}</label>
                            <input type="text" name="entries[${entryIndex}][title_de]" placeholder="{{ __('admin.title_de_placeholder') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.description_en_label') }}</label>
                            <textarea name="entries[${entryIndex}][description]" rows="3" placeholder="{{ __('admin.description_en_placeholder') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.description_de_label') }}</label>
                            <textarea name="entries[${entryIndex}][description_de]" rows="3" placeholder="{{ __('admin.description_de_placeholder') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('blog-entries-wrapper').insertAdjacentHTML('beforeend', html);
            entryIndex++;
        }

        function removeBlogEntry(btn) {
            if (confirm('{{ __('admin.remove_entry_confirm') }}')) {
                btn.closest('.blog-entry').remove();
                document.querySelectorAll('.blog-entry').forEach((entry, idx) => {
                    const heading = entry.querySelector('h3');
                    if (heading) heading.textContent = `{{ __('admin.blog_entry_label') }} #${idx + 1}`;

                    const inputs = entry.querySelectorAll('input, textarea');
                    inputs.forEach(input => {
                        if (input.name) {
                            input.name = input.name.replace(/entries\[\d+\]/, `entries[${idx}]`);
                        }
                    });
                });
            }
        }

        function addImage() {
            let html = `
                <div class="blog-image flex items-start justify-between mb-2">
                    <div class="w-full">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('admin.blog_image_label') }} #${imageIndex + 1}</h3>
                        <input type="file" name="images[]" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800">
                    </div>
                    <div class="ml-3 mt-6">
                        <button type="button" onclick="removeImage(this)" class="bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition duration-200">×</button>
                    </div>
                </div>`;

            document.getElementById('image-wrapper').insertAdjacentHTML('beforeend', html);
            imageIndex++;
        }

        function removeImage(btn) {
            if (confirm('{{ __('admin.remove_image_confirm') }}')) {
                btn.closest('.blog-image').remove();
                document.querySelectorAll('.blog-image').forEach((img, idx) => {
                    const heading = img.querySelector('h3');
                    if (heading) heading.textContent = `{{ __('admin.blog_image_label') }} #${idx + 1}`;
                });
                imageIndex = document.querySelectorAll('.blog-image').length;
            }
        }

        function deleteImage(id) {
            if (!confirm('{{ __('admin.delete_image_confirm') }}')) return;

            fetch(`/admin/blogs/image/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        location.reload();
                    } else {
                        alert(data.message || '{{ __('admin.delete_failed_msg') }}');
                    }
                })
                .catch(() => alert('{{ __('admin.something_wrong_msg') }}'));
        }
    </script>
@endsection
