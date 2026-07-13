@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">{{ __('admin.create_new_blog') }}</h2>
            <a href="{{ route('admin.blogs.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                {{ __('admin.back_to_blogs') }}
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <form method="POST" action="{{ route('admin.blogs.store') }}" enctype="multipart/form-data" class="p-6">
                @csrf

                <!-- Blog Entries Section -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('admin.blog_entries') }}</label>
                    <div id="blog-entries-wrapper">
                        <div class="blog-entry mb-4 border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-lg font-semibold text-gray-800">{{ __('admin.blog_entry') }} #1</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.heading_english') }}</label>
                                    <input type="text" name="heading"
                                        placeholder="{{ __('admin.enter_heading_english') }}"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.heading_german') }}</label>
                                    <input type="text" name="heading_de"
                                        placeholder="{{ __('admin.enter_heading_german') }}"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.title_english') }}</label>
                                    <input type="text" name="entries[0][title]"
                                        placeholder="{{ __('admin.enter_title_english') }}"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.title_german') }}</label>
                                    <input type="text" name="entries[0][title_de]"
                                        placeholder="{{ __('admin.enter_title_german') }}"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.description_english') }}</label>
                                    <textarea name="entries[0][description]" rows="3" placeholder="{{ __('admin.enter_description_english') }}"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.description_german') }}</label>
                                    <textarea name="entries[0][description_de]" rows="3" placeholder="{{ __('admin.enter_description_german') }}"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addBlogEntry()"
                        class="mt-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md transition duration-200 text-sm font-medium">
                        + {{ __('admin.add_another_entry') }}
                    </button>
                </div>

                <!-- Category Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.category') }}</label>
                    <select name="category_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                        <option value="">{{ __('admin.select_category') }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Images Section -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.images') }}</label>
                    <div id="image-wrapper" class="space-y-2">
                        <div class="blog-image flex items-start justify-between mb-2">
                            <div class="w-full">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('admin.blog_image') }} #1</h3>
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
                        + {{ __('admin.add_more_images') }}
                    </button>
                    <p class="text-xs text-gray-500 mt-2">{{ __('admin.image_upload_note') }}</p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end border-t border-gray-200 pt-6">
                    <button type="submit"
                        class="bg-black hover:bg-gray-800 text-white px-6 py-2 rounded-md transition duration-200">
                        {{ __('admin.create_blog') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let entryIndex = 1;
        let imageIndex = 1;

        function addBlogEntry() {
            let html = `
                <div class="blog-entry mb-4 border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('admin.blog_entry') }} #${entryIndex + 1}</h3>
                        <button type="button" onclick="removeBlogEntry(this)" class="bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition duration-200">×</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.title_english') }}</label>
                            <input type="text" name="entries[${entryIndex}][title]" placeholder="{{ __('admin.enter_title_english') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.title_german') }}</label>
                            <input type="text" name="entries[${entryIndex}][title_de]" placeholder="{{ __('admin.enter_title_german') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.description_english') }}</label>
                            <textarea name="entries[${entryIndex}][description]" rows="3" placeholder="{{ __('admin.enter_description_english') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.description_german') }}</label>
                            <textarea name="entries[${entryIndex}][description_de]" rows="3" placeholder="{{ __('admin.enter_description_german') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('blog-entries-wrapper').insertAdjacentHTML('beforeend', html);
            entryIndex++;
        }

        function removeBlogEntry(btn) {
            if (confirm('{{ __('admin.remove_blog_entry_confirm') }}')) {
                btn.closest('.blog-entry').remove();
                document.querySelectorAll('.blog-entry').forEach((entry, idx) => {
                    const heading = entry.querySelector('h3');
                    if (heading) heading.textContent = `{{ __('admin.blog_entry') }} #${idx + 1}`;

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
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('admin.blog_image') }} #${imageIndex + 1}</h3>
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
                    if (heading) heading.textContent = `{{ __('admin.blog_image') }} #${idx + 1}`;
                });
                imageIndex = document.querySelectorAll('.blog-image').length;
            }
        }
    </script>
@endsection
