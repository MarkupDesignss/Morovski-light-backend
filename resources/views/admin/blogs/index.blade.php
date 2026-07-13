@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">

        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">{{ __('admin.blog_management_title') }}</h2>

            <a href="{{ route('admin.blogs.create') }}"
                class="bg-black hover:bg-gray-800 text-white px-4 py-2 rounded-md transition duration-200 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('admin.add_new_blog_btn') }}
            </a>
        </div>

        <!-- Blogs Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('admin.table_heading') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('admin.table_category') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('admin.table_actions') }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">

                        @forelse ($blogs as $blog)
                            <tr class="hover:bg-gray-50 transition duration-150">

                                {{-- TITLE (first entry safe way) --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $blog->heading ?? '_' }}
                                    </div>
                                </td>

                                {{-- CATEGORY --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $blog->category->name ?? __('admin.uncategorized') }}
                                    </span>
                                </td>

                                {{-- ACTIONS --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">

                                        <a href="{{ route('admin.blogs.show', $blog->id) }}"
                                            class="text-blue-600 hover:text-blue-900 transition duration-150">
                                            {{ __('admin.view_btn') }}
                                        </a>

                                        <a href="{{ route('admin.blogs.edit', $blog->id) }}"
                                            class="text-green-600 hover:text-green-900 transition duration-150">
                                            {{ __('admin.edit_btn') }}
                                        </a>

                                        <form method="POST" action="{{ route('admin.blogs.destroy', $blog->id) }}"
                                            class="inline-block"
                                            onsubmit="return confirm('{{ __('admin.delete_confirm_msg') }}')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900 transition duration-150">
                                                {{ __('admin.delete_btn') }}
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500">

                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                        </path>
                                    </svg>

                                    <p class="mt-2">{{ __('admin.no_blogs_found') }}</p>

                                    <a href="{{ route('admin.blogs.create') }}"
                                        class="mt-3 inline-block text-black hover:text-gray-600">
                                        {{ __('admin.create_first_blog_link') }} →
                                    </a>

                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($blogs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $blogs->links() }}
                </div>
            @endif

        </div>
    </div>
@endsection
