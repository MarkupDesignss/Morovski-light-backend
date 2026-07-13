@extends('layouts.admin')

@section('title', 'Manage Categories')

@section('content')
    <div class="space-y-8 min-h-screen p-6">

        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div id="success-message" class="relative mb-4 overflow-hidden rounded-xl p-4 shadow-lg"
                style="background: linear-gradient(90deg, #160c00, #3a2819); color: white; border: 1px solid rgba(255,255,255,0.08);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="mr-3 rounded-full bg-amber-800/40 p-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold">{{ session('success') }}</p>
                            <p class="text-sm text-white/80">Operation completed successfully</p>
                        </div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-white/80 hover:text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="absolute bottom-0 left-0 h-1 w-full" style="background: rgba(255,255,255,0.06)">
                    <div class="h-full bg-amber-400 progress-bar"></div>
                </div>
            </div>
        @endif

        {{-- HEADER --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold"
                    style="

        color: #2a1a05;
        font-family: Georgia, serif;
        margin: 0;">
                    {{ __('admin.categories') }}</h2>
                <p class="mt-2 text-sm text-stone-600">{{ __('admin.manage_categories') }}</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="px-4 py-2 rounded-lg border text-white"
                    style="

        color: #2a1a05;
        font-family: Georgia, serif;
        margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                    <div class="text-sm">{{ __('admin.total_categories') }}: {{ $categories->count() }}</div>
                </div>
                <a href="{{ route('admin.categories.create') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"
                    style="

        color: #2a1a05;
        font-family: Georgia, serif;
        margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('admin.add_new_category') }}
                </a>
            </div>
        </div>

        {{-- STATS CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-stone-500">{{ __('admin.total_categories') }}</p>
                        <p class="text-2xl font-bold mt-1" style="color: #160c00;">{{ $categories->count() }}</p>
                    </div>
                    <div class="p-3 rounded-xl" style="background: rgba(22, 12, 0, 0.08);">
                        <svg class="w-6 h-6" style="color: #3a2819;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-stone-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-stone-500">{{ __('admin.active_categories') }}</p>
                        <p class="text-2xl font-bold mt-1 text-emerald-600">
                            {{ $categories->where('is_active', true)->count() }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- CATEGORIES TABLE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-stone-100" style="background: rgba(22, 12, 0, 0.02);">
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">Sr. No.</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">
                                {{ __('admin.images') }}
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">
                                {{ __('admin.category_name') }}
                            </th>
                            <!--<th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">Parent Category-->
                            <!--</th>-->
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">
                                {{ __('admin.Status') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">
                                {{ __('admin.sort_order') }}</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold" style="color: #160c00;">
                                {{ __('admin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($categories as $category)
                            <tr class="hover:bg-stone-50 transition-colors duration-200">
                                <td class="px-6 py-4 text-sm font-medium" style="color: #160c00;">{{ $loop->iteration }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($category->image)
                                        <img src="{{ $category->image }}"
                                             class="h-10 w-10 rounded-lg object-cover border">
                                    @else
                                        <div class="h-10 w-10 flex items-center justify-center bg-stone-100 rounded-lg text-xs text-stone-400">
                                            N/A
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-lg flex items-center justify-center text-white text-xs mr-3"
                                            style="background: linear-gradient(135deg, #3a2819, #160c00);">
                                            {{ strtoupper(substr($category->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-stone-800">{{ $category->name }}</p>
                                            <p class="text-xs text-stone-400">Slug: {{ Str::slug($category->name) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <!--<td class="px-6 py-4">-->
                                <!--    @if ($category->parent)-->
                                <!--        <div class="flex items-center">-->
                                <!--            <svg class="w-4 h-4 mr-2" style="color: #3a2819;" fill="none"-->
                                <!--                stroke="currentColor" viewBox="0 0 24 24">-->
                                <!--                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"-->
                                <!--                    d="M3 10h18M3 14h18"></path>-->
                                <!--            </svg>-->
                                <!--            <span class="text-sm text-stone-600">{{ $category->parent->name }}</span>-->
                                <!--        </div>-->
                                <!--    @else-->
                                <!--        <span-->
                                <!--            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-stone-100 text-stone-600">-->
                                <!--            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"-->
                                <!--                viewBox="0 0 24 24">-->
                                <!--                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"-->
                                <!--                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">-->
                                <!--                </path>-->
                                <!--            </svg>-->
                                <!--            Root Category-->
                                <!--        </span>-->
                                <!--    @endif-->
                                <!--</td>-->
                                <td class="px-6 py-4">
                                    @if ($category->is_active)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-2 h-2 mr-1 bg-emerald-500 rounded-full"></span>
                                            {{ __('admin.active') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-2 h-2 mr-1 bg-rose-500 rounded-full"></span>
                                            {{ __('admin.inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium"
                                            style="color: #160c00;">{{ $category->sort_order ?? 0 }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}"
                                            class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-white transition-all duration-300 shadow-sm hover:shadow"
                                            style="

        color: #2a1a05;
        font-family: Georgia, serif;
        margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                            {{ __('admin.edit') }}
                                        </a>

                                        <button type="button"
                                            class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 transition-all duration-300 shadow-sm hover:shadow delete-btn"
                                            data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                                            style="

        color: #2a1a05;
        font-family: Georgia, serif;
        margin: 0;">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                            {{ __('admin.delete') }}
                                        </button>

                                        <form id="delete-form-{{ $category->id }}"
                                            action="{{ route('admin.categories.destroy', $category->id) }}"
                                            method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 rounded-full flex items-center justify-center mb-4"
                                            style="background: rgba(22, 12, 0, 0.08);">
                                            <svg class="w-10 h-10" style="color: #3a2819;" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                                </path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-stone-800 mb-1">No Categories Found</h3>
                                        <p class="text-sm text-stone-500 mb-4">Get started by creating your first category
                                        </p>
                                        <a href="{{ route('admin.categories.create') }}"
                                            class="inline-flex items-center px-4 py-2 rounded-lg text-white shadow-md"
                                            style="

        color: #2a1a05;
        font-family: Georgia, serif;
        margin: 0; background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add First Category
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($categories->hasPages())
                <div class="px-6 py-4 border-t border-stone-100">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.transition = 'opacity 0.5s ease';
                    successMessage.style.opacity = '0';
                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 500);
                }, 3000);
            }

            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const categoryId = this.dataset.id;
                    const categoryName = this.dataset.name;

                    Swal.fire({
                        title: 'Delete Category',
                        html: `
                            <div class="text-center">
                                <p class="text-md text-stone-700 mb-2">Are you sure you want to delete category:
                                    <span class="font-bold" style="color: #160c00;">"${categoryName}"</span>?
                                </p>
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-left">
                                    <p class="text-xs text-amber-700">
                                        <span class="font-bold">⚠️ Warning:</span> This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DC2626',
                        cancelButtonColor: '#78716C',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        customClass: {
                            confirmButton: 'px-4 py-2 rounded-lg text-white font-medium',
                            cancelButton: 'px-4 py-2 rounded-lg text-stone-700 bg-stone-200 font-medium hover:bg-stone-300',
                            popup: 'rounded-2xl'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Deleting...',
                                html: 'Please wait while we delete the category.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            document.getElementById(`delete-form-${categoryId}`).submit();
                        }
                    });
                });
            });
        });

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-2xl'
                }
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                customClass: {
                    popup: 'rounded-2xl'
                }
            });
        @endif
    </script>
@endsection
