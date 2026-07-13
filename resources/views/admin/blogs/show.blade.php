@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ __('admin.blog_details') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('admin.view_complete_blog_info') }}</p>
            </div>

            <div class="flex space-x-3">
                <a href="{{ route('admin.blogs.edit', $blog->id) }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    {{ __('admin.edit_blog') }}
                </a>

                <a href="{{ route('admin.blogs.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    {{ __('admin.back_to_list') }}
                </a>
            </div>
        </div>

        <!-- Blog Info -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- TITLE (FIXED HERE) -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">
                        {{ __('admin.blog_heading') }}
                    </label>

                    <p class="text-gray-900 font-semibold">
                        {{ $blog->heading ?? __('admin.na') }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">
                        {{ __('admin.blog_title') }}
                    </label>

                    <p class="text-gray-900 font-semibold">
                        {{ $blog->entries[0]['title'] ?? __('admin.na') }}
                    </p>
                </div>

                <!-- CATEGORY -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">
                        {{ __('admin.category') }}
                    </label>

                    <p>
                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                            {{ $blog->category->name ?? __('admin.uncategorized') }}
                        </span>
                    </p>
                </div>

                <!-- STATUS -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">
                        {{ __('admin.status') }}
                    </label>

                    <span
                        class="px-3 py-1 text-sm rounded-full
                    {{ $blog->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $blog->is_active ? __('admin.active') : __('admin.inactive') }}
                    </span>
                </div>

                <!-- CREATED DATE -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase mb-1">
                        {{ __('admin.created_date') }}
                    </label>

                    <p class="text-gray-900">
                        {{ $blog->created_at?->format('F d, Y') ?? __('admin.na') }}
                    </p>
                </div>

            </div>
        </div>

        <!-- ENTRIES -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">

            <h3 class="text-lg font-bold mb-4">{{ __('admin.blog_content') }}</h3>

            @forelse ($blog->entries as $index => $entry)
                <div class="border rounded-lg p-5 mb-4">

                    <!-- Entry Number -->
                    <div class="mb-3">
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded">
                            {{ __('admin.entry') }} #{{ $index + 1 }}
                        </span>
                    </div>

                    <!-- English -->
                    <h4 class="text-lg font-bold text-gray-900">
                        {{ $entry['title'] ?? __('admin.no_title') }}
                    </h4>

                    <p class="text-gray-600 mt-2">
                        {{ $entry['description'] ?? __('admin.no_description') }}
                    </p>

                    <!-- German -->
                    @if (!empty($entry['title_de']) || !empty($entry['description_de']))
                        <div class="mt-4 bg-gray-50 p-4 rounded border-l-4 border-blue-400">

                            <p class="text-sm text-gray-500 mb-1">{{ __('admin.german_version') }}</p>

                            <h5 class="font-semibold">
                                {{ $entry['title_de'] ?? '' }}
                            </h5>

                            <p class="text-gray-600">
                                {{ $entry['description_de'] ?? '' }}
                            </p>

                        </div>
                    @endif

                </div>
            @empty
                <p class="text-gray-500">{{ __('admin.no_entries_found') }}</p>
            @endforelse

        </div>

        <!-- IMAGES -->
        @if ($blog->images && $blog->images->count())
            <div class="bg-white shadow-lg rounded-lg p-6">

                <h3 class="text-lg font-bold mb-4">{{ __('admin.images') }}</h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                    @foreach ($blog->images as $image)
                        <img src="{{ asset('storage/' . $image->image) }}" class="w-full h-40 object-cover rounded border">
                    @endforeach

                </div>

            </div>
        @endif

    </div>
@endsection
