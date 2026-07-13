@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h2 style="color:#2f5365" class="text-3xl font-bold">Create FAQs</h2>
                <p class="mt-2 text-sm" style="color: #4B5565;">Add frequently asked questions for your website.</p>
            </div>
            <div>
                <a href="{{ route('admin.faq.index') }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg transition-all duration-300"
                    style="border: 1px solid #162E38; color: #162E38; background: transparent;">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to FAQs
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
                                <h3 class="text-sm font-medium" style="color: #dc3545;">Please fix the following errors:
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

                <form method="POST" action="{{ route('admin.faq.store') }}">
                    @csrf

                    <div id="faq-wrapper">
                        <div class="faq-item mb-4 border rounded-lg p-4 relative" style="border-color: #e5e7eb;">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-medium" style="color: #162E38;">FAQ #1</h3>
                            </div>
                            <input type="text" name="faqs[0][question]" placeholder="Enter Question"
                                class="w-full border rounded-lg px-4 py-2 mb-3 focus:outline-none focus:ring-2 transition-all"
                                style="border-color: #e5e7eb;">
                            <input type="text" name="faqs[0][question_de]" placeholder="Enter Question (DE)"
                                class="w-full border rounded-lg px-4 py-2 mb-3 focus:outline-none focus:ring-2 transition-all"
                                style="border-color: #e5e7eb;">
                            <textarea name="faqs[0][answer]" placeholder="Enter Answer" rows="3"
                                class="w-full border rounded-lg px-4 py-2 mb-3 focus:outline-none focus:ring-2 transition-all"
                                style="border-color: #e5e7eb;"></textarea>
                            <textarea name="faqs[0][answer_de]" placeholder="Enter Answer (DE)" rows="3"
                                class="w-full border rounded-lg px-4 py-2 mb-3 focus:outline-none focus:ring-2 transition-all"
                                style="border-color: #e5e7eb;"></textarea>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2">
                                    Sort order:
                                    <input type="number" name="faqs[0][sort_order]" value="0"
                                        class="w-24 border rounded-lg px-2 py-1 focus:outline-none focus:ring-2 transition-all"
                                        style="border-color: #e5e7eb;">
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="hidden" name="faqs[0][is_active]" value="0">
                                    <input type="checkbox" name="faqs[0][is_active]" value="1" checked>
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="addFaq()"
                        class="inline-flex items-center px-4 py-2 rounded-lg transition-all duration-300 mb-4"
                        style="background: rgba(22, 46, 56, 0.1); color: #162E38;">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add More FAQ
                    </button>

                    <div class="flex justify-end gap-3 pt-4 border-t" style="border-color: #e5e7eb;">
                        <a href="{{ route('admin.faq.index') }}" class="px-6 py-2 rounded-lg transition-all duration-300"
                            style="border: 1px solid #e5e7eb; color: #6B7280; background: transparent;">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-2 rounded-lg text-white shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"
                            style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Save FAQs
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
        </style>
    @endpush

    @push('scripts')
        <script>
            let index = 1;

            function addFaq() {
                let html = `
                    <div class="faq-item mb-4 border rounded-lg p-4 relative" style="border-color: #e5e7eb;">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-medium" style="color: #162E38;">FAQ #${index + 1}</h3>
                            <button type="button" onclick="removeFaq(this)"
                                    class="text-white rounded-full w-6 h-6 flex items-center justify-center transition-all hover:scale-110"
                                    style="background: #dc3545;">
                                ×
                            </button>
                        </div>
                        <input type="text" name="faqs[${index}][question]"
                               placeholder="Enter Question"
                               class="w-full border rounded-lg px-4 py-2 mb-3 focus:outline-none focus:ring-2 transition-all"
                               style="border-color: #e5e7eb;">
                        <input type="text" name="faqs[${index}][question_de]" placeholder="Enter Question (DE)"
                               class="w-full border rounded-lg px-4 py-2 mb-3 focus:outline-none focus:ring-2 transition-all"
                               style="border-color: #e5e7eb;">
                        <textarea name="faqs[${index}][answer]"
                                  placeholder="Enter Answer"
                                  rows="3"
                                  class="w-full border rounded-lg px-4 py-2 mb-3 focus:outline-none focus:ring-2 transition-all"
                                  style="border-color: #e5e7eb;"></textarea>
                        <textarea name="faqs[${index}][answer_de]" placeholder="Enter Answer (DE)" rows="3"
                                  class="w-full border rounded-lg px-4 py-2 mb-3 focus:outline-none focus:ring-2 transition-all"
                                  style="border-color: #e5e7eb;"></textarea>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2">
                                Sort order:
                                <input type="number" name="faqs[${index}][sort_order]" value="${index}"
                                       class="w-24 border rounded-lg px-2 py-1 focus:outline-none focus:ring-2 transition-all"
                                       style="border-color: #e5e7eb;">
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="hidden" name="faqs[${index}][is_active]" value="0">
                                <input type="checkbox" name="faqs[${index}][is_active]" value="1" checked>
                                Active
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
                        heading.textContent = `FAQ #${idx + 1}`;
                    }
                    const questionInput = item.querySelector('input[name$="[question]"]');
                    const questionDeInput = item.querySelector('input[name$="[question_de]"]');
                    const answerTextarea = item.querySelector('textarea[name$="[answer]"]');
                    const answerDeTextarea = item.querySelector('textarea[name$="[answer_de]"]');
                    const sortInput = item.querySelector('input[name$="[sort_order]"]');
                    const isActiveHidden = item.querySelector('input[type="hidden"][name$="[is_active]"]');
                    const isActiveCheckbox = item.querySelector('input[type="checkbox"][name$="[is_active]"]');
                    if (questionInput) questionInput.name = `faqs[${idx}][question]`;
                    if (questionDeInput) questionDeInput.name = `faqs[${idx}][question_de]`;
                    if (answerTextarea) answerTextarea.name = `faqs[${idx}][answer]`;
                    if (answerDeTextarea) answerDeTextarea.name = `faqs[${idx}][answer_de]`;
                    if (sortInput) sortInput.name = `faqs[${idx}][sort_order]`;
                    if (isActiveHidden) isActiveHidden.name = `faqs[${idx}][is_active]`;
                    if (isActiveCheckbox) isActiveCheckbox.name = `faqs[${idx}][is_active]`;
                });
            }
        </script>
    @endpush
@endsection
