<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\HowItWorks;
use App\Models\PlatformAdvantage;
use App\Models\Faq;
use App\Models\ContactSetting;
use App\Models\HeaderMenu;

class CmsController extends Controller
{
    public function index()
    {
        $pages = Page::with('images')->where('is_active', 1)
            // ->select('id', 'title', 'slug','heading','content')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $pages
        ]);
    }

    public function show($slug)
    {
        $page = Page::with('images')->where('slug', $slug)
            ->where('is_active', 1)
            ->first();

        if (!$page) {
            return response()->json([
                'status' => false,
                'message' => __('messages.page_not_found'),
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $page
        ]);
    }

    // public function howitworks()
    // {
    //     $rows = HowItWorks::orderBy('step_number', 'asc')
    //         ->get(['step_number', 'heading', 'subheading', 'title', 'description']);

    //     if ($rows->isEmpty()) {
    //         return response()->json([
    //             'status' => true,
    //             'data' => null
    //         ]);
    //     }

    //     // take heading/subheading from first row
    //     $heading = $rows->first()->heading;
    //     $subheading = $rows->first()->subheading;

    //     // map steps only
    //     $steps = $rows->map(function ($item) {
    //         return [
    //             'step_number' => (int) $item->step_number,
    //             'title' => $item->title,
    //             'description' => $item->description,
    //         ];
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'data' => [
    //             'heading' => $heading,
    //             'subheading' => $subheading,
    //             'steps' => $steps
    //         ]
    //     ]);
    // }

    public function howitworks()
    {
        $rows = HowItWorks::orderBy('step_number', 'asc')->get();

        if ($rows->isEmpty()) {
            return response()->json([
                'status' => true,
                'data' => null
            ]);
        }

        $first = $rows->first();

        $steps = $rows->map(function ($item) {
            return [
                'step_number' => (int) $item->step_number,
                'title' => $item->title,
                'description' => $item->description,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => [
                'heading' => $first->heading,
                'subheading' => $first->subheading,
                'steps' => $steps
            ]
        ]);
    }

    public function platformadvantages()
    {
        $rows = PlatformAdvantage::orderBy('step_number', 'asc')->get();

        if ($rows->isEmpty()) {
            return response()->json([
                'status' => true,
                'data' => null
            ]);
        }

        $first = $rows->first();

        $steps = $rows->map(function ($item) {
            return [
                'step_number' => (int) $item->step_number,
                'title' => $item->title,
                'description' => $item->description,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => [
                'heading' => $first->heading,
                'subheading' => $first->subheading,
                'steps' => $steps
            ]
        ]);
    }

    public function faq()
    {
        try {
            $faqs = Faq::where('is_active', 1)
                ->orderBy('sort_order')
                ->get();
            // ->get(['id', 'question', 'answer']);

            return response()->json([
                'status' => true,
                'message' =>  __('messages.faq_data_fetched'),
                'data' => $faqs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' =>  __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

  public function getHeader()
    {
        try {
            $logo = HeaderMenu::where('type', 'logo')->first();
    
            $menus = HeaderMenu::where('type', 'menu')
                ->where('status', 1)
                ->orderBy('sort_order')
                ->get();
    
            return response()->json([
                'status' => true,
                'message' => __('messages.header_data_fetched'),
                'data' => [
                    'logo'    => $logo && $logo->logo
                        ? asset('storage/' . $logo->logo)
                        : null,
    
                    'favicon' => $logo && $logo->favicon
                        ? asset('storage/' . $logo->favicon)
                        : null,
    
                    'menus'   => $menus
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
      public function contactSetting()
    {
        $settings = ContactSetting::all()->keyBy('type');
    
        return response()->json([
            'status' => true,
            'data' => [
                'email' => [
                    'title' => $settings['email']->title ?? '',
                    'short_description' => $settings['email']->short_description ?? '',
                ],
                'call_us' => [
                    'title' => $settings['call_us']->title ?? '',
                    'short_description' => $settings['call_us']->short_description ?? '',
                ],
                'business_hours' => [
                    'title' => $settings['business_hours']->title ?? '',
                    'short_description' => $settings['business_hours']->short_description ?? '',
                ],
                'visit_us' => [
                    'title' => $settings['visit_us']->title ?? '',
                    'short_description' => $settings['visit_us']->short_description ?? '',
                ],
            ]
        ]);
    }

}