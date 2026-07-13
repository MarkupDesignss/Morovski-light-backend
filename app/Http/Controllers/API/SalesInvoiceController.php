<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedMail;
use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Models\Admin;
use App\Models\Page;
use App\Models\CartItem;
use App\Models\Wishlist;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use App\Models\ItemShareLink;
use App\Models\ItemShareLinkClick;
use App\Models\Item;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Notification;
use App\Mail\ShareItemLinkMail;
use App\Models\OrderItemAllocation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;



class SalesInvoiceController extends Controller
{
    // public function dashboard()
    // {
    //     try {
    //         $user = Auth::user();

    //         /*
    //     --------------------------------
    //     OPEN PROFORMA INVOICES
    //     --------------------------------
    //     */
    //         $openPIs = Invoice::where('type', 'proforma')
    //             ->whereHas('order', function ($q) {
    //                 $q->where('payment_status', '!=', 'paid');
    //             })
    //             ->count();

    //         $newPIsThisWeek = Invoice::where('type', 'proforma')
    //             ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
    //             ->count();

    //         /*
    //     --------------------------------
    //     ORDERS THIS MONTH
    //     --------------------------------
    //     */
    //         $ordersThisMonth = Order::whereMonth('created_at', now()->month)->count();
    //         $ordersLastMonth = Order::whereMonth('created_at', now()->subMonth()->month)->count();

    //         /*
    //     --------------------------------
    //     LOW STOCK ALERTS
    //     --------------------------------
    //     */
    //         $lowStockItems = Item::where('quantity', '<=', 20)->count();

    //         /*
    //     --------------------------------
    //     RECENT PROFORMA INVOICES
    //     --------------------------------
    //     */
    //         $recentPIs = Invoice::with('order.user')
    //             ->where('type', 'proforma')
    //             ->whereHas('order.user')
    //             ->latest()
    //             ->take(5)
    //             ->get()
    //             ->map(function ($pi) {
    //                 return [
    //                     'pi_number' => $pi->invoice_number,
    //                     'client'    => $pi->order->user->full_name,
    //                     'total'     => $pi->total_amount,
    //                     'status'    => $pi->order->payment_status,
    //                 ];
    //             });

    //         /*
    //     --------------------------------
    //     B2B REQUESTS
    //     --------------------------------
    //     */
    //         $b2bRequests = User::where('account_type', 'b2b')
    //             ->latest()
    //             ->take(5)
    //             ->get()
    //             ->map(function ($user) {
    //                 return [
    //                     'company' => $user->businessProfile->company_name ?? $user->name,
    //                     'time'    => $user->created_at->diffForHumans(),
    //                     'status'  => $user->business_status ?? 'pending',
    //                 ];
    //             });

    //         /*
    //     --------------------------------
    //     NOTIFICATIONS
    //     --------------------------------
    //     */
    //         $notifications = [];

    //         // PI Viewed
    //         $recentViewed = Invoice::where('type', 'proforma')
    //             ->latest()
    //             ->take(2)
    //             ->get();

    //         foreach ($recentViewed as $pi) {
    //             $notifications[] = [
    //                 'message' => "{$pi->invoice_number} viewed",
    //                 'time'    => $pi->updated_at->diffForHumans()
    //             ];
    //         }

    //         // Expiring PIs (example logic)
    //         $expiring = Invoice::where('type', 'proforma')
    //             ->whereDate('created_at', '<=', now()->subDays(7))
    //             ->take(2)
    //             ->get();

    //         foreach ($expiring as $pi) {
    //             $notifications[] = [
    //                 'message' => "{$pi->invoice_number} expiring soon",
    //                 'time'    => $pi->created_at->diffForHumans()
    //             ];
    //         }

    //         // Low stock alerts
    //         $lowStockList =  Item::where('quantity', '<=', 20)
    //             ->take(2)
    //             ->get();

    //         foreach ($lowStockList as $item) {
    //             $notifications[] = [
    //                 'message' => "Low stock - {$item->name}",
    //                 'time'    => "{$item->quantity} units left"
    //             ];
    //         }

    //         /*
    //     --------------------------------
    //     RESPONSE
    //     --------------------------------
    //     */
    //         return response()->json([
    //             'status' => true,
    //             'data' => [

    //                 'stats' => [
    //                     'open_pis' => [
    //                         'total' => $openPIs,
    //                         'new_this_week' => $newPIsThisWeek
    //                     ],
    //                     'orders' => [
    //                         'this_month' => $ordersThisMonth,
    //                         'last_month' => $ordersLastMonth
    //                     ],
    //                     'low_stock' => $lowStockItems
    //                 ],

    //                 'recent_pis' => $recentPIs,

    //                 'b2b_requests' => $b2bRequests,

    //                 'notifications' => $notifications

    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }

    public function report()
    {
        try {

            /*
            --------------------------------
            DATE RANGES
            --------------------------------
            */
            $currentMonthStart = now()->startOfMonth();
            $currentMonthEnd   = now()->endOfMonth();

            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd   = now()->subMonth()->endOfMonth();

            /*
            --------------------------------
            SALES EXECUTIVE FILTER
            --------------------------------
            */
            $salesExecutiveId = auth()->id();

            /*
            --------------------------------
            TOTAL PIs GENERATED
            --------------------------------
            */
            $pisGenerated = Invoice::
                // $pisGenerated = Invoice::where('sales_executive_id', $salesExecutiveId)
                where('type', 'proforma')
                ->whereBetween('created_at', [
                    $currentMonthStart,
                    $currentMonthEnd
                ])
                ->count();

            /*
            --------------------------------
            PIs CONVERTED
            --------------------------------
            */
            $pisConverted = Invoice::where('type', 'proforma')
                ->where('pi_status', 'like', '%convert%')
                ->whereBetween('created_at', [
                    $currentMonthStart,
                    $currentMonthEnd
                ])
                ->count();

            /*
            --------------------------------
            REVENUE
            ONLY CONVERTED PI
            --------------------------------
            */
            $revenue = Invoice::where('type', 'proforma')
                //  ->where('pi_status', 'like', '%convert%')
                ->whereBetween('created_at', [
                    $currentMonthStart,
                    $currentMonthEnd
                ])
                ->sum('total_amount');

            /*
            --------------------------------
            AVG PI VALUE
            --------------------------------
            */
            $avgPiValue = Invoice::where('type', 'proforma')
                ->whereBetween('created_at', [
                    $currentMonthStart,
                    $currentMonthEnd
                ])
                ->avg('total_amount');

            /*
            --------------------------------
            LAST MONTH AVG PI VALUE
            --------------------------------
            */
            $lastMonthAvgPiValue = Invoice::where('sales_executive_id', $salesExecutiveId)
                ->where('type', 'proforma')
                ->whereBetween('created_at', [
                    $lastMonthStart,
                    $lastMonthEnd
                ])
                ->avg('total_amount');

            /*
            --------------------------------
            STATUS BREAKDOWN
            --------------------------------
            */
            $draftCount = Invoice::where('pi_status', 'like', '%B2B Web Request%')
                ->count();

            $sentViewedCount = Invoice::where('pi_status', 'like', '%converted_to_order%')
                ->count();

            $convertedCount = Invoice::where('pi_status', 'like', '%converted_to_order%')
                ->count();

            $expiredCancelledCount = Invoice::where('type', 'proforma')
                ->where(function ($q) {

                    $q->whereDate('valid_until', '<', now());
                })
                ->count();

            /*
            --------------------------------
            CONVERSION RATE
            --------------------------------
            */
            $conversionRate = 0;

            if ($pisGenerated > 0) {

                $conversionRate = round(
                    ($pisConverted / $pisGenerated) * 100
                );
            }

            /*
            --------------------------------
            TOP PRODUCTS
            --------------------------------
            */
            $topProducts = InvoiceItem::join('items', 'invoice_items.item_id', '=', 'items.id')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereBetween('invoices.created_at', [
                    $currentMonthStart,
                    $currentMonthEnd
                ])
                ->select(
                    'items.id',
                    'items.name',
                    'items.sku',
                    DB::raw('SUM(invoice_items.quantity) as total_units')
                )
                ->groupBy(
                    'items.id',
                    'items.name',
                    'items.sku'
                )
                ->orderByDesc('total_units')
                ->take(5)
                ->get()
                ->map(function ($item) {

                    return [
                        'item_id'     => $item->id,
                        'item_name'   => $item->name,
                        'sku'         => $item->sku,
                        'total_units' => (int) $item->total_units,
                    ];
                });

            /*
            --------------------------------
            ACTIVITY LOG
            --------------------------------
            */
            // $activityLog = Invoice::latest()
            //     ->take(10)
            //     ->get()
            //     ->map(function ($invoice) {

            //         $action = 'PI Created';

            //         if ($invoice->pi_status == 'sent') {
            //             $action = 'PI Sent';
            //         }

            //         if ($invoice->pi_status == 'viewed') {
            //             $action = 'PI Viewed';
            //         }

            //         if ($invoice->pi_status == 'converted to order') {
            //             $action = 'PI Converted';
            //         }

            //         return [
            //             'date' => $invoice->created_at->format('d M, h:i A'),

            //             'action' => $action,

            //             'detail' => $invoice->invoice_number,
            //         ];
            //     });

            $activityLog = Invoice::whereDate('created_at', today())
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($invoice) {

                    return [
                        'date'   => $invoice->created_at->format('d M, h:i A'),
                        'action' => 'PI Created',
                        'detail' => $invoice->invoice_number,
                    ];
                });

            /*
            --------------------------------
            FINAL RESPONSE
            --------------------------------
            */
            return response()->json([

                'status' => true,

                'data' => [

                    /*
                    --------------------------------
                    PERFORMANCE CARDS
                    --------------------------------
                    */
                    'overview_cards' => [

                        'pis_generated' => [
                            'title' => 'PIs Generated',
                            'total' => $pisGenerated,
                        ],

                        'pis_converted' => [
                            'title' => 'PIs Converted',
                            'total' => $pisConverted,
                            'rate'  => $conversionRate . '%',
                        ],

                        'revenue' => [
                            'title'  => 'Revenue',
                            'amount' => round($revenue, 2),
                        ],

                        'avg_pi_value' => [
                            'title'  => 'Avg PI Value',
                            'amount' => round($avgPiValue, 2),
                            'previous_month' => round($lastMonthAvgPiValue, 2),
                        ]
                    ],

                    /*
                    --------------------------------
                    PI STATUS BREAKDOWN
                    --------------------------------
                    */
                    'pi_status_breakdown' => [

                        'draft' => $draftCount,

                        'sent_viewed' => $sentViewedCount,

                        'converted' => $convertedCount,

                        'expired_cancelled' => $expiredCancelledCount,

                        'conversion_rate' => $conversionRate
                    ],

                    /*
                    --------------------------------
                    TOP PRODUCTS
                    --------------------------------
                    */
                    'top_products' => $topProducts,

                    /*
                    --------------------------------
                    ACTIVITY LOG
                    --------------------------------
                    */
                    'activity_log' => $activityLog,
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function dashboard()
    {
        try {



            /*
        --------------------------------
        DATE RANGES
        --------------------------------
        */
            $currentMonthStart = now()->startOfMonth();
            $currentMonthEnd   = now()->endOfMonth();

            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd   = now()->subMonth()->endOfMonth();

            /*
        --------------------------------
        TOTAL REVENUE
        FROM INVOICE TABLE
        --------------------------------
        */
            // $totalRevenue = Invoice::sum('total_amount');
               $totalRevenue = Invoice::where('type', 'proforma')
                //  ->where('pi_status', 'like', '%convert%')
                ->whereBetween('created_at', [
                    $currentMonthStart,
                    $currentMonthEnd
                ])
                ->sum('total_amount');

            $currentMonthRevenue = Invoice::whereBetween('created_at', [
                $currentMonthStart,
                $currentMonthEnd
            ])
                ->sum('total_amount');

            $lastMonthRevenue = Invoice::whereBetween('created_at', [
                $lastMonthStart,
                $lastMonthEnd
            ])
                ->sum('total_amount');

            $revenueGrowth = 0;
            // dd($lastMonthEnd,$lastMonthStart);
            if ($lastMonthRevenue > 0) {
                $revenueGrowth = round(
                    (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100,
                    1
                );
            }

            /*
        --------------------------------
        ACTIVE PROFORMA INVOICES
        --------------------------------
        */
            $activePIs = Invoice::where('type', 'proforma')
                ->where(function ($q) {
                    $q->where('valid_until', '>=', now());
                })
                ->count();

            $pendingPIs = Invoice::where('type', 'proforma')
                ->where('pi_status', 'B2B Web Request')
                ->count();

            /*
        --------------------------------
        B2B REQUESTS
        --------------------------------
        */
            $totalB2BRequests = User::where('account_type', 'b2b')->count();

            $newB2BRequests = User::where('account_type', 'b2b')
                ->count();
            // $newB2BRequests = User::where('account_type', 'b2b')
            //     ->whereBetween('created_at', [
            //         now()->startOfWeek(),
            //         now()->endOfWeek()
            //     ])
            //     ->count();

            /*
        --------------------------------
        CONVERSION RATE
        --------------------------------
        */
            $totalInvoices = Invoice::count();

            $paidInvoices = Invoice::where('amount_paid', '>', 0)->count();

            $conversionRate = 0;

            if ($totalInvoices > 0) {
                $conversionRate = round(
                    ($paidInvoices / $totalInvoices) * 100
                );
            }

            $lastMonthInvoices = Invoice::whereBetween('created_at', [
                $lastMonthStart,
                $lastMonthEnd
            ])
                ->count();

            $lastMonthPaidInvoices = Invoice::where('amount_paid', '>', 0)
                ->whereBetween('created_at', [
                    $lastMonthStart,
                    $lastMonthEnd
                ])
                ->count();

            $lastMonthConversion = 0;

            if ($lastMonthInvoices > 0) {
                $lastMonthConversion = (
                    $lastMonthPaidInvoices / $lastMonthInvoices
                ) * 100;
            }

            $conversionGrowth = round(
                $conversionRate - $lastMonthConversion
            );

            /*
        --------------------------------
        LOW STOCK ITEMS
        --------------------------------
        */
            $lowStockItems = Item::where('quantity', '<=', 20)->count();

            /*
        --------------------------------
        RECENT PROFORMA INVOICES
        --------------------------------
        */
            $recentPIs = Invoice::with('order.user')
                ->where('type', 'proforma')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($pi) {

                    return [
                        'invoice_id'     => $pi->id,
                        'invoice_number' => $pi->invoice_number,
                        'client'         => optional(optional($pi->order)->user)->full_name,
                        'type'           => $pi->type,
                        'status'         => $pi->pi_status,
                        'total_amount'   => $pi->total_amount,
                        'amount_paid'    => $pi->amount_paid,
                        'created_at'     => $pi->created_at->format('d M Y'),
                    ];
                });

            /*
        --------------------------------
        B2B REQUEST LIST
        --------------------------------
        */
            $b2bRequests = User::with('businessProfile')
                ->where('account_type', 'b2b')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($user) {

                    return [
                        'id'      => $user->id,
                        'company' => optional($user->businessProfile)->company_name
                            ?? $user->full_name,
                        'email'   => $user->email,
                        'phone'   => $user->phone,
                        'status'  => $user->business_status ?? 'pending',
                        'time'    => $user->created_at->diffForHumans(),
                    ];
                });

            /*
        --------------------------------
        NOTIFICATIONS
        --------------------------------
        */
            $notifications = [];

            // Recent invoices
            $recentInvoices = Invoice::latest()
                ->take(2)
                ->get();

            foreach ($recentInvoices as $invoice) {

                $notifications[] = [
                    'message' => "{$invoice->invoice_number} generated",
                    'time'    => $invoice->created_at->diffForHumans()
                ];
            }

            // Expiring PI
            $expiringInvoices = Invoice::where('type', 'proforma')
                ->whereNotNull('valid_until')
                ->whereDate('valid_until', '<=', now()->addDays(3))
                ->take(2)
                ->get();

            foreach ($expiringInvoices as $invoice) {

                $notifications[] = [
                    'message' => "{$invoice->invoice_number} expiring soon",
                    'time'    => optional($invoice->valid_until)->format('d M Y')
                ];
            }

            // Low stock alerts
            $lowStockList = Item::where('quantity', '<=', 20)
                ->take(2)
                ->get();

            foreach ($lowStockList as $item) {

                $notifications[] = [
                    'message' => "Low stock - {$item->name}",
                    'time'    => "{$item->quantity} units left"
                ];
            }

            /*
        --------------------------------
        FINAL RESPONSE
        --------------------------------
        */
            return response()->json([

                'status' => true,

                'data' => [

                    /*
                --------------------------------
                DASHBOARD CARDS
                --------------------------------
                */
                    'overview_cards' => [

                        'total_revenue' => [
                            'title'  => 'Total Revenue',
                            'amount' => number_format($totalRevenue),
                            // 'amount' => round($totalRevenue, 2),
                            'growth' => $revenueGrowth
                        ],

                        'active_pis' => [
                            'title'   => 'Active PIs',
                            'total'   => $activePIs,
                            'pending' => $pendingPIs
                        ],

                        'b2b_requests_summary' => [
                            'title' => 'B2B Requests',
                            'total' => $totalB2BRequests,
                            'new'   => $newB2BRequests
                        ],

                        'conversion_rate' => [
                            'title'  => 'Conversion Rate',
                            'rate'   => $conversionRate,
                            'growth' => $conversionGrowth
                        ]
                    ],

                    /*
                --------------------------------
                EXTRA STATS
                --------------------------------
                */
                    'stats' => [
                        'low_stock_items' => $lowStockItems,
                    ],

                    /*
                --------------------------------
                RECENT PI LIST
                --------------------------------
                */
                    'recent_pis' => $recentPIs,

                    /*
                --------------------------------
                B2B REQUEST LIST
                --------------------------------
                */
                    'b2b_requests' => $b2bRequests,

                    /*
                --------------------------------
                NOTIFICATIONS
                --------------------------------
                */
                    'notifications' => $notifications
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // public function markAsPaid(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'invoice_id' => 'required|exists:invoices,id'
    //         ]);

    //         $invoice = Invoice::with('order')
    //             ->where('id', $request->invoice_id)
    //             ->where('type', 'proforma')
    //             ->first();

    //         if (!$invoice) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Invoice not found'
    //             ]);
    //         }

    //         /*
    //         --------------------------------
    //         UPDATE PAYMENT
    //         --------------------------------
    //         */
    //         $invoice->update([
    //             'pi_status' => 'paid',
    //             'amount_paid' => $invoice->total_amount
    //         ]);

    //         if ($invoice->order) {
    //             $invoice->order->update([
    //                 'payment_status' => 'paid'
    //             ]);
    //         }

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Invoice marked as paid'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ],500);
    //     }
    // }

    public function markAsPaid(Request $request)
    {
        try {

            $request->validate([
                'invoice_id' => 'required|exists:invoices,id'
            ]);

            $invoice = Invoice::with('order')
                ->where('id', $request->invoice_id)
                ->where('type', 'proforma')
                ->first();

            if (!$invoice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            /*
            --------------------------------
            CHECK ORDER EXISTS
            --------------------------------
            */
            if (!$invoice->order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order does not exist for this invoice'
                ], 422);
            }

            /*
            --------------------------------
            DUE AMOUNT CHECK
            --------------------------------
            */
            if ($invoice->order->due_amount > 0) {

                return response()->json([
                    'status' => false,
                    'message' => 'Cannot mark as paid. Pending due amount is ₹' . number_format($invoice->order->due_amount, 2)
                ], 422);
            }

            /*
            --------------------------------
            ALREADY PAID CHECK
            --------------------------------
            */
            if ($invoice->pi_status == 'paid') {

                return response()->json([
                    'status' => false,
                    'message' => 'Invoice already paid'
                ], 422);
            }

            /*
            --------------------------------
            UPDATE PAYMENT
            --------------------------------
            */
            $invoice->update([
                'pi_status'   => 'paid',
                'amount_paid' => $invoice->total_amount
            ]);

            $invoice->order->update([
                'payment_status' => 'paid'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Invoice marked as paid successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadPI($id)
    {
        $invoice = Invoice::with(['order.user', 'items.orderItem'])
            ->findOrFail($id);

        $html = view('invoice.pi', compact('invoice'))->render();

        return response($html)
            ->header('Content-Type', 'text/html');
    }

    public function updateProformaInvoice(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'valid_until' => 'nullable|date|after:today',
                'payment_terms' => 'nullable|string',

                'items' => 'nullable|array',
                'items.*.id' => 'required|exists:invoice_items,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0|max:100',
            ]);

            $invoice = Invoice::with('items')
                ->where('id', $id)
                ->where('type', 'proforma')
                ->first();

            if (!$invoice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invoice not found'
                ]);
            }

            /*
            --------------------------------
            UPDATE BASIC FIELDS
            --------------------------------
            */
            if ($request->filled('valid_until')) {
                $invoice->valid_until = $request->valid_until;
            }

            if ($request->filled('payment_terms')) {
                $invoice->payment_terms = $request->payment_terms;
            }

            $invoice->save();

            /*
            --------------------------------
            UPDATE ITEMS
            --------------------------------
            */
            $totalAmount = 0;

            if ($request->filled('items')) {
                foreach ($request->items as $itemData) {

                    $item = InvoiceItem::find($itemData['id']);

                    $lineTotal = $itemData['quantity'] * $itemData['unit_price'];

                    $discount = $itemData['discount'] ?? 0;

                    if ($discount > 0) {
                        $lineTotal -= ($lineTotal * $discount / 100);
                    }

                    $item->update([
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'line_discount_percent' => $discount,
                        'total_price' => $lineTotal,
                    ]);

                    $totalAmount += $lineTotal;
                }
            }

            /*
            --------------------------------
            UPDATE TOTAL
            --------------------------------
            */
            if ($totalAmount > 0) {
                $invoice->update([
                    'total_amount' => $totalAmount,
                    'payment_terms' => $request->payment_terms,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Proforma Invoice updated successfully'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getAllProformaInvoices(Request $request)
    {
        try {
            $query = Invoice::with(['client', 'order.user', 'items.item.images'])
                ->where('type', 'proforma')
                ->latest();

            // Search by PI number or client name
            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('invoice_number', 'like', '%' . $request->search . '%')
                        ->orWhereHas('client', function ($q2) use ($request) {
                            $q2->where('full_name', 'like', '%' . $request->search . '%');
                        })
                        ->orWhereHas('order.user', function ($q2) use ($request) {
                            $q2->where('full_name', 'like', '%' . $request->search . '%');
                        });
                });
            }

            // Status filter (draft, sent, B2B Web Request, etc.)
            if ($request->filled('status')) {
                $query->where('pi_status', $request->status);
            }

            // For B2B Web Request queue specifically
            if ($request->filled('queue') && $request->queue === 'b2b_web') {
                $query->where('pi_status', 'B2B Web Request');
            }

            // Date filters (same as yours)
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            $invoices = $query->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'Proforma invoices fetched successfully',
                'data' => $invoices,
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'total' => $invoices->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function shippingMethods()
    {
        try {

            $shippingMethods = ShippingMethod::where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->get([
                    'id',
                    'name',
                    'slug',
                    'description',
                    'delivery_time',
                    'price',
                    'is_free',
                    'is_active',
                    'sort_order',
                    'created_at',
                    'updated_at'
                ]);

            return response()->json([
                'status' => true,
                'message' => 'Shipping methods fetched successfully',
                'data' => $shippingMethods
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function getProformaInvoiceDetails($id)
    {
        // try {

            $invoice = Invoice::with([

                /*
                |--------------------------------------------------------------------------
                | CLIENT
                |--------------------------------------------------------------------------
                */

                'client:id,full_name,email,phone',

                'client.businessProfile:user_id,company_name,discount_percentage',

                'client.shippingAddresses',

                /*
                |--------------------------------------------------------------------------
                | ORDERS
                |--------------------------------------------------------------------------
                */

                'order:*',

                'orders.user:id,full_name,email,phone',

                /*
                |--------------------------------------------------------------------------
                | SALES EXECUTIVE
                |--------------------------------------------------------------------------
                */

                'salesExecutive:id,full_name,email,phone',

                /*
                |--------------------------------------------------------------------------
                | INVOICE ITEMS
                |--------------------------------------------------------------------------
                */

                'invoiceItems.item:id,category_id,name,slug,sku,price,type,quantity,rating,is_featured,reviews_count,wishlist_count,description,specifications',

                /*
                |--------------------------------------------------------------------------
                | ITEM IMAGES
                |--------------------------------------------------------------------------
                */

                'invoiceItems.item.images:id,item_id,image',

                /*
                |--------------------------------------------------------------------------
                | WAREHOUSE STOCK
                |--------------------------------------------------------------------------
                */

                'invoiceItems.item.warehouseItems.warehouse:id,name'

            ])
                ->where('type', 'proforma')
                ->findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | DEFAULT SHIPPING ADDRESS
            |--------------------------------------------------------------------------
            */

            $shippingAddress =
                $invoice->client?->shippingAddresses?->first();

            /*
            |--------------------------------------------------------------------------
            | ITEMS
            |--------------------------------------------------------------------------
            */

            $items = $invoice->invoiceItems->map(function ($invoiceItem) {

                $item = $invoiceItem->item;

                return [

                    'invoice_item_id' => $invoiceItem->id,

                    'order_item_id' => $invoiceItem->order_item_id,

                    'item_id' => $invoiceItem->item_id,

                    'quantity' => $invoiceItem->quantity,

                    'unit_price' => $invoiceItem->unit_price,

                    'line_discount_percent' =>
                    $invoiceItem->line_discount_percent,

                    'total_price' =>
                    $invoiceItem->total_price,

                    'stock_note' =>
                    $invoiceItem->stock_note,

                    /*
                    |--------------------------------------------------------------------------
                    | ITEM DETAILS
                    |--------------------------------------------------------------------------
                    */

                    'item_details' => [

                        'id' => $item?->id,

                        'category_id' => $item?->category_id,

                        'name' => $item?->name,

                        'slug' => $item?->slug,

                        'sku' => $item?->sku,

                        'price' => $item?->price,

                        'type' => $item?->type,

                        'quantity' => $item?->quantity,

                        'rating' => $item?->rating,

                        'is_featured' => $item?->is_featured,

                        'reviews_count' => $item?->reviews_count,

                        'wishlist_count' => $item?->wishlist_count,

                        'description' => $item?->description,

                        'specifications' => $item?->specifications,

                        /*
                        |--------------------------------------------------------------------------
                        | ITEM IMAGES
                        |--------------------------------------------------------------------------
                        */

                        'images' => $item
                            ? $item->images->map(function ($image) {

                                return [

                                    'id' => $image->id,

                                    'image' => $image->image,

                                    'image_url' =>
                                    asset('storage/' . $image->image),
                                ];
                            })->values()
                            : [],

                        /*
                        |--------------------------------------------------------------------------
                        | WAREHOUSE STOCK DETAILS
                        |--------------------------------------------------------------------------
                        */

                        'warehouse_stock' => $item
                            ? $item->warehouseItems
                            ->map(function ($warehouseItem) {

                                return [

                                    'warehouse_id' =>
                                    $warehouseItem
                                        ->warehouse?->id,

                                    'warehouse_name' =>
                                    $warehouseItem
                                        ->warehouse?->name,

                                    'available_qty' =>
                                    $warehouseItem
                                        ->quantity,
                                ];
                            })
                            ->values()

                            : [],
                    ]
                ];
            });

            $admin = Admin::get();
            $pages = Page::where('slug','invoice-terms-and-conditions')->get();

            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */
            // dd( $invoice->order->payment);
            return response()->json([

                'status' => true,

                'message' =>
                'Proforma invoice details fetched successfully',

                'data' => [

                    'invoice' => [

                        'id' => $invoice->id,

                        'invoice_number' => $invoice->invoice_number,

                        'type' => $invoice->type,

                        'pi_status' => $invoice->pi_status,

                        'valid_until' => $invoice->valid_until,

                        'payment_terms' => $invoice->payment_terms,

                        'total_amount' => $invoice->total_amount,

                        'amount_paid' => $invoice->amount_paid,

                        'pi_details' => $invoice->pi_details,

                        'created_at' => $invoice->created_at,

                        /*
                        |--------------------------------------------------------------------------
                        | CLIENT
                        |--------------------------------------------------------------------------
                        */

                        'client' => [

                            'id' =>
                            $invoice->client?->id,

                            'full_name' =>
                            $invoice->client?->full_name,

                            'email' =>
                            $invoice->client?->email,

                            'phone' =>
                            $invoice->client?->phone,
                        ],

                        /*
                        |--------------------------------------------------------------------------
                        | CLIENT BUSINESS PROFILE
                        |--------------------------------------------------------------------------
                        */

                        'client_business_profile' => [

                            'company_name' =>
                            $invoice->client?->businessProfile?->company_name,

                            'discount_percentage' =>
                            $invoice->client?->businessProfile?->discount_percentage,
                        ],

                        /*
                        |--------------------------------------------------------------------------
                        | ORDERS
                        |--------------------------------------------------------------------------
                        */

                        'orders' => $invoice->order,

                        /*
                        |--------------------------------------------------------------------------
                        | SALES EXECUTIVE
                        |--------------------------------------------------------------------------
                        */

                        'sales_customer' => [

                            'id' =>
                            $invoice->salesExecutive?->id,

                            'full_name' =>
                            $invoice->salesExecutive?->full_name,

                            'email' =>
                            $invoice->salesExecutive?->email,

                            'phone' =>
                            $invoice->salesExecutive?->phone,
                        ],

                        /*
                        |--------------------------------------------------------------------------
                        | SHIPPING ADDRESS
                        |--------------------------------------------------------------------------
                        */

                        'shipping_address' => [

                            'id' =>
                            $shippingAddress?->id,

                            'user_id' =>
                            $shippingAddress?->user_id,

                            'full_name' =>
                            $shippingAddress?->full_name,

                            'phone' =>
                            $shippingAddress?->phone,

                            'address_line_1' =>
                            $shippingAddress?->address_line_1,

                            'address_line_2' =>
                            $shippingAddress?->address_line_2,

                            'city' =>
                            $shippingAddress?->city,

                            'state' =>
                            $shippingAddress?->state,

                            'postal_code' =>
                            $shippingAddress?->postal_code,

                            'country' =>
                            $shippingAddress?->country,

                            'is_default' =>
                            $shippingAddress?->is_default,

                            'created_at' =>
                            $shippingAddress?->created_at,

                            'updated_at' =>
                            $shippingAddress?->updated_at,
                        ],
                    ],

                    'payment_details' => [
                        'id' => $invoice->order?->payment?->id,
                        'order_id' => $invoice->order?->payment?->order_id,
                        'user_id' => $invoice->order?->payment?->user_id,
                        'payment_method' => $invoice->order?->payment?->payment_method,
                        'transaction_id' => $invoice->order?->payment?->transaction_id,
                        'payment_gateway' => $invoice->order?->payment?->payment_gateway,
                        'amount' => $invoice->order?->payment?->amount,
                        'status' => $invoice->order?->payment?->status,
                        'paid_at' => $invoice->order?->payment?->paid_at,
                        // 'created_at' => $invoice->order?->payment?->created_at,
                    ],

                    'admin_details' => $admin,
                    'terms_conditions' => $pages ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | ITEMS
                    |--------------------------------------------------------------------------
                    */

                    'items' => $items
                ]
            ]);
        // } catch (\Exception $e) {

        //     return response()->json([

        //         'status' => false,

        //         'message' => $e->getMessage()

        //     ], 500);
        // }
    }

    public function deleteProformaInvoice($id)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            /*
        --------------------------------
        GET INVOICE
        --------------------------------
        */
            $invoice = Invoice::where('id', $id)
                ->where('type', 'proforma')
                ->with('items')
                ->first();

            if (!$invoice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Proforma Invoice not found'
                ]);
            }

            /*
        --------------------------------
        RESTRICT DELETE (OPTIONAL RULES)
        --------------------------------
        */
            if ($invoice->pi_status === 'sent') {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot delete a sent invoice'
                ]);
            }

            /*
        --------------------------------
        DELETE CHILD ITEMS FIRST
        --------------------------------
        */
            $invoice->items()->delete();

            /*
        --------------------------------
        DELETE INVOICE
        --------------------------------
        */
            $invoice->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Proforma Invoice deleted successfully'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createProformaInvoice(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'valid_until' => 'required|date|after:today',
                'payment_terms' => 'nullable|string',

                'items' => 'required|array|min:1',
                'items.*.order_item_id' => 'required|exists:order_items,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0|max:100',
            ]);

            $user = Auth::user();


            /*
        --------------------------------
        GET ORDER
        --------------------------------
        */
            $order = Order::with('items')->find($request->order_id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ]);
            }

            /*
        --------------------------------
        CREATE INVOICE (USE ORDER VALUES)
        --------------------------------
        */
            $invoice = Invoice::create([
                'order_id' => $order->id,
                'client_id' => $order->user_id,
                'sales_executive_id' => $user->id,
                'invoice_number' => 'PI-' . now()->timestamp,
                'type' => 'proforma',
                'pi_status' => 'draft',
                'valid_until' => $request->valid_until,
                'payment_terms' => $request->payment_terms,

                //  DIRECT FROM ORDER
                // 'subtotal' => $order->subtotal,
                // 'b2b_discount' => $order->b2b_discount,
                // 'promo_discount' => $order->promocode_discount,
                // 'shipping_charges' => $order->shipping_charges,
                // 'gst_amount' => $order->gst_amount,

                'total_amount' => $order->total_amount,
                'amount_paid' => $order->paid_amount,
            ]);

            /*
        --------------------------------
        ADD INVOICE ITEMS
        --------------------------------
        */
            foreach ($request->items as $item) {

                $lineTotal = $item['quantity'] * $item['unit_price'];

                $discount = $item['discount'] ?? 0;

                if ($discount > 0) {
                    $lineTotal -= ($lineTotal * $discount / 100);
                }

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'order_item_id' => $item['order_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_discount_percent' => $discount,
                    'total_price' => $lineTotal,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Proforma Invoice created successfully',
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'final_amount' => $invoice->total_amount
                ]
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    // public function sendPI(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'invoice_id' => 'required|exists:invoices,id'
    //         ]);

    //         $user = Auth::user();

    //         // Only Sales Exec can send PI
    //         if (!$user->roles->contains('slug', 'sales-executive')) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Unauthorized'
    //             ], 403);
    //         }

    //         // Load invoice with client relation (client_id -> User)
    //         $invoice = Invoice::with('client')
    //             ->where('id', $request->invoice_id)
    //             ->where('type', 'proforma')
    //             ->first();

    //         if (!$invoice) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Proforma Invoice not found'
    //             ], 404);
    //         }

    //         // Get client from invoice (not from order)
    //         $client = $invoice->client;

    //         if (!$client || !$client->email) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Client email not found'
    //             ], 400);
    //         }

    //         // Update status: from 'B2B Web Request' or 'draft' to 'sent'
    //         $invoice->update([
    //             'pi_status' => 'sent'
    //         ]);

    //         // Send email with PDF attachment
    //         Mail::to($client->email)->send(new \App\Mail\ProformaInvoiceMail($invoice));

    //         // Send WhatsApp (if phone exists)
    //         if ($client->phone) {
    //             $this->sendWhatsAppPI($client->phone, $invoice);
    //         }

    //         // Log notification for Sales Exec (optional)
    //         Notification::create([
    //             'user_id' => $user->id,
    //             'type' => 'pi_sent',
    //             'title' => 'Proforma Invoice Sent',
    //             'message' => "PI #{$invoice->invoice_number} sent to {$client->full_name}",
    //             'reference_type' => 'invoice',
    //             'reference_id' => $invoice->id,
    //             'priority' => 'medium'
    //         ]);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Proforma Invoice sent successfully',
    //             'data' => [
    //                 'invoice_id' => $invoice->id,
    //                 'client_email' => $client->email,
    //                 'status' => 'sent'
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function sendPI(Request $request)
    {
        try {
            $request->validate([
                'invoice_id' => 'required|exists:invoices,id'
            ]);

            $user = Auth::user();

            // Only Sales Exec can send PI
            if (!$user->roles->contains('slug', 'sales-executive')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Load invoice with client relation (client_id -> User)
            $invoice = Invoice::with('client')
                ->where('id', $request->invoice_id)
                ->where('type', 'proforma')
                ->first();

            if (!$invoice) {
                return response()->json([
                    'status' => false,
                    'message' => 'Proforma Invoice not found'
                ], 422);
            }

            // Get client from invoice (not from order)
            $client = $invoice->client;
            if (!$client || !$client->email) {
                return response()->json([
                    'status' => false,
                    'message' => 'Client email not found'
                ], 400);
            }
            // if ($invoice || $invoice->pi_status != 'draft') {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Performa already sent'
            //     ], 400);
            // }

            // Update status: from 'B2B Web Request' or 'draft' to 'sent'
            // $invoice->update([
            //     'pi_status' => 'sent'
            // ]);

            // Send email with PDF attachment
            Mail::to($client->email)->send(new \App\Mail\ProformaInvoiceMail($invoice));

            // Send WhatsApp (if phone exists)
            if ($client->phone) {
                $this->sendWhatsAppPI($client->phone, $invoice);
            }

            // Log notification for Sales Exec (optional)
            Notification::create([
                'user_id' => $user->id,
                'type' => 'pi_sent',
                'title' => 'Proforma Invoice Sent',
                'message' => "PI #{$invoice->invoice_number} sent to {$client->full_name}",
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'priority' => 'medium'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Proforma Invoice sent successfully',
                'data' => [
                    'invoice_id' => $invoice->id,
                    'client_email' => $client->email,
                    'status' => 'sent'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendWhatsAppPI($phone, $invoice)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');

        $client = new \Twilio\Rest\Client($sid, $token);

        /*
        --------------------------------
        GET ITEMS LIST (FROM INVOICE ITEMS)
        --------------------------------
        */
        $itemsText = '';

        foreach ($invoice->items as $item) {
            $productName = $item->orderItem->product_name ?? 'Item';

            $itemsText .= "- {$productName} (Qty: {$item->quantity}) | ₹{$item->total_price}\n";
        }

        /*
        --------------------------------
        GET CLIENT NAME
        --------------------------------
        */
        $clientName = $invoice->order?->user?->full_name ?? 'Customer';

        /*
        --------------------------------
        PREPARE MESSAGE
        --------------------------------
        */
        $message = " *Proforma Invoice Generated!*\n\n"
            . " Name: {$clientName}\n"
            . " PI Number: {$invoice->invoice_number}\n\n"
            . " *Items:*\n{$itemsText}\n"
            . " Total Amount: ₹{$invoice->total_amount}\n"
            . " Paid: ₹{$invoice->amount_paid}\n"
            . " Due: ₹" . ($invoice->total_amount - $invoice->amount_paid) . "\n\n"
            . " Valid Till: " . \Carbon\Carbon::parse($invoice->valid_until)->format('d M Y') . "\n"
            . " Payment Terms: {$invoice->payment_terms}\n\n"
            . "Please proceed with payment. Thank you!";

        /*
        --------------------------------
        SEND WHATSAPP MESSAGE
        --------------------------------
        */
        $client->messages->create(
            "whatsapp:+91" . $phone,
            [
                "from" => "whatsapp:+14155238886",
                "body" => $message
            ]
        );
    }

    public function getB2BUsers(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $users = User::where('account_type', 'b2b')
                ->with('businessProfile')
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'B2B users fetched successfully',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getB2BUserOrders(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $orders = Order::with('items.images','items.item')->whereHas('user', function ($q) {
                $q->where('account_type', 'b2b');
            })
                ->with([
                    'user:id,full_name,email,phone',
                    'user.businessProfile:id,user_id,company_name,gst_number'
                ])
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'B2B users orders fetched successfully',
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    // public function updateTrackingNumber(Request $request, $id)
    // {
    //     try {
    //         $request->validate([
    //             'tracking_number' => 'required|string|max:255'
    //         ]);

    //         $order = Order::findOrFail($id);

    //         $order->update([
    //             'tracking_number' => $request->tracking_number
    //         ]);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Tracking number updated successfully',
    //             'data' => $order
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }

    public function updateTrackingNumber(Request $request, $id)
    {
        try {
            $request->validate([
                'tracking_number' => 'required|string|max:255',
                'courier' => 'required|string|max:255'
            ]);

            $order = Order::findOrFail($id);

            $order->update([
                'tracking_number' => $request->tracking_number,
                'courier' => $request->courier
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Tracking number updated successfully',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function performance(Request $request)
    {
        try {
            $from = $request->from ?? now()->startOfMonth();
            $to   = $request->to ?? now()->endOfMonth();

            $totalPI = Invoice::whereBetween('created_at', [$from, $to])->count();

            $convertedPI = Invoice::where('pi_status', 'converted')
                ->whereBetween('created_at', [$from, $to])
                ->count();

            $revenue = Invoice::whereBetween('created_at', [$from, $to])
                ->sum('amount_paid');

            $avgPIValue = Invoice::whereBetween('created_at', [$from, $to])
                ->avg('total_amount');

            $conversionRate = $totalPI > 0
                ? round(($convertedPI / $totalPI) * 100, 2)
                : 0;

            $statusData = Invoice::select('pi_status', DB::raw('count(*) as total'))
                ->whereBetween('created_at', [$from, $to])
                ->groupBy('pi_status')
                ->pluck('total', 'pi_status');

            $statusBreakdown = [
                'draft'     => $statusData['draft'] ?? 0,
                'sent'      => $statusData['sent'] ?? 0,
                'converted' => $statusData['converted'] ?? 0,
                'expired'   => $statusData['expired'] ?? 0,
            ];

            $topProducts = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->join('order_items', 'invoice_items.order_item_id', '=', 'order_items.id')
                ->join('items', 'order_items.item_id', '=', 'items.id')
                ->whereBetween('invoices.created_at', [$from, $to])
                ->select(
                    'items.name',
                    DB::raw('SUM(invoice_items.quantity) as total_units')
                )
                ->groupBy('items.id', 'items.name')
                ->orderByDesc('total_units')
                ->limit(5)
                ->get();

            $activityLogs = Invoice::with('client:id,full_name')
                ->whereBetween('created_at', [$from, $to])
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($invoice) {

                    $action = match ($invoice->pi_status) {
                        'draft'     => 'PI Created',
                        'sent'      => 'PI Sent',
                        'converted' => 'PI Converted',
                        'expired'   => 'PI Expired',
                        default     => 'Updated'
                    };

                    return [
                        'date'   => $invoice->created_at->format('d M, H:i'),
                        'action' => $action,
                        'detail' => $invoice->invoice_number . ' - ' . ($invoice->client->full_name ?? 'N/A')
                    ];
                });

            return response()->json([
                'status' => true,
                'data' => [
                    'summary' => [
                        'pis_generated'   => $totalPI,
                        'pis_converted'   => $convertedPI,
                        'conversion_rate' => $conversionRate,
                        'revenue'         => $revenue,
                        'avg_pi_value'    => round($avgPIValue, 2),
                    ],
                    'pi_status_breakdown' => $statusBreakdown,
                    'top_products'        => $topProducts,
                    'activity_logs'       => $activityLogs,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // public function updateOrder(Request $request, $id)
    // {
    //     DB::beginTransaction();

    //     try {

    //         $order = Order::with('items')->findOrFail($id);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | VALIDATION
    //         |--------------------------------------------------------------------------
    //         */

    //         $request->validate([

    //             'order_status'       => 'nullable|string',

    //             'payment_method'     => 'nullable|string',
    //             'tracking_number'    => 'nullable|string',

    //             'address_id'         => 'nullable|exists:shipping_addresses,id',

    //             'shipping_address'   => 'nullable|string',

    //             /*
    //             |--------------------------------------------------------------------------
    //             | AMOUNTS
    //             |--------------------------------------------------------------------------
    //             */

    //             'b2b_discount'       => 'nullable|numeric|min:0',
    //             'shipping_charges'   => 'nullable|numeric|min:0',
    //             'gst_percentage'     => 'nullable|numeric|min:0|max:100',

    //             // NEW PAYMENT ONLY
    //             'paid_amount'        => 'nullable|numeric|min:0',

    //             'promocode_discount' => 'nullable|numeric|min:0',

    //             /*
    //             |--------------------------------------------------------------------------
    //             | ITEMS
    //             |--------------------------------------------------------------------------
    //             */

    //             'items'                    => 'nullable|array|min:1',

    //             'items.*.id'               => 'nullable|exists:order_items,id',
    //             'items.*.item_id'          => 'required_with:items|exists:items,id',
    //             'items.*.quantity'         => 'required_with:items|integer|min:1',
    //             'items.*.price'            => 'nullable|numeric|min:0',
    //             'items.*.remove'           => 'nullable|boolean',

    //         ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | UPDATE BASIC ORDER DETAILS
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($request->filled('order_status')) {
    //             $order->order_status = $request->order_status;
    //         }

    //         if ($request->filled('tracking_number')) {
    //             $order->tracking_number = $request->tracking_number;
    //         }

    //         if ($request->filled('address_id')) {
    //             $order->address_id = $request->address_id;
    //         }

    //         if ($request->filled('payment_method')) {
    //             $order->payment_method = $request->payment_method;
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | DEFAULT EXISTING SUBTOTAL
    //         |--------------------------------------------------------------------------
    //         */

    //         $subtotal = $order->subtotal ?? 0;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | UPDATE ITEMS ONLY IF PROVIDED
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($request->filled('items')) {

    //             $subtotal = 0;

    //             foreach ($request->items as $productData) {

    //                 $item = Item::findOrFail($productData['item_id']);

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | REMOVE ITEM
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 if (!empty($productData['remove']) && !empty($productData['id'])) {

    //                     $existingOrderItem = OrderItem::find($productData['id']);

    //                     if ($existingOrderItem) {

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | RESTORE STOCK
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         $item->increment('quantity', $existingOrderItem->quantity);

    //                         $existingOrderItem->delete();
    //                     }

    //                     continue;
    //                 }

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | PRICE
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $price = $productData['price'] ?? $item->price;

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | UPDATE EXISTING ITEM
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 if (!empty($productData['id'])) {

    //                     $orderItem = OrderItem::findOrFail($productData['id']);

    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | STOCK ADJUSTMENT
    //                     |--------------------------------------------------------------------------
    //                     */

    //                     $oldQty = $orderItem->quantity;
    //                     $newQty = $productData['quantity'];

    //                     $difference = $newQty - $oldQty;

    //                     if ($difference > 0) {

    //                         if ($item->quantity < $difference) {
    //                             throw new \Exception("Insufficient stock for {$item->name}");
    //                         }

    //                         $item->decrement('quantity', $difference);
    //                     } else {

    //                         $item->increment('quantity', abs($difference));
    //                     }

    //                     $orderItem->update([
    //                         'item_id'     => $item->id,
    //                         'quantity'    => $newQty,
    //                         'price'       => $price,
    //                         'total_price' => $newQty * $price,
    //                     ]);

    //                     $subtotal += ($newQty * $price);
    //                 } else {

    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | ADD NEW ITEM
    //                     |--------------------------------------------------------------------------
    //                     */

    //                     if ($item->quantity < $productData['quantity']) {
    //                         throw new \Exception("Insufficient stock for {$item->name}");
    //                     }

    //                     $item->decrement('quantity', $productData['quantity']);

    //                     OrderItem::create([
    //                         'order_id'    => $order->id,
    //                         'item_id'     => $item->id,
    //                         'quantity'    => $productData['quantity'],
    //                         'price'       => $price,
    //                         'total_price' => $productData['quantity'] * $price,
    //                     ]);

    //                     $subtotal += ($productData['quantity'] * $price);
    //                 }
    //             }
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | EXTRA CHARGES / DISCOUNTS
    //         |--------------------------------------------------------------------------
    //         */

    //         $b2bDiscount = $request->b2b_discount ?? $order->b2b_discount ?? 0;

    //         $shippingCharges = $request->shipping_charges ?? $order->shipping_charges ?? 0;

    //         $promocodeDiscount = $request->promocode_discount ?? $order->promocode_discount ?? 0;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | GST
    //         |--------------------------------------------------------------------------
    //         */

    //         $gstPercentage = $request->gst_percentage ?? 18;

    //         $taxableAmount = (
    //             $subtotal
    //             - $b2bDiscount
    //             - $promocodeDiscount
    //         );

    //         if ($taxableAmount < 0) {
    //             $taxableAmount = 0;
    //         }

    //         $gstAmount = ($taxableAmount * $gstPercentage) / 100;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | FINAL TOTAL
    //         |--------------------------------------------------------------------------
    //         */

    //         $totalAmount = (
    //             $taxableAmount
    //             + $shippingCharges
    //             + $gstAmount
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | PAID / DUE
    //         |--------------------------------------------------------------------------
    //         */

    //         /*
    //         |--------------------------------------------------------------------------
    //         | EXISTING + NEW PAYMENT
    //         |--------------------------------------------------------------------------
    //         */

    //         $existingPaidAmount = $order->paid_amount ?? 0;

    //         // NEW PAYMENT FROM REQUEST
    //         $newPaidAmount = $request->paid_amount ?? 0;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | FINAL PAID AMOUNT
    //         |--------------------------------------------------------------------------
    //         */

    //         $paidAmount = $existingPaidAmount + $newPaidAmount;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | VALIDATION
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($paidAmount > $totalAmount) {
    //             throw new \Exception("Paid amount cannot exceed total amount");
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | DUE AMOUNT
    //         |--------------------------------------------------------------------------
    //         */

    //         $dueAmount = $totalAmount - $paidAmount;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | AUTO PAYMENT STATUS
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($dueAmount <= 0) {

    //             $paymentStatus = 'paid';
    //         } elseif ($paidAmount > 0) {

    //             $paymentStatus = 'partial';
    //         } else {

    //             $paymentStatus = 'pending';
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | UPDATE ORDER
    //         |--------------------------------------------------------------------------
    //         */

    //         $order->update([

    //             'subtotal'            => $subtotal,

    //             'b2b_discount'        => $b2bDiscount,

    //             'shipping_charges'    => $shippingCharges,

    //             'gst_amount'          => $gstAmount,

    //             'promocode_discount'  => $promocodeDiscount,

    //             'total_amount'        => $totalAmount,

    //             'paid_amount'         => $paidAmount,

    //             'due_amount'          => $dueAmount,

    //             'payment_status'      => $paymentStatus,

    //             'order_date'          => now(),
    //         ]);

    //         DB::commit();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | RETURN UPDATED ORDER
    //         |--------------------------------------------------------------------------
    //         */

    //         $updatedOrder = Order::with([
    //             'items.item',
    //             'user',
    //         ])->find($order->id);

    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Order updated successfully',
    //             'data'    => $updatedOrder
    //         ]);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status'  => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

 public function updateOrder(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $order = Order::with('items')->findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */

            $request->validate([

                'order_status'       => 'nullable|string',

                'payment_method'     => 'nullable|string',

                'tracking_number'    => 'nullable|string',

                'address_id'         => 'nullable|exists:shipping_addresses,id',

                /*
                |--------------------------------------------------------------------------
                | DISCOUNT
                |--------------------------------------------------------------------------
                */

                'discount_percentage' => 'nullable|numeric|min:0|max:100',

                /*
                |--------------------------------------------------------------------------
                | CHARGES
                |--------------------------------------------------------------------------
                */

                'shipping_charges'   => 'nullable|numeric|min:0',

                'gst_percentage'     => 'nullable|numeric|min:0|max:100',

                'promocode_discount' => 'nullable|numeric|min:0',

                /*
                |--------------------------------------------------------------------------
                | PAYMENT
                |--------------------------------------------------------------------------
                */

                'paid_amount'        => 'nullable|numeric|min:0',

                /*
                |--------------------------------------------------------------------------
                | ITEMS
                |--------------------------------------------------------------------------
                */

                'items'                    => 'nullable|array|min:1',

                'items.*.id'               => 'nullable|exists:order_items,id',

                'items.*.item_id'          => 'required_with:items|exists:items,id',

                'items.*.quantity'         => 'required_with:items|integer|min:1',

                'items.*.price'            => 'nullable|numeric|min:0',

                'items.*.remove'           => 'nullable|boolean',
            ]);

            /*
            |--------------------------------------------------------------------------
            | UPDATE BASIC ORDER DETAILS
            |--------------------------------------------------------------------------
            */

            if ($request->filled('order_status')) {

                $order->order_status = $request->order_status;
            }

            if ($request->filled('tracking_number')) {

                $order->tracking_number = $request->tracking_number;
            }

            if ($request->filled('address_id')) {

                $order->address_id = $request->address_id;
            }

            if ($request->filled('payment_method')) {

                $order->payment_method = $request->payment_method;
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE ITEMS
            |--------------------------------------------------------------------------
            */

            if ($request->filled('items')) {

                foreach ($request->items as $productData) {

                    /*
                    |--------------------------------------------------------------------------
                    | REMOVE ITEM
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !empty($productData['remove']) &&
                        !empty($productData['id'])
                    ) {

                        $existingOrderItem =
                            OrderItem::find($productData['id']);

                        if ($existingOrderItem) {

                            $item = Item::find(
                                $existingOrderItem->item_id
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | RESTORE STOCK
                            |--------------------------------------------------------------------------
                            */

                            if ($item) {

                                $item->increment(
                                    'quantity',
                                    $existingOrderItem->quantity
                                );
                            }

                            $existingOrderItem->delete();
                        }

                        continue;
                    }

                    $item = Item::findOrFail(
                        $productData['item_id']
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | PRICE
                    |--------------------------------------------------------------------------
                    */

                    $price =
                        $productData['price']
                        ?? $item->price;

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE EXISTING ITEM
                    |--------------------------------------------------------------------------
                    */

                    if (!empty($productData['id'])) {

                        $orderItem = OrderItem::findOrFail(
                            $productData['id']
                        );

                        $oldQty = $orderItem->quantity;

                        $newQty = $productData['quantity'];

                        $difference = $newQty - $oldQty;

                        /*
                        |--------------------------------------------------------------------------
                        | STOCK CHECK
                        |--------------------------------------------------------------------------
                        */

                        if ($difference > 0) {

                            if ($item->quantity < $difference) {

                                throw new \Exception(
                                    "Insufficient stock for {$item->name}"
                                );
                            }

                            $item->decrement(
                                'quantity',
                                $difference
                            );
                        } elseif ($difference < 0) {

                            $item->increment(
                                'quantity',
                                abs($difference)
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE ORDER ITEM
                        |--------------------------------------------------------------------------
                        */

                        $orderItem->update([

                            'item_id'     => $item->id,

                            'quantity'    => $newQty,

                            'unit_price'  => $price,

                            'total_price' => $newQty * $price,
                        ]);
                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | ADD NEW ITEM
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $item->quantity <
                            $productData['quantity']
                        ) {

                            throw new \Exception(
                                "Insufficient stock for {$item->name}"
                            );
                        }

                        $item->decrement(
                            'quantity',
                            $productData['quantity']
                        );

                        OrderItem::create([

                            'order_id'    => $order->id,

                            'item_id'     => $item->id,

                            'quantity'    =>
                            $productData['quantity'],

                            'unit_price'  => $price,

                            'total_price' =>
                            $productData['quantity'] * $price,

                            'status'      => 'pending',
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | REFRESH ORDER ITEMS
            |--------------------------------------------------------------------------
            */

            $order->load('items');

            /*
            |--------------------------------------------------------------------------
            | RECALCULATE SUBTOTAL
            |--------------------------------------------------------------------------
            */

            $subtotal = $order->items->sum(function ($item) {

                return $item->total_price;
            });

            /*
            |--------------------------------------------------------------------------
            | DISCOUNT PERCENTAGE
            |--------------------------------------------------------------------------
            */

            $discountPercentage =
                $request->discount_percentage
                ?? $order->discount_percentage
                ?? 0;

            /*
            |--------------------------------------------------------------------------
            | DISCOUNT AMOUNT
            |--------------------------------------------------------------------------
            */

            $b2bDiscount =
                ($subtotal * $discountPercentage) / 100;

            /*
            |--------------------------------------------------------------------------
            | SHIPPING
            |--------------------------------------------------------------------------
            */

            $shippingCharges =
                $request->shipping_charges
                ?? $order->shipping_charges
                ?? 0;

            /*
            |--------------------------------------------------------------------------
            | PROMOCODE
            |--------------------------------------------------------------------------
            */

            $promocodeDiscount =
                $request->promocode_discount
                ?? $order->promocode_discount
                ?? 0;

            /*
            |--------------------------------------------------------------------------
            | TAXABLE AMOUNT
            |--------------------------------------------------------------------------
            */

            $taxableAmount =
                $subtotal
                - $b2bDiscount
                - $promocodeDiscount;

            if ($taxableAmount < 0) {

                $taxableAmount = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | GST
            |--------------------------------------------------------------------------
            */

            $gstPercentage =
                $request->gst_percentage ?? 18;

            $gstAmount =
                ($taxableAmount * $gstPercentage) / 100;

            /*
            |--------------------------------------------------------------------------
            | FINAL TOTAL
            |--------------------------------------------------------------------------
            */

            // $totalAmount =
            //     $taxableAmount
            //     + $shippingCharges;
                $totalAmount = round(
                    $taxableAmount + $shippingCharges
                );

            /*
            |--------------------------------------------------------------------------
            | EXISTING + NEW PAYMENT
            |--------------------------------------------------------------------------
            */

            $existingPaidAmount =
                $order->paid_amount ?? 0;

            $newPaidAmount =
                $request->paid_amount ?? 0;

            $paidAmount =
                $existingPaidAmount + $newPaidAmount;

            /*
            |--------------------------------------------------------------------------
            | VALIDATE PAYMENT
            |--------------------------------------------------------------------------
            */
            if ($paidAmount > $totalAmount) {

                throw new \Exception(
                    "Paid amount cannot exceed total amount"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | DUE AMOUNT
            |--------------------------------------------------------------------------
            */

            $dueAmount =
                $totalAmount - $paidAmount;

            /*
            |--------------------------------------------------------------------------
            | PAYMENT STATUS
            |--------------------------------------------------------------------------
            */

            if ($dueAmount <= 0) {

                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {

                $paymentStatus = 'partial';
            } else {

                $paymentStatus = 'pending';
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE ORDER
            |--------------------------------------------------------------------------
            */

            $order->update([

                'order_status'        =>
                $order->order_status,

                'payment_method'      =>
                $order->payment_method,

                'tracking_number'     =>
                $order->tracking_number,

                'address_id'          =>
                $order->address_id,

                'subtotal'            => $subtotal,

                'discount_percentage' =>
                $discountPercentage,

                'discount_updated_by' =>
                auth()->id(),

                'b2b_discount'        =>
                $b2bDiscount,

                'shipping_charges'    =>
                $shippingCharges,

                'gst_amount'          =>
                $gstAmount,

                'promocode_discount'  =>
                $promocodeDiscount,

                'total_amount'        =>
                $totalAmount,

                'paid_amount'         =>
                $paidAmount,

                'due_amount'          =>
                $dueAmount,

                'payment_status'      =>
                $paymentStatus,
            ]);

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | RETURN UPDATED ORDER
            |--------------------------------------------------------------------------
            */

            $updatedOrder = Order::with([

                'items.item',
                'user'

            ])->find($order->id);

            return response()->json([

                'status'  => true,

                'message' => 'Order updated successfully',

                'data'    => $updatedOrder

            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status'  => false,

                'message' => $e->getMessage(),

                'line'    => $e->getLine()

            ], 500);
        }
    }



    public function createOrderFromInvoice(Request $request, $invoiceId)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */

            $request->validate([

                'shipping_method_id' => [
                    'required',
                    'exists:shipping_methods,id'
                ],

                'address_id' => [
                    'required',
                    'exists:shipping_addresses,id'
                ],

                /*
                |--------------------------------------------------------------------------
                | DISCOUNT
                |--------------------------------------------------------------------------
                */

                'discount_percentage' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:100'
                ],

                /*
                |--------------------------------------------------------------------------
                | WAREHOUSE ALLOCATIONS
                |--------------------------------------------------------------------------
                */

                'warehouse_allocations' => [
                    'required',
                    'array',
                    'min:1'
                ],

                'warehouse_allocations.*.item_id' => [
                    'required',
                    'exists:invoice_items,id'
                ],

                'warehouse_allocations.*.warehouse_id' => [
                    'required',
                    'exists:warehouses,id'
                ],

                'warehouse_allocations.*.qty' => [
                    'required',
                    'integer',
                    'min:1'
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | GET INVOICE
            |--------------------------------------------------------------------------
            */

            $invoice = Invoice::with([

                'client.businessProfile',
                'invoiceItems.item'

            ])
                ->where('type', 'proforma')
                ->findOrFail($invoiceId);

            /*
            |--------------------------------------------------------------------------
            | CHECK CLIENT
            |--------------------------------------------------------------------------
            */

            if (!$invoice->client) {

                return response()->json([

                    'status'  => false,
                    'message' => 'Client not found.'

                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | ONLY B2B USERS
            |--------------------------------------------------------------------------
            */

            if ($invoice->client->account_type !== 'b2b') {

                return response()->json([

                    'status'  => false,
                    'message' => 'Only B2B users are allowed.'

                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | CHECK INVOICE ITEMS
            |--------------------------------------------------------------------------
            */

            if ($invoice->invoiceItems->count() == 0) {

                return response()->json([

                    'status'  => false,
                    'message' => 'Invoice items not found.'

                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | SHIPPING ADDRESS
            |--------------------------------------------------------------------------
            */

            $shippingAddress = ShippingAddress::where(
                'id',
                $request->address_id
            )
                ->where(
                    'user_id',
                    $invoice->client_id
                )
                ->first();

            if (!$shippingAddress) {

                return response()->json([

                    'status'  => false,

                    'message' =>
                    'Selected address does not belong to this user.'

                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | BUSINESS PROFILE
            |--------------------------------------------------------------------------
            */

            $businessProfile = $invoice->client->businessProfile;

            /*
            |--------------------------------------------------------------------------
            | DISCOUNT PERCENTAGE
            |--------------------------------------------------------------------------
            |
            | Priority:
            | 1. Sales executive entered discount
            | 2. Otherwise admin default discount
            |
            */

            $defaultDiscount =
                $businessProfile?->discount_percentage ?? 0;
            $discountPercentage =
                $request->filled('discount_percentage')
                ? $request->discount_percentage
                : $defaultDiscount;

            $discountSource =
                $request->filled('discount_percentage')
                ? 'sales_executive'
                : 'admin';

            /*
            |--------------------------------------------------------------------------
            | SUBTOTAL
            |--------------------------------------------------------------------------
            */

            $subtotal = $invoice->invoiceItems->sum(function ($invoiceItem) {

                return
                    $invoiceItem->quantity *
                    $invoiceItem->unit_price;
            });

            /*
            |--------------------------------------------------------------------------
            | B2B DISCOUNT
            |--------------------------------------------------------------------------
            */

            $b2bDiscount =
                ($subtotal * $discountPercentage) / 100;

            $afterDiscountAmount =
                $subtotal - $b2bDiscount;

            /*
            |--------------------------------------------------------------------------
            | GST
            |--------------------------------------------------------------------------
            */

            $gstPercentage = 18;

            $gstAmount =
                ($afterDiscountAmount * $gstPercentage) / 100;

            /*
            |--------------------------------------------------------------------------
            | SHIPPING METHOD
            |--------------------------------------------------------------------------
            */

            $shippingMethod = ShippingMethod::where(
                'is_active',
                1
            )
                ->find($request->shipping_method_id);

            if (!$shippingMethod) {

                return response()->json([

                    'status'  => false,

                    'message' =>
                    'Invalid shipping method selected.'

                ], 400);
            }

            $shippingCharge = $shippingMethod->price ?? 0;

            /*
            |--------------------------------------------------------------------------
            | TOTAL AMOUNT
            |--------------------------------------------------------------------------
            */

            $totalAmount =
                $afterDiscountAmount
                + $shippingCharge;

            /*
            |--------------------------------------------------------------------------
            | CREATE ORDER
            |--------------------------------------------------------------------------
            */

            $order = Order::create([

                'user_id'          => $invoice->client_id,

                'address_id'       => $shippingAddress->id,

                'order_number'     =>
                'ORD-' . strtoupper(Str::random(10)),

                'subtotal'         => $subtotal,

                'discount_percentage' => $discountPercentage,

                'discount_source'  => $discountSource,

                'discount_updated_by' => auth()->id(),

                'b2b_discount'     => $b2bDiscount,

                'shipping_charges' => $shippingCharge,

                'gst_amount'       => $gstAmount,

               'total_amount' => round($totalAmount),

                'payment_status'   => 'pending',

                'order_status'     => 'confirmed',

                'payment_method'   => 'bank_transfer',

                'paid_amount'      => 0,

               'due_amount' => round($totalAmount),

                'order_date'       => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | CREATE ORDER ITEMS
            |--------------------------------------------------------------------------
            */

            $createdOrderItems = [];

            foreach ($invoice->invoiceItems as $invoiceItem) {

                $orderItem = OrderItem::create([

                    'order_id'       => $order->id,

                    'item_id'        => $invoiceItem->item_id,

                    'quantity'       => $invoiceItem->quantity,

                    'status'         => 'pending',

                    'dispatched_qty' => 0,

                    'unit_price'     => $invoiceItem->unit_price,

                    'total_price'    => $invoiceItem->total_price,
                ]);

                $createdOrderItems[$invoiceItem->id] = $orderItem;
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDATE TOTAL ALLOCATION
            |--------------------------------------------------------------------------
            */

            $groupedAllocations = collect(
                $request->warehouse_allocations
            )->groupBy('item_id');

            foreach ($groupedAllocations as $invoiceItemId => $rows) {

                $invoiceItem = InvoiceItem::find($invoiceItemId);

                if (!$invoiceItem) {

                    DB::rollBack();

                    return response()->json([

                        'status'  => false,

                        'message' =>
                        "Invoice item {$invoiceItemId} not found"

                    ], 400);
                }

                $totalAllocated =
                    collect($rows)->sum('qty');

                if ($totalAllocated > $invoiceItem->quantity) {

                    DB::rollBack();

                    return response()->json([

                        'status'  => false,

                        'message' =>
                        "Allocated qty exceeds invoice qty for invoice item ID {$invoiceItemId}"

                    ], 400);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE ALLOCATIONS
            |--------------------------------------------------------------------------
            */

            foreach ($request->warehouse_allocations as $allocation) {

                $invoiceItemId = $allocation['item_id'];

                $orderItem =
                    $createdOrderItems[$invoiceItemId] ?? null;

                if (!$orderItem) {

                    DB::rollBack();

                    return response()->json([

                        'status'  => false,

                        'message' =>
                        "Order item mapping not found"

                    ], 400);
                }

                OrderItemAllocation::create([

                    'order_id'           => $order->id,

                    'order_item_id'      => $orderItem->id,

                    'warehouse_id'       => $allocation['warehouse_id'],

                    'sales_executive_id' => auth()->id(),

                    'allocated_qty'      => $allocation['qty'],

                    'dispatched_qty'     => 0,

                    'status'             => 'pending',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | GET SELECTED WAREHOUSE IDS
            |--------------------------------------------------------------------------
            */

            $warehouseIds = collect($request->warehouse_allocations)
                ->pluck('warehouse_id')
                ->unique()
                ->toArray();

            /*
            |--------------------------------------------------------------------------
            | GET WAREHOUSE USERS
            |--------------------------------------------------------------------------
            */

            // $warehouseManagers = User::whereIn(
            //     'warehouse_id',
            //     $warehouseIds
            // )
            //     ->where('is_active', 1)
            //     ->get();

            $warehouseManagers = User::whereIn('warehouse_id', $warehouseIds)
                ->where('account_type', 'warehouse_manager')
                ->where('is_active', 1)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | SEND NOTIFICATIONS
            |--------------------------------------------------------------------------
            */

            foreach ($warehouseManagers as $manager) {

                /*
                |--------------------------------------------------------------------------
                | GET MANAGER WAREHOUSE ALLOCATIONS
                |--------------------------------------------------------------------------
                */

                $managerAllocations = collect(
                    $request->warehouse_allocations
                )->where(
                    'warehouse_id',
                    $manager->warehouse_id
                );

                $totalItems =
                    $managerAllocations->sum('qty');

                /*
                |--------------------------------------------------------------------------
                | DATABASE NOTIFICATION
                |--------------------------------------------------------------------------
                */

                Notification::create([

                    'user_id'        => $manager->id,

                    'type'           => 'warehouse_item_allocation',

                    'title'          => 'New Item Allocation Request',

                    'message'        =>
                    "New allocation request received for Order #{$order->order_number }",

                    'reference_type' => 'order',

                    'reference_id'   => $order->id,

                    'priority'       => 'high',

                    'extra_data'     => json_encode([

                        'order_id'       => $order->id,

                        'order_number'   => $order->order_number,

                        'warehouse_id'   => $manager->warehouse_id,

                        'customer_name'  =>
                        optional($order->user)->full_name,

                        'customer_email' =>
                        optional($order->user)->email,

                        'total_items'    => $totalItems,

                        'allocated_by'   =>
                        auth()->user()->full_name ?? 'System',
                    ]),
                ]);

                /*
                |--------------------------------------------------------------------------
                | TWILIO WHATSAPP NOTIFICATION
                |--------------------------------------------------------------------------
                */

                try {

                    if (!empty($manager->phone)) {

                        $sid   = env('TWILIO_SID');
                        $token = env('TWILIO_AUTH_TOKEN');

                        $twilio = new Client($sid, $token);

                        $message =
                            "New Item Allocation Request\n\n"
                            . "Order ID: #{$order->id}\n"
                            . "Order Number: {$order->order_number}\n"
                            . "Warehouse ID: {$manager->warehouse_id}\n"
                            . "Items Allocated: {$totalItems}\n"
                            . "Discount: {$discountPercentage}%\n"
                            . "Allocated By: "
                            . (auth()->user()->full_name ?? 'System');

                        $twilio->messages->create(
                            "whatsapp:+91{$manager->phone}",
                            [
                                'from' => env('TWILIO_WHATSAPP_NUMBER'),
                                'body' => $message,
                            ]
                        );
                    }
                } catch (\Exception $twilioError) {

                    Log::error(
                        'Twilio WhatsApp Error: '
                            . $twilioError->getMessage()
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE INVOICE
            |--------------------------------------------------------------------------
            */

            $invoice->update([

                'order_id'  => $order->id,

                'pi_status' => 'converted_to_order'
            ]);

            DB::commit();

            return response()->json([

                'status'  => true,

                'message' =>
                'Order created successfully with warehouse allocations.',

                'data' => [

                    'order_id'       => $order->id,

                    'order_number'   => $order->order_number,

                    'invoice_id'     => $invoice->id,

                    'invoice_number' => $invoice->invoice_number,

                    'subtotal'       => $subtotal,

                    'discount_percentage' => $discountPercentage,

                    'discount_amount' => $b2bDiscount,

                    'gst_amount'     => $gstAmount,

                    'shipping_charge' => $shippingCharge,

                    'total_amount'   => $totalAmount,

                    'allocations'    => $request->warehouse_allocations
                ]

            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status'  => false,

                'message' => $e->getMessage(),

                'line'    => $e->getLine()

            ], 500);
        }
    }

    // public function createOrderFromInvoice(Request $request, $invoiceId)
    // {
    //     DB::beginTransaction();

    //     try {

    //         /*
    //     |--------------------------------------------------------------------------
    //     | VALIDATION
    //     |--------------------------------------------------------------------------
    //     */

    //         $request->validate([

    //             'shipping_method_id' => [
    //                 'required',
    //                 'exists:shipping_methods,id'
    //             ],

    //             'address_id' => [
    //                 'required',
    //                 'exists:shipping_addresses,id'
    //             ],

    //             /*
    //         |--------------------------------------------------------------------------
    //         | WAREHOUSE ALLOCATIONS
    //         |--------------------------------------------------------------------------
    //         */

    //             'warehouse_allocations' => [
    //                 'required',
    //                 'array',
    //                 'min:1'
    //             ],

    //             'warehouse_allocations.*.item_id' => [
    //                 'required',
    //                 'exists:invoice_items,id'
    //             ],

    //             'warehouse_allocations.*.warehouse_id' => [
    //                 'required',
    //                 'exists:warehouses,id'
    //             ],

    //             'warehouse_allocations.*.qty' => [
    //                 'required',
    //                 'integer',
    //                 'min:1'
    //             ],
    //         ]);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | GET INVOICE
    //     |--------------------------------------------------------------------------
    //     */

    //         $invoice = Invoice::with([

    //             'client.businessProfile',
    //             'invoiceItems.item'

    //         ])
    //             ->where('type', 'proforma')
    //             ->findOrFail($invoiceId);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | CHECK CLIENT
    //     |--------------------------------------------------------------------------
    //     */

    //         if (!$invoice->client) {

    //             return response()->json([

    //                 'status'  => false,
    //                 'message' => 'Client not found.'

    //             ], 404);
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | ONLY B2B USERS
    //     |--------------------------------------------------------------------------
    //     */

    //         if ($invoice->client->account_type !== 'b2b') {

    //             return response()->json([

    //                 'status'  => false,
    //                 'message' => 'Only B2B users are allowed.'

    //             ], 400);
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | CHECK INVOICE ITEMS
    //     |--------------------------------------------------------------------------
    //     */

    //         if ($invoice->invoiceItems->count() == 0) {

    //             return response()->json([

    //                 'status'  => false,
    //                 'message' => 'Invoice items not found.'

    //             ], 400);
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | SHIPPING ADDRESS
    //     |--------------------------------------------------------------------------
    //     */

    //         $shippingAddress = ShippingAddress::where(
    //             'id',
    //             $request->address_id
    //         )
    //             ->where(
    //                 'user_id',
    //                 $invoice->client_id
    //             )
    //             ->first();

    //         if (!$shippingAddress) {

    //             return response()->json([

    //                 'status'  => false,

    //                 'message' =>
    //                 'Selected address does not belong to this user.'

    //             ], 400);
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | BUSINESS PROFILE
    //     |--------------------------------------------------------------------------
    //     */

    //         $businessProfile = $invoice->client->businessProfile;

    //         $discountPercentage =
    //             $businessProfile?->discount_percentage ?? 0;

    //         /*
    //     |--------------------------------------------------------------------------
    //     | SUBTOTAL
    //     |--------------------------------------------------------------------------
    //     */

    //         $subtotal = $invoice->invoiceItems->sum(function ($invoiceItem) {

    //             return
    //                 $invoiceItem->quantity *
    //                 $invoiceItem->unit_price;
    //         });

    //         /*
    //     |--------------------------------------------------------------------------
    //     | B2B DISCOUNT
    //     |--------------------------------------------------------------------------
    //     */

    //         $b2bDiscount =
    //             ($subtotal * $discountPercentage) / 100;

    //         $afterDiscountAmount =
    //             $subtotal - $b2bDiscount;

    //         /*
    //     |--------------------------------------------------------------------------
    //     | GST
    //     |--------------------------------------------------------------------------
    //     */

    //         $gstPercentage = 18;

    //         $gstAmount =
    //             ($afterDiscountAmount * $gstPercentage) / 100;

    //         /*
    //     |--------------------------------------------------------------------------
    //     | SHIPPING METHOD
    //     |--------------------------------------------------------------------------
    //     */

    //         $shippingMethod = ShippingMethod::where(
    //             'is_active',
    //             1
    //         )
    //             ->find($request->shipping_method_id);

    //         if (!$shippingMethod) {

    //             return response()->json([

    //                 'status'  => false,

    //                 'message' =>
    //                 'Invalid shipping method selected.'

    //             ], 400);
    //         }

    //         $shippingCharge = $shippingMethod->price ?? 0;


    //         /*
    //     |--------------------------------------------------------------------------
    //     | TOTAL AMOUNT
    //     |--------------------------------------------------------------------------
    //     */

    //         $totalAmount =
    //             $afterDiscountAmount
    //             + $shippingCharge;

    //         /*
    //     |--------------------------------------------------------------------------
    //     | CREATE ORDER
    //     |--------------------------------------------------------------------------
    //     */

    //         $order = Order::create([

    //             'user_id'          => $invoice->client_id,

    //             'address_id'       => $shippingAddress->id,

    //             'order_number'     =>
    //             'ORD-' . strtoupper(Str::random(10)),

    //             'subtotal'         => $subtotal,

    //             'b2b_discount'     => $b2bDiscount,

    //             'shipping_charges' => $shippingCharge,

    //             'gst_amount'       => $gstAmount ?? '0',

    //             'total_amount'     => $totalAmount,

    //             'payment_status'   => 'pending',

    //             'order_status'     => 'confirmed',

    //             'payment_method'   => 'bank_transfer',

    //             'paid_amount'      => 0,

    //             'due_amount'       => $totalAmount,

    //             'order_date'       => now(),
    //         ]);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | CREATE ORDER ITEMS
    //     |--------------------------------------------------------------------------
    //     */

    //         $createdOrderItems = [];

    //         foreach ($invoice->invoiceItems as $invoiceItem) {

    //             $orderItem = OrderItem::create([

    //                 'order_id'       => $order->id,

    //                 'item_id'        => $invoiceItem->item_id,

    //                 'quantity'       => $invoiceItem->quantity,

    //                 'status'         => 'pending',

    //                 'dispatched_qty' => 0,

    //                 'unit_price'     => $invoiceItem->unit_price,

    //                 'total_price'    => $invoiceItem->total_price,
    //             ]);

    //             $createdOrderItems[$invoiceItem->id] = $orderItem;
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | VALIDATE TOTAL ALLOCATION
    //     |--------------------------------------------------------------------------
    //     */

    //         $groupedAllocations = collect(
    //             $request->warehouse_allocations
    //         )->groupBy('item_id');

    //         foreach ($groupedAllocations as $invoiceItemId => $rows) {

    //             $invoiceItem = InvoiceItem::find($invoiceItemId);

    //             if (!$invoiceItem) {

    //                 DB::rollBack();

    //                 return response()->json([

    //                     'status'  => false,

    //                     'message' =>
    //                     "Invoice item {$invoiceItemId} not found"

    //                 ], 400);
    //             }

    //             $totalAllocated =
    //                 collect($rows)->sum('qty');

    //             if ($totalAllocated > $invoiceItem->quantity) {

    //                 DB::rollBack();

    //                 return response()->json([

    //                     'status'  => false,

    //                     'message' =>
    //                     "Allocated qty exceeds invoice qty for invoice item ID {$invoiceItemId}"

    //                 ], 400);
    //             }
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | CREATE ALLOCATIONS
    //     |--------------------------------------------------------------------------
    //     */

    //         foreach ($request->warehouse_allocations as $allocation) {

    //             $invoiceItemId = $allocation['item_id'];

    //             $orderItem =
    //                 $createdOrderItems[$invoiceItemId] ?? null;

    //             if (!$orderItem) {

    //                 DB::rollBack();

    //                 return response()->json([

    //                     'status'  => false,

    //                     'message' =>
    //                     "Order item mapping not found"

    //                 ], 400);
    //             }

    //             OrderItemAllocation::create([

    //                 'order_id'           => $order->id,

    //                 'order_item_id'      => $orderItem->id,

    //                 'warehouse_id'       => $allocation['warehouse_id'],

    //                 'sales_executive_id' => auth()->id(),

    //                 'allocated_qty'      => $allocation['qty'],

    //                 'dispatched_qty'     => 0,

    //                 'status'             => 'pending',
    //             ]);
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | GET SELECTED WAREHOUSE IDS
    //     |--------------------------------------------------------------------------
    //     */

    //         $warehouseIds = collect($request->warehouse_allocations)
    //             ->pluck('warehouse_id')
    //             ->unique()
    //             ->toArray();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | GET WAREHOUSE USERS
    //     |--------------------------------------------------------------------------
    //     */

    //         $warehouseManagers = User::whereIn(
    //             'warehouse_id',
    //             $warehouseIds
    //         )
    //             ->where('is_active', 1)
    //             ->get();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | SEND NOTIFICATIONS
    //     |--------------------------------------------------------------------------
    //     */

    //         foreach ($warehouseManagers as $manager) {

    //             /*
    //         |--------------------------------------------------------------------------
    //         | GET MANAGER WAREHOUSE ALLOCATIONS
    //         |--------------------------------------------------------------------------
    //         */

    //             $managerAllocations = collect(
    //                 $request->warehouse_allocations
    //             )->where(
    //                 'warehouse_id',
    //                 $manager->warehouse_id
    //             );

    //             $totalItems =
    //                 $managerAllocations->sum('qty');

    //             /*
    //         |--------------------------------------------------------------------------
    //         | DATABASE NOTIFICATION
    //         |--------------------------------------------------------------------------
    //         */

    //             Notification::create([

    //                 'user_id'        => $manager->id,

    //                 'type'           => 'warehouse_item_allocation',

    //                 'title'          => 'New Item Allocation Request',

    //                 'message'        =>
    //                 "New allocation request received for Order #{$order->id}",

    //                 'reference_type' => 'order',

    //                 'reference_id'   => $order->id,

    //                 'priority'       => 'high',

    //                 'extra_data'     => json_encode([

    //                     'order_id'       => $order->id,

    //                     'order_number'   => $order->order_number,

    //                     'warehouse_id'   => $manager->warehouse_id,

    //                     'customer_name'  =>
    //                     optional($order->user)->full_name,

    //                     'customer_email' =>
    //                     optional($order->user)->email,

    //                     'total_items'    => $totalItems,

    //                     'allocated_by'   =>
    //                     auth()->user()->full_name ?? 'System',
    //                 ]),
    //             ]);

    //             /*
    //         |--------------------------------------------------------------------------
    //         | TWILIO WHATSAPP NOTIFICATION
    //         |--------------------------------------------------------------------------
    //         */

    //             try {

    //                 if (!empty($manager->phone)) {

    //                     $sid   = env('TWILIO_SID');
    //                     $token = env('TWILIO_AUTH_TOKEN');

    //                     $twilio = new Client($sid, $token);

    //                     $message =
    //                         "New Item Allocation Request\n\n"
    //                         . "Order ID: #{$order->id}\n"
    //                         . "Order Number: {$order->order_number}\n"
    //                         . "Warehouse ID: {$manager->warehouse_id}\n"
    //                         . "Items Allocated: {$totalItems}\n"
    //                         . "Allocated By: "
    //                         . (auth()->user()->full_name ?? 'System');

    //                     $twilio->messages->create(
    //                         "whatsapp:+91{$manager->phone}",
    //                         [
    //                             'from' => env('TWILIO_WHATSAPP_NUMBER'),
    //                             'body' => $message,
    //                         ]
    //                     );
    //                 }
    //             } catch (\Exception $twilioError) {

    //                 Log::error(
    //                     'Twilio WhatsApp Error: '
    //                         . $twilioError->getMessage()
    //                 );
    //             }
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | UPDATE INVOICE
    //     |--------------------------------------------------------------------------
    //     */

    //         $invoice->update([

    //             'order_id'  => $order->id,

    //             'pi_status' => 'converted_to_order'
    //         ]);

    //         DB::commit();

    //         return response()->json([

    //             'status'  => true,

    //             'message' =>
    //             'Order created successfully with warehouse allocations.',

    //             'data' => [

    //                 'order_id'       => $order->id,

    //                 'order_number'   => $order->order_number,

    //                 'invoice_id'     => $invoice->id,

    //                 'invoice_number' => $invoice->invoice_number,

    //                 'total_amount'   => $totalAmount,

    //                 'allocations'    => $request->warehouse_allocations
    //             ]

    //         ], 200);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([

    //             'status'  => false,

    //             'message' => $e->getMessage(),

    //             'line'    => $e->getLine()

    //         ], 500);
    //     }
    // }

    // public function generateItemLink(Request $request)
    // {
    //     try {

    //         $validated = $request->validate([
    //             'category_ids'   => 'required|array|min:1',
    //             'category_ids.*' => 'exists:categories,id',

    //             'item_ids'       => 'required|array|min:1',
    //             'item_ids.*'     => 'exists:items,id',
    //         ]);

    //         $items = Item::whereIn('id', $request->item_ids)
    //             ->select('id', 'slug', 'category_id')
    //             ->get();

    //         $data = [];

    //       foreach ($request->category_ids as $categoryId) {

    //             $categoryItems = Item::where('category_id', $categoryId)
    //                 ->select('slug')
    //                 ->get();

    //             $itemLinks = $categoryItems->map(function ($item) {
    //                 return 'https://www.markupdesigns.net/morovski-light-web/items/' . $item->slug;
    //             })->values();

    //             $data[] = [
    //                 'category_id'   => $categoryId,
    //                 'category_link' => 'https://www.markupdesigns.net/morovski-light-web/items?category_id=' . $categoryId,
    //                 'items'         => $itemLinks,
    //             ];
    //         }

    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Links generated successfully',
    //             'data'    => $data,
    //         ]);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'status'  => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    
    public function generateItemLink(Request $request)
    {
        try {
    
            $validated = $request->validate([
                'category_ids'   => 'required|array|min:1',
                'category_ids.*' => 'exists:categories,id',
    
                'item_ids'       => 'required|array|min:1',
                'item_ids.*'     => 'exists:items,id',
            ]);
    
            // Fetch only the requested items, grouped by category_id
            $itemsGrouped = Item::whereIn('id', $request->item_ids)
                ->select('id', 'slug', 'category_id')
                ->get()
                ->groupBy('category_id');
    
            $data = [];
    
            foreach ($request->category_ids as $categoryId) {
    
                // Get the items for this category (only the requested ones)
                $categoryItems = $itemsGrouped->get($categoryId, collect());
    
                $itemLinks = $categoryItems->map(function ($item) {
                    return 'https://www.markupdesigns.net/morovski-light-web/items/' . $item->slug;
                })->values();
    
                $data[] = [
                    'category_id'   => $categoryId,
                    'category_link' => 'https://www.markupdesigns.net/morovski-light-web/items?category_id=' . $categoryId,
                    'items'         => $itemLinks,
                ];
            }
    
            return response()->json([
                'status'  => true,
                'message' => 'Links generated successfully',
                'data'    => $data,
            ]);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // public function generateItemLink(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {

    //         $validated = $request->validate([

    //             'item_ids'   => 'required|array|min:1',

    //             'item_ids.*' => 'required|exists:items,id',
    //         ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | GET ITEMS
    //         |--------------------------------------------------------------------------
    //         */

    //         $items = Item::whereIn('id', $request->item_ids)->get();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | GENERATE SLUG
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($items->count() == 1) {

    //             // SINGLE ITEM
    //             $slug = $items->first()->slug;
    //         } else {

    //             // MULTIPLE ITEMS
    //             $slug = 'shared-' . Str::random(8);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | CREATE LINK
    //         |--------------------------------------------------------------------------
    //         */

    //         $shareLink = ItemShareLink::create([

    //             'user_id'   => Auth::id(),

    //             'link_code' => Str::random(20),

    //             'slug'      => $slug,
    //         ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | ATTACH ITEMS
    //         |--------------------------------------------------------------------------
    //         */

    //         $shareLink->items()->attach($request->item_ids);

    //         DB::commit();

    //         return response()->json([

    //             'status'  => true,

    //             'message' => 'Item link generated successfully',

    //             'data' => [

    //                 'link_id' => $shareLink->id,

    //                 'slug' => $shareLink->slug,

    //                 'link' => 'https://www.markupdesigns.net/morovski-light-web/items/' . $shareLink->slug,

    //                 'items_count' => count($request->item_ids),
    //             ]
    //         ]);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([

    //             'status'  => false,

    //             'message' => $e->getMessage()

    //         ], 500);
    //     }
    // }
    public function openSharedItems($slug, Request $request)
    {
        try {

            $shareLink = ItemShareLink::with([
                'items.images'
            ])
                ->where('slug', $slug)
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | UNIQUE CLICK CHECK
            |--------------------------------------------------------------------------
            */

            $alreadyClicked = ItemShareLinkClick::where(
                'item_share_link_id',
                $shareLink->id
            )
                ->where(
                    'ip_address',
                    $request->ip()
                )
                ->exists();

            /*
            |--------------------------------------------------------------------------
            | COUNT ONLY ONCE PER USER/IP
            |--------------------------------------------------------------------------
            */

            if (!$alreadyClicked) {

                ItemShareLinkClick::create([

                    'item_share_link_id' => $shareLink->id,

                    'ip_address' => $request->ip(),

                    'user_agent' => $request->userAgent(),
                ]);

                $shareLink->increment('unique_clicks');
            }

            /*
            |--------------------------------------------------------------------------
            | TOTAL CLICKS
            |--------------------------------------------------------------------------
            */

            $shareLink->increment('total_clicks');

            /*
            |--------------------------------------------------------------------------
            | REDIRECT TO FRONTEND
            |--------------------------------------------------------------------------
            */

            return redirect()->to(
                'https://www.markupdesigns.net/morovski-light-web/products/' . $shareLink->slug
            );
        } catch (\Exception $e) {

            return response()->json([

                'status'  => false,

                'message' => $e->getMessage()

            ], 500);
        }
    }

    // public function sendItemLinksMail(Request $request)
    // {
    //     try {

    //         $request->validate([

    //             'email'   => 'required|email',

    //             'links'   => 'required|array|min:1',

    //             'links.*' => 'required|url',
    //         ]);

    //         Mail::to($request->email)->send(
    //             new ShareItemLinkMail($request->links)
    //         );

    //         return response()->json([

    //             'status' => true,

    //             'message' => 'Mail sent successfully',
    //         ]);
    //     } catch (\Exception $e) {

    //         return response()->json([

    //             'status' => false,

    //             'message' => $e->getMessage()

    //         ], 500);
    //     }
    // }

    public function sendItemLinksMail(Request $request)
    {
        try {

            $request->validate([
                'email'   => 'required|email',
                'links'   => 'required|array|min:1',
                'links.*' => 'required|url',
            ]);

            Mail::to($request->email)
                ->send(new ShareItemLinkMail($request->links));

            return response()->json([
                'status'  => true,
                'message' => 'Mail sent successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // public function sendItemLinksWhatsApp(Request $request)
    // {
    //     try {

    //         $request->validate([

    //             'phone'   => 'required|string',

    //             'links'   => 'required|array|min:1',

    //             'links.*' => 'required|url',
    //         ]);

    //         $sid = env('TWILIO_SID');
    //         $token = env('TWILIO_AUTH_TOKEN');

    //         $client = new \Twilio\Rest\Client($sid, $token);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | PREPARE MESSAGE
    //         |--------------------------------------------------------------------------
    //         */

    //         $message = "🛍️ *Shared Item Links*\n\n";

    //         foreach ($request->links as $key => $link) {

    //             $message .= ($key + 1) . ". {$link}\n";
    //         }

    //         $message .= "\nThank you!";

    //         /*
    //         |--------------------------------------------------------------------------
    //         | SEND WHATSAPP MESSAGE
    //         |--------------------------------------------------------------------------
    //         */

    //         $client->messages->create(
    //             "whatsapp:+91" . $request->phone,
    //             [
    //                 "from" => "whatsapp:+14155238886",
    //                 "body" => $message
    //             ]
    //         );

    //         return response()->json([

    //             'status' => true,

    //             'message' => 'WhatsApp message sent successfully',
    //         ]);

    //     } catch (\Exception $e) {

    //         return response()->json([

    //             'status' => false,

    //             'message' => $e->getMessage()

    //         ], 500);
    //     }
    // }

    public function sendItemLinksWhatsApp(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|string',
                'pdf'   => 'required|file',
                // 'pdf'   => 'required|file|mimes:pdf|max:5120',
            ]);

            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $client = new \Twilio\Rest\Client($sid, $token);


            $message = "Your PDF document is attached.\n\nThank you!";

            $pdfPath = $request->file('pdf')->store('whatsapp_pdfs', 'public');
            $mediaUrl = asset('storage/' . $pdfPath);

            $client->messages->create(
                "whatsapp:+91" . $request->phone,
                [
                    "from"     => "whatsapp:+14155238886",
                    "body"     => $message,
                    "mediaUrl" => [$mediaUrl]
                ]
            );

            return response()->json([
                'status'  => true,
                'message' => 'WhatsApp PDF sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function allItems(Request $request)
    {
        try {

            $user = Auth::user();

            $items = Item::with(['images', 'category'])
                ->visibleForUser($user)
                ->latest()
                ->get();

            /*
            --------------------------------
            GET CART & WISHLIST IDS
            --------------------------------
            */
            $cartItemIds = [];
            $wishlistItemIds = [];

            if ($user) {

                $cartItemIds = CartItem::whereHas('cart', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                    ->pluck('item_id')
                    ->toArray();

                $wishlistItemIds = Wishlist::where('user_id', $user->id)
                    ->pluck('item_id')
                    ->toArray();
            }

            /*
            --------------------------------
            FORMAT RESPONSE
            --------------------------------
            */
            $data = $items->map(function ($item) use ($cartItemIds, $wishlistItemIds) {

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'sku' => $item->sku,
                    'price' => $item->price,
                    'description' => $item->description,
                    'quantity' => $item->quantity,

                    'stock_type' => $item->quantity > 20
                        ? 'in_stock'
                        : ($item->quantity > 0 ? 'low_stock' : 'out_of_stock'),

                    'thumbnail' => $item->images->first()
                        ? asset('storage/' . $item->images->first()->image)
                        : null,

                    'images' => $item->images->map(function ($img) {
                        return asset('storage/' . $img->image);
                    }),

                    'category' => $item->category ? [
                        'id' => $item->category->id,
                        'name' => $item->category->name,
                        'slug' => $item->category->slug,
                    ] : null,

                    'specifications' => $item->specifications,

                    'is_cart' => in_array($item->id, $cartItemIds),

                    'is_wishlist' => in_array($item->id, $wishlistItemIds),
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'All items fetched successfully',
                'total_items' => $data->count(),
                'data' => $data,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    // public function salesExecutiveNotifications(Request $request)
    // {
    //     try {

    //         $notifications = Notification::with('user')

    //             ->whereHas('user', function ($query) {

    //                 $query->where('account_type', 'sales_executive');
    //             });

    //         /*
    //         |--------------------------------------------------------------------------
    //         | FILTER BY USER ID
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($request->filled('user_id')) {

    //             $notifications->where('user_id', $request->user_id);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | FILTER BY READ STATUS
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($request->filled('is_read')) {

    //             $notifications->where('is_read', $request->is_read);
    //         }

    //         $notifications = $notifications
    //             ->latest()
    //             ->paginate($request->per_page ?? 20);

    //         return response()->json([

    //             'status' => true,

    //             'message' => 'Sales executive notifications fetched successfully',

    //             'total_notifications' => $notifications->total(),

    //             'data' => $notifications
    //         ]);
    //     } catch (\Exception $e) {

    //         return response()->json([

    //             'status' => false,

    //             'message' => $e->getMessage(),

    //             'line' => $e->getLine()

    //         ], 500);
    //     }
    // }

    public function salesExecutiveNotifications(Request $request)
    {
        try {
    
            $notifications = Notification::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('account_type', 'sales_executive');
                })
                ->where('user_id', auth()->id()) // Auth user notifications only
                ->where('created_at', '>=', Carbon::now()->subDays(60));
    
            /*
            |--------------------------------------------------------------------------
            | OPTIONAL FILTER BY READ STATUS
            |--------------------------------------------------------------------------
            */
    
            if ($request->filled('is_read')) {
                $notifications->where('is_read', $request->is_read);
            }
    
            $notifications = $notifications
                ->latest()
                ->paginate($request->per_page ?? 20);
    
            return response()->json([
                'status' => true,
                'message' => 'Sales executive notifications fetched successfully',
                'total_notifications' => $notifications->total(),
                'data' => $notifications
            ]);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function allocateItems(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

            $validated = $request->validate([

                'order_id' => ['required', 'exists:orders,id'],

                'allocations' => ['required', 'array', 'min:1'],

                'allocations.*.order_item_id' => [
                    'required',
                    'exists:order_items,id'
                ],

                'allocations.*.warehouse_id' => [
                    'required',
                    'exists:warehouses,id'
                ],

                'allocations.*.allocated_qty' => [
                    'required',
                    'integer',
                    'min:1'
                ],
            ]);

            $order = Order::with([
                'orderItems.product',
                'user'
            ])->findOrFail($validated['order_id']);

            /*
        |--------------------------------------------------------------------------
        | VALIDATE TOTAL ALLOCATION
        |--------------------------------------------------------------------------
        */

            $grouped = collect($validated['allocations'])
                ->groupBy('order_item_id');

            foreach ($grouped as $orderItemId => $rows) {

                $orderItem = OrderItem::findOrFail($orderItemId);

                $totalAllocated = collect($rows)->sum('allocated_qty');

                if ($totalAllocated > $orderItem->quantity) {

                    return response()->json([

                        'status' => false,

                        'message' => "Allocated qty exceeds ordered qty for item ID {$orderItemId}"

                    ], 422);
                }
            }

            /*
        |--------------------------------------------------------------------------
        | STORE ALLOCATIONS
        |--------------------------------------------------------------------------
        */

            foreach ($validated['allocations'] as $allocation) {

                OrderItemAllocation::create([

                    'order_id'          => $order->id,

                    'order_item_id'     => $allocation['order_item_id'],

                    'warehouse_id'      => $allocation['warehouse_id'],

                    'sales_executive_id' => auth()->id(),

                    'allocated_qty'     => $allocation['allocated_qty'],

                    'status'            => 'pending',
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | GET WAREHOUSE MANAGERS
        |--------------------------------------------------------------------------
        */

            $warehouseManagers = User::join('role_users', 'users.id', '=', 'role_users.user_id')
                ->join('roles', 'roles.id', '=', 'role_users.role_id')
                ->where('roles.slug', 'warehouse_manager')
                ->select('users.*')
                ->get();

            /*
        |--------------------------------------------------------------------------
        | SEND NOTIFICATIONS
        |--------------------------------------------------------------------------
        */

            foreach ($warehouseManagers as $manager) {

                /*
            |--------------------------------------------------------------------------
            | DATABASE NOTIFICATION
            |--------------------------------------------------------------------------
            */

                Notification::create([

                    'user_id'        => $manager->id,

                    'type'           => 'warehouse_item_allocation',

                    'title'          => 'New Item Allocation Request',

                    'message'        => "New allocation request received for Order #{$order->id}",

                    'reference_type' => 'order',

                    'reference_id'   => $order->id,

                    'priority'       => 'high',

                    'extra_data'     => json_encode([

                        'order_id'          => $order->id,

                        'customer_name'     => optional($order->user)->full_name,

                        'customer_email'    => optional($order->user)->email,

                        'total_items'       => count($validated['allocations']),

                        'allocated_by'      => auth()->user()->name ?? 'System',
                    ]),
                ]);

                /*
            |--------------------------------------------------------------------------
            | TWILIO WHATSAPP NOTIFICATION
            |--------------------------------------------------------------------------
            */

                try {

                    if (!empty($manager->phone)) {

                        $sid    = env('TWILIO_SID');
                        $token  = env('TWILIO_AUTH_TOKEN');

                        $twilio = new Client($sid, $token);

                        $message = "New Item Allocation Request\n\n"
                            . "Order ID: #{$order->id}\n"
                            . "Customer: " . (optional($order->user)->full_name ?? 'N/A') . "\n"
                            . "Items Allocated: " . count($validated['allocations']) . "\n"
                            . "Allocated By: " . (auth()->user()->name ?? 'System');

                        $twilio->messages->create(
                            "whatsapp:+91{$manager->phone}",
                            [
                                'from' => env('TWILIO_WHATSAPP_FROM'),
                                'body' => $message,
                            ]
                        );
                    }
                } catch (\Exception $twilioError) {

                    Log::error('Twilio WhatsApp Error: ' . $twilioError->getMessage());
                }
            }

            DB::commit();

            return response()->json([

                'status' => true,

                'message' => 'Items allocated successfully'

            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status' => false,

                'message' => $e->getMessage()

            ], 500);
        }
    }
}