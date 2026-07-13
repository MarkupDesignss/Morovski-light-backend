<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HeaderMenu;

class HeaderMenuController extends Controller
{
    public function index()
    {
        $logo = HeaderMenu::where('type', 'logo')->first();
        $menus = HeaderMenu::where('type', 'menu')->orderBy('sort_order')->get();

        return view('admin.header.index', compact('logo', 'menus'));
    }

    public function edit()
    {
        $logo = HeaderMenu::where('type', 'logo')->first();
        $menus = HeaderMenu::where('type', 'menu')->orderBy('sort_order')->get();

        return view('admin.header.edit', compact('logo', 'menus'));
    }

    // public function update(Request $request)
    // {
    //     // ================= LOGO =================
    //     if ($request->hasFile('logo')) {
    //         $path = $request->file('logo')->store('logos', 'public');

    //         HeaderMenu::updateOrCreate(
    //             ['type' => 'logo'],
    //             [
    //                 'logo' => $path,
    //                 'status' => 1
    //             ]
    //         );
    //     }

    //     // ================= MENUS =================
    //     HeaderMenu::where('type', 'menu')->delete();

    //     if ($request->menus) {
    //         foreach ($request->menus as $index => $menu) {
    //             if (!empty($menu['title'])) {
    //                 HeaderMenu::create([
    //                     'type' => 'menu',
    //                     'title' => $menu['title'],
    //                     'sort_order' => $index,
    //                     'status' => isset($menu['status']) ? 1 : 0,
    //                 ]);
    //             }
    //         }
    //     }

    //     return redirect()->route('admin.header.index')->with('success', 'Updated successfully');
    // }
    
    public function update(Request $request)
    {
        $data = [];
    
        // Logo Upload
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }
    
        // Favicon Upload
        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('favicons', 'public');
        }
    
        if (!empty($data)) {
            $data['status'] = 1;
    
            HeaderMenu::updateOrCreate(
                ['type' => 'logo'],
                $data
            );
        }
    
        // MENUS
        HeaderMenu::where('type', 'menu')->delete();
    
        if ($request->menus) {
            foreach ($request->menus as $index => $menu) {
                if (!empty($menu['title'])) {
                    HeaderMenu::create([
                        'type'       => 'menu',
                        'title'      => $menu['title'],
                        'sort_order' => $index,
                        'status'     => isset($menu['status']) ? 1 : 0,
                    ]);
                }
            }
        }
    
        return redirect()
            ->route('admin.header.index')
            ->with('success', 'Updated successfully');
    }
}