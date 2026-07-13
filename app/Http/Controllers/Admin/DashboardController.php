<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\Item;

class DashboardController extends Controller
{

    public function dashboard()
    {
        $totalUsers = User::where('is_registered','1')->count();
        $totalItems = Item::count();
        $totalOrders = Order::count();
            
        $Category = Category::count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalItems',
            'totalOrders',
            'Category'
        ));
    }
}
