<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::with('category')->latest()->paginate(10);
        return view('admin.blogs.index', compact('blogs'));
    }
    public function create()
    {
        $categories = Category::where('is_active', 1)->get();
        return view('admin.blogs.create', compact('categories'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'heading'    => 'required_without:heading_de|string',
            'heading_de' => 'required_without:heading|string',
            'entries' => 'required|array',
            'entries.*.title' => 'required|string',
            'entries.*.description' => 'required|string',

            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        DB::beginTransaction();
        try {
            $blog = Blog::create([
                'category_id' => $request->category_id,
                'heading' => $request->heading,
                'heading_de' => $request->heading_de,
                'slug'        => Str::slug($request->heading) . '-' . time(),
                'entries' => $request->entries,
                'is_active' => $request->is_active ?? 1,
                'sort_order' => $request->sort_order ?? 0,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('blogs', 'public');

                    $blog->images()->create([
                        'image' => $path
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.blogs.index')
                ->with('success', 'Blog created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }
    public function show(Blog $blog)
    {
        $blog->load('images', 'category');
        return view('admin.blogs.show', compact('blog'));
    }
    public function edit(Blog $blog)
    {
        $categories = Category::get();
        $blog->load('images');

        return view('admin.blogs.edit', compact('blog', 'categories'));
    }
    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',

            'entries' => 'required|array',
            'heading'    => 'required_without:heading_de|string',
            'heading_de' => 'required_without:heading|string',
            'entries.*.title' => 'required|string',
            'entries.*.description' => 'required|string',

            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        DB::beginTransaction();

        try {
            $blog->update([
                'category_id' => $request->category_id ?? $blog->category_id,
                'heading' => $request->heading ?? $blog->heading,
                'heading_de' => $request->heading_de ?? $blog->heading_de,
                'entries' => $request->entries ?? $blog->entries,
                'is_active' => $request->is_active ?? $blog->is_active,
                'sort_order' => $request->sort_order ?? $blog->sort_order,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('blogs', 'public');

                    $blog->images()->create([
                        'image' => $path
                    ]);
                }
            }

            if ($request->deleted_images) {
                foreach ($request->deleted_images as $imgId) {
                    $img = BlogImage::find($imgId);

                    if ($img) {
                        Storage::disk('public')->delete($img->image);
                        $img->delete();
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.blogs.index')
                ->with('success', 'Blog updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }
    public function destroy(Blog $blog)
    {
        DB::beginTransaction();

        try {
            // delete images from storage
            foreach ($blog->images as $img) {
                Storage::disk('public')->delete($img->image);
            }

            $blog->delete();

            DB::commit();

            return back()->with('success', 'Blog deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }
    public function deleteImage($id)
    {
        try {
            $image = BlogImage::findOrFail($id);

            // delete file from storage
            if ($image->image) {
                Storage::disk('public')->delete($image->image);
            }

            $image->delete();

            return response()->json([
                'status' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function apiIndex()
    {
        $blogs = Blog::with('category', 'images')
            ->where('is_active', 1)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => __('messages.blogs_fetched_successfully'),
            'data' => $blogs
        ]);
    }

    public function apiShow($slug)
    {
        $blog = Blog::with('category', 'images')
            ->where('slug', $slug)
            ->first();

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => __('messages.blog_not_found')
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => __('messages.blogs_fetched_successfully'),
            'data' => $blog
        ]);
    }
}
