@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 py-4 sm:py-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 style="color:#2f5365" class="text-2xl sm:text-3xl font-bold">{{ __('admin.edit_faqs_title') }}</h2>
                <p class="mt-1 sm:mt-2 text-xs sm:text-sm" style="color: #4B5565;">{{ __('admin.edit_faqs_subtitle') }}</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('admin.faq.index') }}"
                    class="inline-flex items-center px-3 sm:px-4 py-2 rounded-lg transition-all duration-300 text-sm sm:text-base"
                    style="border: 1px solid #162E38; color: #162E38; background: transparent;">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('admin.back_to_faqs_btn') }}
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-4 sm:p-6">
                <form method="POST" action="{{ route('admin.faq.update') }}">
                    @csrf
                    @method('PUT')

                    <div id="faq-wrapper">
                        @foreach ($faqs as $i => $faq)
                            <div class="faq-item mb-4 border rounded-lg p-3 sm:p-4 relative" style="border-color: #e5e7eb;">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-medium text-sm sm:text-base" style="color: #162E38;">{{ __('admin.faq_prefix') }}
                                        #{{ $loop->iteration }}</h3>
                                    <button type="button" onclick="removeFaq(this)"
                                        class="text-white rounded-full w-6 h-6 flex items-center justify-center transition-all hover:scale-110 flex-shrink-0"
                                        style="background: #dc3545;">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <label class="block text-xs sm:text-sm font-medium mb-1" style="color: #4B5565;">{{ __('admin.question_label') }}</label>
                                <input type="text" name="faqs[{{ $i }}][question]" 
                                    placeholder="{{ __('admin.question_placeholder') }}"
                                    value="{{ $faq->getRawOriginal('question') }}"
                                    class="w-full border rounded-lg px-3 sm:px-4 py-2 text-sm sm:text-base mb-3 focus:outline-none focus:ring-2 transition-all"
                                    style="border-color: #e5e7eb;">
                                
                                <label class="block text-xs sm:text-sm font-medium mb-1" style="color: #4B5565;">{{ __('admin.answer_label') }}</label>
                                <textarea name="faqs[{{ $i }}][answer]" rows="3"
                                    class="w-full border rounded-lg px-3 sm:px-4 py-2 text-sm sm:text-base mb-3 focus:outline-none focus:ring-2 transition-all"
                                    style="border-color: #e5e7eb;" 
                                    placeholder="{{ __('admin.answer_placeholder') }}">{{ $faq->getRawOriginal('answer') }}</textarea>
                                
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                                    <label class="flex items-center gap-2 text-sm">
                                        <span style="color: #4B5565;">{{ __('admin.sort_order_label') }}:</span>
                                        <input type="number" name="faqs[{{ $i }}][sort_order]"
                                            value="{{ $faq->sort_order ?? $i }}"
                                            class="w-20 sm:w-24 border rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 transition-all"
                                            style="border-color: #e5e7eb;">
                                    </label>
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="hidden" name="faqs[{{ $i }}][is_active]" value="0">
                                        <input type="checkbox" name="faqs[{{ $i }}][is_active]" value="1"
                                            {{ $faq->is_active ? 'checked' : '' }}
                                            class="w-4 h-4" style="accent-color: #162E38;">
                                        <span style="color: #4B5565;">{{ __('admin.active_label') }}</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" onclick="addFaq()"
                        class="inline-flex items-center px-3 sm:px-4 py-2 rounded-lg transition-all duration-300 mb-4 text-sm sm:text-base"
                        style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ __('admin.add_more_faq_btn') }}
                    </button>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t" style="border-color: #e5e7eb;">
                        <a href="{{ route('admin.faq.index') }}" 
                            class="px-4 sm:px-6 py-2 rounded-lg transition-all duration-300 text-center text-sm sm:text-base"
                            style="border: 1px solid #e5e7eb; color: #6B7280; background: transparent;">
                            {{ __('admin.cancel_btn') }}
                        </a>
                        <button type="submit"
                            class="px-4 sm:px-6 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02] text-sm sm:text-base"
                            style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                            <svg class="inline-block w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            {{ __('admin.update_faqs_btn') }}
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

            input:focus,
            textarea:focus {
                border-color: #162E38 !important;
                box-shadow: 0 0 0 2px rgba(22, 46, 56, 0.1) !important;
                outline: none;
            }

            .faq-item {
                transition: all 0.3s ease;
            }

            .faq-item:hover {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }

            /* Mobile optimizations */
            @media (max-width: 640px) {
                .container {
                    padding-left: 1rem;
                    padding-right: 1rem;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            let index = {{ count($faqs) }};

            function addFaq() {
                let html = `
                <div class="faq-item mb-4 border rounded-lg p-3 sm:p-4 relative" style="border-color: #e5e7eb;">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-medium text-sm sm:text-base" style="color: #162E38;">{{ __('admin.faq_prefix') }} #${index + 1}</h3>
                        <button type="button" onclick="removeFaq(this)"
                                class="text-white rounded-full w-6 h-6 flex items-center justify-center transition-all hover:scale-110 flex-shrink-0"
                                style="background: #dc3545;">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <label class="block text-xs sm:text-sm font-medium mb-1" style="color: #4B5565;">{{ __('admin.question_label') }}</label>
                    <input type="text" name="faqs[${index}][question]"
                           placeholder="{{ __('admin.question_placeholder') }}"
                           class="w-full border rounded-lg px-3 sm:px-4 py-2 text-sm sm:text-base mb-3 focus:outline-none focus:ring-2 transition-all"
                           style="border-color: #e5e7eb;">
                    
                    <label class="block text-xs sm:text-sm font-medium mb-1" style="color: #4B5565;">{{ __('admin.answer_label') }}</label>
                    <textarea name="faqs[${index}][answer]"
                              placeholder="{{ __('admin.answer_placeholder') }}"
                              rows="3"
                              class="w-full border rounded-lg px-3 sm:px-4 py-2 text-sm sm:text-base mb-3 focus:outline-none focus:ring-2 transition-all"
                              style="border-color: #e5e7eb;"></textarea>
                    
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                        <label class="flex items-center gap-2 text-sm">
                            <span style="color: #4B5565;">{{ __('admin.sort_order_label') }}:</span>
                            <input type="number" name="faqs[${index}][sort_order]" value="${index}"
                                   class="w-20 sm:w-24 border rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 transition-all"
                                   style="border-color: #e5e7eb;">
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="faqs[${index}][is_active]" value="0">
                            <input type="checkbox" name="faqs[${index}][is_active]" value="1" checked
                                   class="w-4 h-4" style="accent-color: #162E38;">
                            <span style="color: #4B5565;">{{ __('admin.active_label') }}</span>
                        </label>
                    </div>
                </div>
            `;
                document.getElementById('faq-wrapper').insertAdjacentHTML('beforeend', html);
                index++;
            }

            function removeFaq(btn) {
                btn.closest('.faq-item').remove();
                // Renumber remaining FAQs
                document.querySelectorAll('.faq-item').forEach((item, idx) => {
                    const heading = item.querySelector('h3');
                    if (heading) {
                        heading.textContent = `{{ __('admin.faq_prefix') }} #${idx + 1}`;
                    }
                    const questionInput = item.querySelector('input[name$="[question]"]');
                    const answerTextarea = item.querySelector('textarea[name$="[answer]"]');
                    const sortInput = item.querySelector('input[name$="[sort_order]"]');
                    const isActiveHidden = item.querySelector('input[type="hidden"][name$="[is_active]"]');
                    const isActiveCheckbox = item.querySelector('input[type="checkbox"][name$="[is_active]"]');
                    
                    if (questionInput) questionInput.name = `faqs[${idx}][question]`;
                    if (answerTextarea) answerTextarea.name = `faqs[${idx}][answer]`;
                    if (sortInput) sortInput.name = `faqs[${idx}][sort_order]`;
                    if (isActiveHidden) isActiveHidden.name = `faqs[${idx}][is_active]`;
                    if (isActiveCheckbox) isActiveCheckbox.name = `faqs[${idx}][is_active]`;
                });
                // Update index for next additions
                const remainingItems = document.querySelectorAll('.faq-item').length;
                index = remainingItems;
            }
        </script>
    @endpush
@endsection