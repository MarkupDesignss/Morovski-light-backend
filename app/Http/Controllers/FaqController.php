<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('sort_order')->get();
        return view('admin.faq.index', compact('faqs'));
    }

    public function create()
    {
        return view('admin.faq.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'faqs.*.question' => 'required',
            'faqs.*.answer' => 'required',
        ]);

        foreach ($request->faqs as $index => $faq) {
            Faq::create([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'sort_order' => $index,
                'is_active' => 1,
            ]);
        }

        return redirect()->route('admin.faq.index')
            ->with('success', 'FAQs added successfully');
    }

    public function edit()
    {
        $faqs = Faq::orderBy('sort_order')->get();
        return view('admin.faq.edit', compact('faqs'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'faqs.*.question' => 'required',
            'faqs.*.answer' => 'required',
        ]);

        // purge old
        Faq::truncate();

        foreach ($request->faqs as $index => $faq) {
            Faq::create([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'sort_order' => $index,
                'is_active' => 1,
            ]);
        }

        return redirect()->route('admin.faq.index')
            ->with('success', 'FAQs updated successfully');
    }
}
