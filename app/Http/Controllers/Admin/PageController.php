<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->paginate(10);
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'title' => 'required',
    //         'heading' => 'nullable|string',
    //         'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp',

    //         'main_button.text' => 'nullable|string',
    //         'sub_buttons.*.text' => 'nullable|string',
    //     ]);

    //     $page = Page::create([
    //         'title' => $request->title,
    //         'slug' => Str::slug($request->title),
    //         'content' => $request->content,
    //         'heading' => $request->heading,
    //         'meta_title' => $request->meta_title,
    //         'meta_description' => $request->meta_description,
    //         'is_active' => $request->has('is_active'),

    //         'main_button' => $request->main_button,
    //         'sub_buttons' => $request->sub_buttons,
    //     ]);

    //     // images same as before
    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $file) {
    //             $path = $file->store('pages', 'public');

    //             $page->images()->create([
    //                 'image' => $path,
    //             ]);
    //         }
    //     }

    //     return redirect()->route('admin.pages.index')
    //         ->with('success', 'Page created successfully');
    // }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'heading' => 'nullable|string',
    
            // Images + Videos
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi,webm|max:51200',
    
            'main_button.text' => 'nullable|string',
            'sub_buttons.*.text' => 'nullable|string',
        ]);
    
        $page = Page::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'heading' => $request->heading,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'is_active' => $request->has('is_active'),
    
            'main_button' => $request->main_button,
            'sub_buttons' => $request->sub_buttons,
        ]);
    
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
    
                if ($file && $file->isValid()) {
    
                    $path = $file->store('pages', 'public');
    
                    $type = str_starts_with($file->getMimeType(), 'video/')
                        ? 'video'
                        : 'image';
    
                    $page->images()->create([
                        'image' => $path,
                        'type'  => $type,
                    ]);
                }
            }
        }
    
        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page created successfully');
    }

    public function edit(Page $page)
    {
        $page->load('images');
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'required',
            'heading' => 'nullable|string',
    
            // Images + Videos
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi,webm|max:51200',
    
            'main_button.text' => 'nullable|string',
            'sub_buttons.*.text' => 'nullable|string',
        ]);
    
        $page->update([
            'title' => $request->title,
            'slug' => $page->slug, // Keep existing slug
            // 'slug' => Str::slug($request->title), // Uncomment if slug should update
    
            'content' => $request->content,
            'heading' => $request->heading,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'is_active' => $request->has('is_active'),
    
            'main_button' => $request->main_button,
            'sub_buttons' => $request->sub_buttons,
        ]);
    
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
    
                if ($file && $file->isValid()) {
    
                    $path = $file->store('pages', 'public');
    
                    $type = str_starts_with($file->getMimeType(), 'video/')
                        ? 'video'
                        : 'image';
    
                    $page->images()->create([
                        'image' => $path,
                        'type'  => $type,
                    ]);
                }
            }
        }
    
        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Page updated successfully');
    }
    // public function update(Request $request, Page $page)
    // {
    //     $request->validate([
    //         'title' => 'required',
    //         'heading' => 'nullable|string',
    //         'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp',

    //         'main_button.text' => 'nullable|string',
    //         'sub_buttons.*.text' => 'nullable|string',
    //     ]);

    //     $page->update([
    //         'title' => $request->title ?? $page->title,
    //         'slug' => $page->slug,
    //         // 'slug' => Str::slug($request->title),
    //         'content' => $request->content ?? $page->content,
    //         'heading' => $request->heading ?? $page->heading,
    //         'meta_title' => $request->meta_title ?? $page->meta_title ?? '',
    //         'meta_description' => $request->meta_description ?? $page->meta_description,
    //         'is_active' => $request->has('is_active') ?? $page->is_active,

    //         'main_button' => $request->main_button,
    //         'sub_buttons' => $request->sub_buttons,
    //     ]);

    //     // images same
    //     if ($request->hasFile('images') && is_array($request->file('images'))) {
    //         foreach ($request->file('images') as $file) {
    //             if ($file && $file->isValid()) {
    //                 $path = $file->store('pages', 'public');

    //                 $page->images()->create([
    //                     'image' => $path,
    //                 ]);
    //             }
    //         }
    //     }

    //     return redirect()->route('admin.pages.index')
    //         ->with('success', 'Page updated successfully');
    // }

    public function destroy(Page $page)
    {
        //  delete images from storage also
        foreach ($page->images as $img) {
            Storage::disk('public')->delete($img->image);
        }

        $page->delete();

        return back()->with('success', 'Page deleted successfully');
    }
    public function deleteImage(PageImage $image, Request $request)
    {
        try {
            // Delete from storage
            if (Storage::disk('public')->exists($image->image)) {
                Storage::disk('public')->delete($image->image);
            }

            // Store image info before deletion for response
            $imageId = $image->id;

            // Delete from DB
            $image->delete();

            // Check if request expects JSON response (AJAX)
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Image deleted successfully',
                    'image_id' => $imageId
                ]);
            }

            // Return redirect for traditional form submission
            return back()->with('success', 'Image deleted successfully');
        } catch (\Exception $e) {
            // Log the error
            Log::error('Image deletion failed: ' . $e->getMessage());

            // Return error response based on request type
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete image: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to delete image: ' . $e->getMessage());
        }
    }
}
