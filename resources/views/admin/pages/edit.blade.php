@extends('layouts.admin')
<style>
    img,
video {
    transition: transform 0.2s ease;
}

img:hover,
video:hover {
    transform: scale(1.05);
}
</style>

@section('content')
    <div class="container mx-auto px-4 py-6">
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-2xl font-bold text-gray-800" style="color: #2a1a05; font-family: Georgia, serif; margin: 0;">
                    {{ __('admin.edit_page_title') }}
                </h2>
            </div>

            <form method="POST" action="{{ route('admin.pages.update', $page->id) }}" 
                  enctype="multipart/form-data" 
                  class="p-6"
                  id="pageEditForm">
                @csrf
                @method('PUT')

                <!-- TITLE SECTION -->
                <div class="grid grid-cols-1 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <input type="text" 
                               name="title" 
                               value="{{ old('title', $page->getRawOriginal('title')) }}"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent"
                               required>
                    </div>
                </div>

                <!-- HEADING SECTION -->
                <div class="grid grid-cols-1 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Heading</label>
                        <input type="text" 
                               name="heading" 
                               value="{{ old('heading', $page->getRawOriginal('heading')) }}"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                </div>

                <!-- CONTENT SECTION -->
                <div class="grid grid-cols-1 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                        <textarea name="content" 
                                  rows="6"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">{{ old('content', $page->getRawOriginal('content')) }}</textarea>
                    </div>
                </div>

                <!-- META TITLE SECTION -->
                <div class="grid grid-cols-1 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                        <input type="text" 
                               name="meta_title" 
                               value="{{ old('meta_title', $page->meta_title) }}"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                </div>

                <!-- META DESCRIPTION SECTION -->
                <div class="grid grid-cols-1 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                        <textarea name="meta_description" 
                                  rows="3"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">{{ old('meta_description', $page->meta_description) }}</textarea>
                    </div>
                </div>

                <!-- EXISTING IMAGES -->
                @if ($page->images && $page->images->count() > 0)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            {{ __('admin.existing_images_label') }}
                        </label>
                
                        <div class="flex gap-4 flex-wrap">
                            @foreach ($page->images as $img)
                
                                @php
                                    $extension = strtolower(pathinfo($img->image, PATHINFO_EXTENSION));
                                    $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);
                                @endphp
                
                                <div id="image-{{ $img->id }}" class="relative group">
                
                                    @if ($isVideo)
                
                                        <video controls
                                            class="w-24 h-24 rounded-lg object-cover shadow-md border">
                                            <source src="{{ asset('storage/' . $img->image) }}">
                                            Your browser does not support the video tag.
                                        </video>
                
                                    @else
                
                                        <img src="{{ asset('storage/' . $img->image) }}"
                                             class="w-24 h-24 rounded-lg object-cover shadow-md"
                                             alt="Page Media">
                
                                    @endif
                
                                    <button type="button"
                                            onclick="deleteImage({{ $img->id }})"
                                            class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-lg transition duration-200">
                                        ×
                                    </button>
                
                                </div>
                
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- NEW IMAGES -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.add_new_images_label') }}</label>
                    <input type="file" 
                       name="images[]" 
                       multiple 
                       accept="image/*,video/*"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800">
                    <!--<input type="file" -->
                    <!--       name="images[]" -->
                    <!--       multiple -->
                    <!--       accept="image/jpeg,image/png,image/webp"-->
                    <!--       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800">-->
                    <p class="text-xs text-gray-500 mt-1">{{ __('admin.images_hint') }}</p>
                </div>

                <!-- STATUS -->
                <div class="mb-6">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $page->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-black focus:ring-black">
                        <span class="ml-2 text-sm font-medium text-gray-700">{{ __('admin.active_label') }}</span>
                    </label>
                </div>

                <!-- SUBMIT BUTTON -->
                <div class="flex justify-end border-t border-gray-200 pt-6">
                    <a href="{{ route('admin.pages.index') }}"
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-md transition duration-200 mr-3">
                        {{ __('admin.cancel_btn') }}
                    </a>
                    <button type="submit"
                            class="bg-black hover:bg-gray-800 text-white px-6 py-2 rounded-md transition duration-200">
                        {{ __('admin.update_page_btn') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

   <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : '{{ csrf_token() }}';

    async function deleteImage(id) {

        if (!confirm('Are you sure you want to delete this image?')) {
            return;
        }

        try {

            const response = await fetch(`{{ url('admin/pages/image') }}/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.status) {

                const imageElement = document.getElementById('image-' + id);

                if (imageElement) {
                    imageElement.remove();
                }

                showNotification(data.message || 'Image deleted successfully', 'success');

            } else {

                showNotification(
                    data.message || 'Failed to delete image',
                    'error'
                );
            }

        } catch (error) {

            console.error('Delete Error:', error);

            showNotification(
                'Something went wrong while deleting the image.',
                'error'
            );
        }
    }

    function showNotification(message, type = 'success') {

        const notification = document.createElement('div');

        notification.className =
            `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success'
                    ? 'bg-green-500'
                    : 'bg-red-500'
            } text-white`;

        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {

            notification.style.opacity = '0';

            setTimeout(() => {
                notification.remove();
            }, 300);

        }, 3000);
    }
</script>
@endsection