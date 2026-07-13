<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class CategoryController extends Controller
{
    private function generateUniqueSlug($name, $id = null)
    {
        $slug = Str::slug($name);
    
        $originalSlug = $slug;
    
        $count = 1;
    
        while (
            Category::where('slug', $slug)
                ->when($id, function ($query) use ($id) {
                    $query->where('id', '!=', $id);
                })
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
    
        return $slug;
    }
    public function index()
    {
        $categories = Category::with('parent')
            ->orderBy('sort_order')
            ->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }


    public function create()
    {
        $parents = Category::whereNull('parent_id')->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|min:0|unique:categories,sort_order',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp,avif',
        ]);
    
        /*
        |--------------------------------------------------------------------------
        | Generate unique slug
        |--------------------------------------------------------------------------
        */
        $slug = Str::slug($request->name);
    
        // Check if slug exists
        $slugExists = Category::where('slug', $slug)->exists();
    
        if ($slugExists) {
            // Get all existing slugs that start with the base slug
            $existingSlugs = Category::where('slug', 'LIKE', $slug . '%')
                ->pluck('slug')
                ->toArray();
            
            $maxNumber = 0;
            foreach ($existingSlugs as $existingSlug) {
                // Check if slug matches pattern: slug, slug-1, slug-2, etc.
                if ($existingSlug === $slug) {
                    $maxNumber = max($maxNumber, 1);
                } elseif (preg_match('/^' . preg_quote($slug) . '-(\d+)$/', $existingSlug, $matches)) {
                    $maxNumber = max($maxNumber, (int)$matches[1] + 1);
                }
            }
            
            // If no numbered slug exists, start with 1
            if ($maxNumber === 0) {
                $maxNumber = 1;
            }
            
            $slug = $slug . '-' . $maxNumber;
        }
    
        $imagePath = null;
    
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('categories', 'public');
        }
    
        Category::create([
            'name' => $request->name,
            'slug' => $slug,
            'parent_id' => $request->parent_id ?? null,
            'is_active' => $request->is_active ?? 1,
            'sort_order' => $request->sort_order ?? 0,
            'image' => $imagePath
        ]);
    
        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully');
    }   

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required',
    //         'parent_id' => 'nullable|exists:categories,id',
    //         'sort_order' => 'nullable|min:0|unique:categories,sort_order',
    //         'image' => 'required|image|mimes:jpg,jpeg,png,webp,avif',
    //     ]);
    
    //     /*
    //     |--------------------------------------------------------------------------
    //     | Check Duplicate Slug
    //     |--------------------------------------------------------------------------
    //     */
    //     $slug = Str::slug($request->name);
    
    //     $slugExists = Category::where('slug', $slug)->exists();
    
    //     if ($slugExists) {
    
    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'name' => 'Category with same slug already exists.'
    //             ]);
    //     }
    
    //     $imagePath = null;
    
    //     if ($request->hasFile('image')) {
    
    //         $imagePath = $request->file('image')
    //             ->store('categories', 'public');
    //     }
    
    //     Category::create([
    //         'name' => $request->name,
    //         'slug' => $slug,
    //         'parent_id' => $request->parent_id ?? null,
    //         'is_active' => $request->is_active ?? 1,
    //         'sort_order' => $request->sort_order ?? 0,
    //         'image' => $imagePath
    //     ]);
    
    //     return redirect()
    //         ->route('admin.categories.index')
    //         ->with('success', 'Category created successfully');
    // }



    public function edit($id)
    {
        $category = Category::findOrFail($id);

        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $id)
            ->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
    
        $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'required|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif',
        ]);
    
        /*
        |--------------------------------------------------------------------------
        | Check duplicate sort order
        |--------------------------------------------------------------------------
        */
        $sortOrderExists = Category::where('sort_order', $request->sort_order)
            ->where('id', '!=', $id)
            ->exists();
    
        if ($sortOrderExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'sort_order' => 'This sort order is already assigned to another category.'
                ]);
        }
    
        /*
        |--------------------------------------------------------------------------
        | Generate unique slug
        |--------------------------------------------------------------------------
        */
        $slug = Str::slug($request->name);
        
        // Check if slug exists for other categories
        $slugExists = Category::where('slug', $slug)
            ->where('id', '!=', $id)
            ->exists();
        
        if ($slugExists) {
            // Get all existing slugs to find the highest number
            $existingSlugs = Category::where('slug', 'LIKE', $slug . '%')
                ->where('id', '!=', $id)
                ->pluck('slug')
                ->toArray();
            
            $maxNumber = 0;
            foreach ($existingSlugs as $existingSlug) {
                // Check if slug matches pattern: slug, slug-1, slug-2, etc.
                if ($existingSlug === $slug) {
                    $maxNumber = max($maxNumber, 1);
                } elseif (preg_match('/^' . preg_quote($slug) . '-(\d+)$/', $existingSlug, $matches)) {
                    $maxNumber = max($maxNumber, (int)$matches[1] + 1);
                }
            }
            
            // If no numbered slug exists, start with 1
            if ($maxNumber === 0) {
                $maxNumber = 1;
            }
            
            $slug = $slug . '-' . $maxNumber;
        }
    
        /*
        |--------------------------------------------------------------------------
        | Get original image path from DB
        |--------------------------------------------------------------------------
        */
        $imagePath = $category->getRawOriginal('image');
    
        /*
        |--------------------------------------------------------------------------
        | Upload new image
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('image')) {
            // delete old image
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
    
            // store new image
            $imagePath = $request->file('image')->store('categories', 'public');
        }
    
        /*
        |--------------------------------------------------------------------------
        | Update category
        |--------------------------------------------------------------------------
        */
        $category->update([
            'name'       => $request->name,
            'slug'       => $slug,
            'parent_id'  => $request->parent_id,
            'is_active'  => $request->is_active ?? 1,
            'sort_order' => $request->sort_order,
            'image'      => $imagePath,
        ]);
    
        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully');
    }


    // public function update(Request $request, $id)
    // {
    //     $category = Category::findOrFail($id);
    
    //     $request->validate([
    //         'name' => 'required',
    //         'parent_id' => 'nullable|exists:categories,id',
    //         'sort_order' => 'required|min:0',
    //         'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif',
    //     ]);
    
    //     /*
    //     |--------------------------------------------------------------------------
    //     | Check duplicate sort order
    //     |--------------------------------------------------------------------------
    //     */
    //     $sortOrderExists = Category::where('sort_order', $request->sort_order)
    //         ->where('id', '!=', $id)
    //         ->exists();
    
    //     if ($sortOrderExists) {
    //         return back()
    //             ->withInput()
    //             ->withErrors([
    //                 'sort_order' => 'This sort order is already assigned to another category.'
    //             ]);
    //     }
    
    //     /*
    //     |--------------------------------------------------------------------------
    //     | Get original image path from DB
    //     |--------------------------------------------------------------------------
    //     */
    //     $imagePath = $category->getRawOriginal('image');
    
    //     /*
    //     |--------------------------------------------------------------------------
    //     | Upload new image
    //     |--------------------------------------------------------------------------
    //     */
    //     if ($request->hasFile('image')) {
    
    //         // delete old image
    //         if ($imagePath && Storage::disk('public')->exists($imagePath)) {
    //             Storage::disk('public')->delete($imagePath);
    //         }
    
    //         // store new image
    //         $imagePath = $request->file('image')->store('categories', 'public');
    //     }
    
    //     /*
    //     |--------------------------------------------------------------------------
    //     | Update category
    //     |--------------------------------------------------------------------------
    //     */
    //      $slug = Str::slug($request->name);
    //     $category->update([
    //         'name'       => $request->name,
    //         'slug'       => $slug,
    //         'parent_id'  => $request->parent_id,
    //         'is_active'  => $request->is_active ?? 1,
    //         'sort_order' => $request->sort_order,
    //         'image'      => $imagePath,
    //     ]);
    
    //     return redirect()
    //         ->route('admin.categories.index')
    //         ->with('success', 'Category updated successfully');
    // }
    
    


    public function destroy($id)
    {
        $category = Category::with('items.images')->findOrFail($id);

        foreach ($category->items as $item) {

            foreach ($item->images as $image) {

                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }

                $image->delete();
            }

            $item->delete();
        }

        $category->delete();

        return redirect()->back()
            ->with('success', 'Category and related items deleted successfully');
    }
}
