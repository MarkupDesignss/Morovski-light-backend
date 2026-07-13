<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;

class SearchController extends Controller
{
    /**
     * Global search across users, items and categories
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100'
        ]);

        $q = trim($request->get('q', ''));

        if ($q === '') {
            return response()->json([
                'users' => [],
                'items' => [],
                'categories' => []
            ]);
        }

        $like = "%{$q}%";

        // USERS
        $users = User::where(function ($query) use ($like) {
            $query->where('full_name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like);
        })
            ->limit(6)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->full_name,
                    'email' => $u->email,
                    'url' => route('admin.users.show', $u->id),
                ];
            });

        // ITEMS
        $items = Item::with('images')
            ->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like);
            })
            ->limit(6)
            ->get()
            ->map(function ($it) {
                return [
                    'id' => $it->id,
                    'title' => $it->title,
                    'price' => $it->price,
                    'image' => $it->images->first()?->image_path,
                    'url' => route('admin.items.show', $it->id),
                ];
            });

        // CATEGORIES
        $categories = Category::where('name', 'like', $like)
            ->limit(6)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'url' => route('admin.categories.edit', $c->id),
                ];
            });

        return response()->json([
            __('admin.users') => $users,
            __('admin.items')  => $items,
            __('admin.categories')  => $categories
        ]);
    }
}
