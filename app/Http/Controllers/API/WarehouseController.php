<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\BackInStockMail;
use App\Mail\OrderPlacedMail;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\OrderItemDelivery;
use App\Models\User;
// use App\Models\OrderItem;
use App\Models\DamagedItem;
use App\Models\Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\OrderItem;
use App\Models\PackedOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\BackInStockNotification;
use App\Models\OrderItemAllocation;
use Illuminate\Support\Facades\Mail;
use App\Mail\ItemBackInStockMail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ReplacementRequest;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Carbon\Carbon;

class WarehouseController extends Controller
{

    public function warehouseList(Request $request)
    {
        try {
            $query = Warehouse::query();

            // Optional: search by name or location
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            }

            // Optional: pagination
            $perPage = $request->get('per_page', 10);
            $warehouses = $query->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'Warehouses fetched successfully',
                'data' => $warehouses->items(),
                'pagination' => [
                    'total' => $warehouses->total(),
                    'per_page' => $warehouses->perPage(),
                    'current_page' => $warehouses->currentPage(),
                    'last_page' => $warehouses->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendWhatsAppStock($phone, $userName, $itemName)
    {
        try {
            $sid   = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');

            $client = new \Twilio\Rest\Client($sid, $token);

            $message = "ðŸ”” *Item Back in Stock!*\n\n"
                . "Hi {$userName},\n\n"
                . "The item *{$itemName}* you requested is now available.\n\n"
                . "You can place your order now before it goes out of stock again!\n\n"
                . "ðŸ‘‰ Visit our store now.";

            $client->messages->create(
                "whatsapp:+91{$phone}",
                [
                    "from" => "whatsapp:+14155238886",
                    "body" => $message
                ]
            );
        } catch (\Exception $e) {
            Log::error('WhatsApp Stock Alert Failed: ' . $e->getMessage());
        }
    }

    private function cleanUtf8($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'cleanUtf8'], $data);
        }

        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }

        return $data;
    }

    private function safeMessage($message)
    {
        return mb_convert_encoding($message, 'UTF-8', 'UTF-8');
    }

    public function allocatedItems(Request $request)
    {
        $warehouseId = auth()->user()->warehouse_id;

        $allocations = OrderItemAllocation::with([

            'order',

            'orderItem.item',

            'order.user'

        ])
            ->where('warehouse_id', $warehouseId)

            ->whereIn('status', [
                'pending',
                'partial_dispatch'
            ])

            ->get();

        return response()->json([

            'status' => true,

            'data' => $allocations

        ]);
    }

    public function dispatchItem(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */

            $validated = $request->validate([

                'order_item_ids' => [
                    'required',
                    'array',
                    'min:1'
                ],

                'order_item_ids.*' => [
                    'required',
                    'exists:order_items,id'
                ],

                'warehouse_id' => [
                    'required',
                    'exists:warehouses,id'
                ],

                'transport_mode' => [
                    'nullable',
                    'in:Road,Rail,Air,Ship'
                ],

                'vehicle_number' => [
                    'nullable',
                    'string',
                    'max:50'
                ],

                'approx_distance' => [
                    'nullable',
                    'numeric',
                    'gte:0'
                ],

                'eway_bill_number' => [
                    'nullable',
                    'string',
                    'max:255'
                ],

                'tracking_link' => [
                    'nullable',
                    'url'
                ],

            ], [

                'order_item_ids.required' => 'Order item IDs are required.',
                'order_item_ids.array' => 'Order item IDs must be an array.',
                'order_item_ids.min' => 'At least one order item must be selected.',

                'order_item_ids.*.required' => 'Order item ID is required.',
                'order_item_ids.*.exists' => 'One or more selected order items do not exist.',

                'warehouse_id.required' => 'Warehouse ID is required.',
                'warehouse_id.exists' => 'Selected warehouse does not exist.',

                'transport_mode.in' => 'Transport mode must be Road, Rail, Air, or Ship.',

                'vehicle_number.string' => 'Vehicle number must be a valid string.',
                'vehicle_number.max' => 'Vehicle number cannot exceed 50 characters.',

                'approx_distance.numeric' => 'Approx distance must be a number.',
                'approx_distance.gte' => 'Approx distance cannot be negative.',

                'eway_bill_number.string' => 'E-Way Bill Number must be a valid string.',
                'eway_bill_number.max' => 'E-Way Bill Number cannot exceed 255 characters.',

                'tracking_link.url' => 'Tracking link must be a valid URL.',
            ]);

            $dispatchResponses = [];

            $processedOrders = [];

            /*
            |--------------------------------------------------------------------------
            | LOOP ORDER ITEMS
            |--------------------------------------------------------------------------
            */

            foreach ($validated['order_item_ids'] as $orderItemId) {

                /*
                |--------------------------------------------------------------------------
                | GET ORDER ITEM
                |--------------------------------------------------------------------------
                */

                $orderItem = OrderItem::with([

                    'order',

                    'item',

                    'order.user'

                ])->find($orderItemId);

                if (!$orderItem) {

                    DB::rollBack();

                    return response()->json([

                        'status'  => false,

                        'message' => 'Order item not found',

                        'data'    => [
                            'order_item_id' => $orderItemId
                        ]

                    ], 404);
                }

                $order = $orderItem->order;

                $processedOrders[$order->id] = $order;

                /*
                |--------------------------------------------------------------------------
                | GET ALLOCATION
                |--------------------------------------------------------------------------
                */

                $allocation = OrderItemAllocation::where(
                    'order_item_id',
                    $orderItem->id
                )
                    ->where(
                        'warehouse_id',
                        $validated['warehouse_id']
                    )
                    ->first();

                if (!$allocation) {

                    DB::rollBack();

                    return response()->json([

                        'status'  => false,

                        'message' =>
                        'Allocation not found for this warehouse',

                        'data' => [

                            'order_item_id' => $orderItem->id,

                            'item_name'     =>
                            $orderItem->item->name ?? null,

                            'warehouse_id'  =>
                            $validated['warehouse_id']
                        ]

                    ], 400);
                }

                /*
                |--------------------------------------------------------------------------
                | CHECK REMAINING ALLOCATION
                |--------------------------------------------------------------------------
                */

                $remainingQty =

                    $allocation->allocated_qty -

                    $allocation->dispatched_qty;

                if ($remainingQty <= 0) {

                    DB::rollBack();

                    return response()->json([

                        'status'  => false,

                        'message' =>
                        'Allocated quantity already dispatched',

                        'data' => [

                            'allocation_id' =>
                            $allocation->id,

                            'allocated_qty' =>
                            $allocation->allocated_qty,

                            'already_dispatched_qty' =>
                            $allocation->dispatched_qty
                        ]

                    ], 400);
                }

                /*
                |--------------------------------------------------------------------------
                | CHECK WAREHOUSE STOCK
                |--------------------------------------------------------------------------
                */

                $warehouseItem = WarehouseItem::where(
                    'warehouse_id',
                    $validated['warehouse_id']
                )
                    ->where(
                        'item_id',
                        $orderItem->item_id
                    )
                    ->first();

                if (!$warehouseItem) {

                    DB::rollBack();

                    return response()->json([

                        'status'  => false,

                        'message' =>
                        'Warehouse stock record not found',

                        'data' => [

                            'warehouse_id' =>
                            $validated['warehouse_id'],

                            'item_id' =>
                            $orderItem->item_id
                        ]

                    ], 404);
                }

                if ($warehouseItem->quantity <= 0) {

                    DB::rollBack();

                    return response()->json([

                        'status'  => false,

                        'message' =>
                        'Item out of stock in warehouse',

                        'data' => [

                            'order_item_id' =>
                            $orderItem->id,

                            'item_name' =>
                            $orderItem->item->name ?? null,

                            'warehouse_id' =>
                            $validated['warehouse_id'],

                            'available_stock' =>
                            $warehouseItem->quantity
                        ]

                    ], 400);
                }

                /*
                |--------------------------------------------------------------------------
                | FINAL DISPATCH QTY
                |--------------------------------------------------------------------------
                */

                $dispatchQty = min(
                    $warehouseItem->quantity,
                    $remainingQty
                );

                /*
                |--------------------------------------------------------------------------
                | CREATE INVOICE
                |--------------------------------------------------------------------------
                */

                $invoice = Invoice::create([

                    'order_id'       => $order->id,

                    'client_id'      => $order->user_id ?? null,

                    'invoice_number' =>

                    'INV-' .
                        now()->timestamp .
                        '-' .
                        $orderItem->id,

                    'type'           => 'invoice',

                    'pi_status'      => 'dispatched',

                    'valid_until'    => now()->addDays(15),

                    'total_amount'   =>

                    $dispatchQty *
                        $orderItem->unit_price,

                    'amount_paid'    => $order->paid_amount,
                ]);

                /*
                |--------------------------------------------------------------------------
                | STORE ORDER DETAILS
                |--------------------------------------------------------------------------
                */

                OrderDetail::updateOrCreate(

                    [
                        'order_id' => $order->id
                    ],

                    [

                        'transport_mode'   =>
                        $request->transport_mode ?? 'Road',

                        'vehicle_number'   =>
                        $request->vehicle_number,

                        'approx_distance'  =>
                        $request->approx_distance,

                        'eway_bill_number' =>
                        $request->eway_bill_number,

                        'tracking_link'    =>
                        $request->tracking_link,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | CREATE INVOICE ITEM
                |--------------------------------------------------------------------------
                */

                InvoiceItem::create([

                    'invoice_id'             => $invoice->id,

                    'order_item_id'          => $orderItem->id,

                    'item_id'                => $orderItem->item_id,

                    'quantity'               => $dispatchQty,

                    'unit_price'             => $orderItem->unit_price,

                    'line_discount_percent'  =>
                    $orderItem->discount_percent ?? 0,

                    'total_price'            =>

                    $dispatchQty *
                        $orderItem->unit_price,
                ]);

                /*
                |--------------------------------------------------------------------------
                | DELIVERY ENTRY
                |--------------------------------------------------------------------------
                */

                OrderItemDelivery::create([

                    'order_item_id' => $orderItem->id,

                    'invoice_id'    => $invoice->id,

                    'quantity'      => $dispatchQty,

                    'status'        => 'shipped',

                    'shipped_at'    => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE ALLOCATION DISPATCHED QTY
                |--------------------------------------------------------------------------
                */

                $allocation->increment(
                    'dispatched_qty',
                    $dispatchQty
                );

                /*
                |--------------------------------------------------------------------------
                | UPDATE ORDER ITEM DISPATCHED QTY
                |--------------------------------------------------------------------------
                */

                $orderItem->increment(
                    'dispatched_qty',
                    $dispatchQty
                );

                $allocation->refresh();

                $orderItem->refresh();

                /*
                |--------------------------------------------------------------------------
                | UPDATE ALLOCATION STATUS
                |--------------------------------------------------------------------------
                */

                if (
                    $allocation->dispatched_qty >=
                    $allocation->allocated_qty
                ) {

                    $allocationStatus = 'dispatched';
                } else {

                    $allocationStatus = 'partial_dispatch';
                }

                $allocation->update([

                    'status' => $allocationStatus
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE ORDER ITEM STATUS
                |--------------------------------------------------------------------------
                */

                if (
                    $orderItem->dispatched_qty >=
                    $orderItem->quantity
                ) {

                    $itemStatus = 'dispatched';
                } else {

                    $itemStatus = 'partial_dispatch';
                }

                $orderItem->update([

                    'status' => $itemStatus
                ]);

                /*
                |--------------------------------------------------------------------------
                | DEDUCT STOCK
                |--------------------------------------------------------------------------
                */

                $warehouseItem->decrement(
                    'quantity',
                    $dispatchQty
                );

                Item::where(
                    'id',
                    $orderItem->item_id
                )
                    ->decrement(
                        'quantity',
                        $dispatchQty
                    );

                /*
                |--------------------------------------------------------------------------
                | STORE RESPONSE
                |--------------------------------------------------------------------------
                */

                $dispatchResponses[] = [

                    'allocation_id'        => $allocation->id,

                    'invoice_id'           => $invoice->id,

                    'invoice_number'       => $invoice->invoice_number,

                    'warehouse_id'         => $validated['warehouse_id'],

                    'order_id'             => $order->id,

                    'order_item_id'        => $orderItem->id,

                    'item_name'            =>
                    $orderItem->item->name ?? null,

                    'allocated_qty'        =>
                    $allocation->allocated_qty,

                    'current_dispatch_qty' =>
                    $dispatchQty,

                    'total_dispatched_qty' =>
                    $allocation->dispatched_qty,

                    'remaining_qty'        =>

                    $allocation->allocated_qty -
                        $allocation->dispatched_qty,

                    'allocation_status'    =>
                    $allocationStatus,

                    'item_status'          =>
                    $itemStatus,

                    'warehouse_stock_left' =>
                    $warehouseItem->fresh()->quantity,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE ORDER + PROFORMA INVOICE STATUS
            |--------------------------------------------------------------------------
            */

            foreach ($processedOrders as $order) {

                $totalItems = OrderItem::where(
                    'order_id',
                    $order->id
                )->count();

                $fullyDispatchedItems = OrderItem::where(
                    'order_id',
                    $order->id
                )
                    ->where(
                        'status',
                        'dispatched'
                    )
                    ->count();

                /*
    |--------------------------------------------------------------------------
    | UPDATE ORDER STATUS
    |--------------------------------------------------------------------------
    */

                if ($fullyDispatchedItems == $totalItems) {

                    $order->update([

                        'order_status' => 'dispatched'
                    ]);

                    $invoiceStatus = 'dispatched';
                } else {

                    $order->update([

                        'order_status' => 'partial_dispatch'
                    ]);

                    $invoiceStatus = 'partial_dispatch';
                }

                /*
                |--------------------------------------------------------------------------
                | UPDATE PROFORMA INVOICE STATUS
                |--------------------------------------------------------------------------
                */

                $proformaInvoice = Invoice::where(
                    'order_id',
                    $order->id
                )
                    ->where(
                        'type',
                        'proforma'
                    )
                    ->first();

                if ($proformaInvoice) {

                    $proformaInvoice->update([

                        'pi_status' => $invoiceStatus
                    ]);
                }
            }

            DB::commit();

            return response()->json([

                'status'  => true,

                'message' => 'Dispatch completed successfully',

                'data'    => $dispatchResponses

            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status'  => false,

                'message' => 'Something went wrong',

                'error'   => $e->getMessage(),

                'line'    => $e->getLine()

            ], 500);
        }
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx'
        ]);

        DB::beginTransaction();

        try {

            $file = fopen($request->file('file')->getRealPath(), 'r');

            if (!$file) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unable to read file'
                ], 400);
            }

            $header = fgetcsv($file);
            $header = array_map(fn($h) => strtolower(trim($h)), $header);
            dd($header);
            $rowNumber = 1;

            while (($row = fgetcsv($file)) !== false) {

                $rowNumber++;

                if (count(array_filter($row)) === 0) {
                    continue;
                }

                $row = array_combine($header, $row);

                $validator = Validator::make($row, [
                    'name' => 'required|string|max:255',
                    'sku' => 'required|string|unique:items,sku',
                    'price' => 'required|numeric|min:0',
                    'quantity' => 'required|integer|min:0',
                    'category_id' => 'required|exists:categories,id',
                    'wattage' => 'nullable|string',
                    'color_temperature' => 'nullable|string',
                    'attributes' => 'nullable|string',
                    'image_urls' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    DB::rollBack();

                    return response()->json([
                        'status' => false,
                        'message' => "Error on row {$rowNumber}",
                        'errors' => $validator->errors()
                    ], 422);
                }

                $item = Item::create([
                    'name' => $row['name'],
                    'slug' => Str::slug($row['name']) . '-' . uniqid(),
                    'sku' => $row['sku'],
                    'price' => $row['price'],
                    'quantity' => $row['quantity'],
                    'category_id' => $row['category_id'],
                    'specifications' => [
                        'wattage' => $row['wattage'] ?? null,
                        'color_temperature' => $row['color_temperature'] ?? null,
                        'attributes' => !empty($row['attributes'])
                            ? array_map('trim', explode(',', $row['attributes']))
                            : []
                    ]
                ]);

                // IMAGE HANDLING
                if (!empty($row['image_urls'])) {

                    foreach (explode('|', $row['image_urls']) as $url) {

                        $url = trim($url);
                        if (!$url) continue;

                        try {
                            $response = Http::timeout(10)->get($url);

                            if (!$response->successful()) continue;

                            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';

                            $filename = 'items/' . uniqid() . '.' . $ext;

                            Storage::disk('public')->put($filename, $response->body());

                            $item->images()->create([
                                'image' => $filename
                            ]);
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }

            fclose($file);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Bulk items uploaded successfully'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx'
        ]);

        DB::beginTransaction();

        try {
            $file = fopen($request->file('file')->getRealPath(), 'r');

            if (!$file) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unable to read file'
                ], 400);
            }

            $header = fgetcsv($file);

            if (!$header) {
                return response()->json([
                    'status' => false,
                    'message' => 'CSV header missing'
                ], 400);
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            $header = array_map(fn($h) => strtolower(trim($h)), $header);

            /*
         CHECK REQUIRED COLUMN
        */
            if (!in_array('sku', $header)) {
                return response()->json([
                    'status' => false,
                    'message' => 'SKU column is required in file'
                ], 422);
            }

            $rowNumber = 1;
            $errors = [];
            $updatedCount = 0;

            while (($row = fgetcsv($file)) !== false) {

                $rowNumber++;

                if (count(array_filter($row)) === 0) continue;

                $row = array_pad($row, count($header), null);

                if (count($header) !== count($row)) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'error' => 'Column mismatch'
                    ];
                    continue;
                }

                $row = array_combine($header, $row);
                $row = $this->cleanUtf8($row);

                /*
            VALIDATION (SKU BASED)
            */
                $validator = Validator::make($row, [
                    'sku'         => 'required|exists:items,sku',
                    'name'        => 'required|string|max:255',
                    'price'       => 'required|numeric|min:0',
                    'quantity'    => 'required|integer|min:0',
                    'category_id' => 'required|exists:categories,id',
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'row'   => $rowNumber,
                        'error' => $validator->errors()->all()
                    ];
                    continue;
                }

                /*
             FIND ITEM BY SKU
            */
                $item = Item::where('sku', $row['sku'])->first();

                if (!$item) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'error' => 'Item not found for SKU: ' . $row['sku']
                    ];
                    continue;
                }

                $oldQty = $item->quantity;
                $newQty = (int) $row['quantity'];

                /*
             UPDATE ITEM
            */
                $item->update([
                    'name'        => $row['name'],
                    'slug'        => Str::slug($row['name']) . '-' . uniqid(),
                    'price'       => $row['price'],
                    'quantity'    => $newQty,
                    'category_id' => $row['category_id'],
                ]);

                /*
            STOCK INCREASE â†’ SKU BASED MATCH
            */
                if ($newQty > $oldQty) {

                    $pendingUsers = DB::table('pending_order_items')
                        ->where('item_sku', $item->sku) // ðŸ”¥ KEY CHANGE
                        ->where('pending_qty', '>', 0)
                        ->pluck('user_id')
                        ->unique();

                    if ($pendingUsers->isNotEmpty()) {

                        $users = User::whereIn('id', $pendingUsers)->get();

                        foreach ($users as $user) {

                            /*
                         MAIL
                        */
                            try {
                                Mail::to($user->email)->send(
                                    new ItemBackInStockMail($user, $item)
                                );
                            } catch (\Exception $e) {
                                Log::error('Mail failed: ' . $e->getMessage());
                            }

                            /*
                        WHATSAPP
                        */
                            try {
                                if (!empty($user->phone)) {
                                    $this->sendWhatsAppStock(
                                        $user->phone,
                                        $user->name,
                                        $item->name
                                    );
                                }
                            } catch (\Exception $e) {
                                Log::error('WhatsApp failed: ' . $e->getMessage());
                            }
                        }
                    }
                }

                $updatedCount++;
            }

            fclose($file);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => "Bulk update completed. {$updatedCount} item(s) updated.",
                'errors'  => $errors
            ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $this->safeMessage($e->getMessage())
            ], 500);
        }
    }


    // public function getPendingB2BOrders(Request $request)
    // {
    //     try {

    //         $authUser = Auth::user();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | AUTH USER WAREHOUSE
    //     |--------------------------------------------------------------------------
    //     */

    //         $warehouse = Warehouse::find($authUser->warehouse_id);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | GET PENDING B2B ORDERS
    //     |--------------------------------------------------------------------------
    //     */

    //         $orders = Order::with([
    //             'user.businessProfile',
    //             'address',
    //             'items.item.images'
    //         ])

    //             /*
    //         |--------------------------------------------------------------------------
    //         | ONLY B2B USERS
    //         |--------------------------------------------------------------------------
    //         */

    //             ->whereHas('user', function ($query) {

    //                 $query->where('account_type', 'b2b');
    //             })

    //             /*
    //         |--------------------------------------------------------------------------
    //         | ONLY UNPAID ORDERS
    //         |--------------------------------------------------------------------------
    //         */

    //             ->where('payment_status', '!=', 'paid');

    //         /*
    //     |--------------------------------------------------------------------------
    //     | SEARCH BY ORDER NUMBER / CUSTOMER NAME
    //     |--------------------------------------------------------------------------
    //     */

    //         if ($request->filled('search')) {

    //             $search = $request->search;

    //             $orders->where(function ($query) use ($search) {

    //                 $query->where('order_number', 'like', '%' . $search . '%')

    //                     ->orWhereHas('user', function ($q) use ($search) {

    //                         $q->where('full_name', 'like', '%' . $search . '%');
    //                     });
    //             });
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | FILTER BY ORDER STATUS
    //     |--------------------------------------------------------------------------
    //     */

    //         if ($request->filled('order_status')) {

    //             $orders->where('order_status', $request->order_status);
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | OLDEST ORDER FIRST
    //     |--------------------------------------------------------------------------
    //     */

    //         $orders = $orders
    //             ->orderBy('created_at', 'asc')
    //             ->paginate($request->per_page ?? 10);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | RESPONSE
    //     |--------------------------------------------------------------------------
    //     */

    //         return response()->json([

    //             'status'  => true,

    //             'message' => 'Pending B2B orders fetched successfully.',

    //             'total_orders' => $orders->total(),



    //             'data' => collect($orders->items())->map(function ($order) use ($warehouse) {

    //                 return [

    //                     'order_id'         => $order->id,

    //                     'order_number'     => $order->order_number,

    //                     'customer_id'      => $order->user?->id,

    //                     'customer_name'    => $order->user?->full_name,

    //                     'company_name'     => $order->user?->businessProfile?->company_name,

    //                     'payment_status'   => $order->payment_status,

    //                     'order_status'     => $order->order_status,

    //                     'subtotal'         => $order->subtotal,

    //                     'b2b_discount'     => $order->b2b_discount,

    //                     'shipping_charges' => $order->shipping_charges,

    //                     'gst_amount'       => $order->gst_amount,

    //                     'total_amount'     => $order->total_amount,

    //                     'paid_amount'      => $order->paid_amount,

    //                     'due_amount'       => $order->due_amount,

    //                     'order_date'       => $order->order_date,

    //                     /*
    //                 |--------------------------------------------------------------------------
    //                 | SHIPPING ADDRESS
    //                 |--------------------------------------------------------------------------
    //                 */

    //                     'shipping_address' => $order->address ? [

    //                         'id'             => $order->address->id,
    //                         'full_name'      => $order->address->full_name,
    //                         'phone'          => $order->address->phone,
    //                         'address_line_1' => $order->address->address_line_1,
    //                         'address_line_2' => $order->address->address_line_2,
    //                         'city'           => $order->address->city,
    //                         'state'          => $order->address->state,
    //                         'postal_code'    => $order->address->postal_code,
    //                         'country'        => $order->address->country,

    //                     ] : null,

    //                     /*
    //                 |--------------------------------------------------------------------------
    //                 | ORDER ITEMS
    //                 |--------------------------------------------------------------------------
    //                 */

    //                     'items' => $order->items->map(function ($item) {

    //                         return [

    //                             'order_item_id' => $item->id,

    //                             'item_id'       => $item->item_id,

    //                             'item_name'     => $item->item?->name,

    //                             'sku'           => $item->item?->sku,

    //                             'quantity'      => $item->quantity,

    //                             'unit_price'    => $item->unit_price,

    //                             'total_price'   => $item->total_price,

    //                             'status'        => $item->status,

    //                             'images'        => $item->item?->images,
    //                         ];
    //                     }),

    //                     /*
    //                 |--------------------------------------------------------------------------
    //                 | AUTH USER WAREHOUSE
    //                 |--------------------------------------------------------------------------
    //                 */

    //                     'warehouse' => $warehouse ? [
    //                         'warehouse_id'   => $warehouse->id,
    //                         'warehouse_name' => $warehouse->name,
    //                     ] : null,
    //                 ];
    //             }),

    //             'pagination' => [

    //                 'current_page' => $orders->currentPage(),
    //                 'last_page'    => $orders->lastPage(),
    //                 'per_page'     => $orders->perPage(),
    //                 'total'        => $orders->total(),
    //             ]
    //         ]);
    //     } catch (\Exception $e) {

    //         return response()->json([

    //             'status'  => false,

    //             'message' => $e->getMessage(),

    //             'line'    => $e->getLine()

    //         ], 500);
    //     }
    // }


    // public function getPendingB2BOrders(Request $request)
    // {
    //     try {

    //         $authUser = Auth::user();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | AUTH USER WAREHOUSE
    //         |--------------------------------------------------------------------------
    //         */

    //         $warehouse = Warehouse::find($authUser->warehouse_id);
    //         if (!$warehouse) {

    //             return response()->json([

    //                 'status'  => false,

    //                 'message' => 'Warehouse not assigned to this user'

    //             ], 404);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | GET ORDERS ASSIGNED TO THIS WAREHOUSE
    //         |--------------------------------------------------------------------------
    //         */

    //         $orders = Order::with([

    //                 'user.businessProfile',

    //                 'address',

    //                 'items.item.images',

    //                 'items.allocations' => function ($query) use ($warehouse) {

    //                     $query->where(
    //                         'warehouse_id',
    //                         $warehouse->id
    //                     );
    //                 },

    //                 'items.allocations.warehouse',

    //                 'items.allocations.salesExecutive'
    //             ])

    //             /*
    //             |--------------------------------------------------------------------------
    //             | ONLY B2B USERS
    //             |--------------------------------------------------------------------------
    //             */

    //             ->whereHas('user', function ($query) {

    //                 $query->where('account_type', 'b2b');
    //             })

    //             /*
    //             |--------------------------------------------------------------------------
    //             | ONLY THOSE ORDERS
    //             | WHICH HAVE ALLOCATIONS FOR THIS WAREHOUSE
    //             |--------------------------------------------------------------------------
    //             */

    //             ->whereHas('items.allocations', function ($query) use ($warehouse) {

    //                 $query->where(
    //                     'warehouse_id',
    //                     $warehouse->id
    //                 );
    //             });
    //                         dd($orders);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | SEARCH FILTER
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($request->filled('search')) {

    //             $search = $request->search;

    //             $orders->where(function ($query) use ($search) {

    //                 $query->where(
    //                         'order_number',
    //                         'like',
    //                         '%' . $search . '%'
    //                     )

    //                     ->orWhereHas('user', function ($q) use ($search) {

    //                         $q->where(
    //                             'full_name',
    //                             'like',
    //                             '%' . $search . '%'
    //                         );
    //                     });
    //             });
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | ORDER STATUS FILTER
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($request->filled('order_status')) {

    //             $orders->where(
    //                 'order_status',
    //                 $request->order_status
    //             );
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | PAGINATION
    //         |--------------------------------------------------------------------------
    //         */

    //         $orders = $orders
    //             ->orderBy('created_at', 'asc')
    //             ->paginate($request->per_page ?? 10);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | RESPONSE
    //         |--------------------------------------------------------------------------
    //         */

    //         return response()->json([

    //             'status'  => true,

    //             'message' =>
    //                 'Warehouse assigned B2B orders fetched successfully',

    //             'total_orders' => $orders->total(),

    //             'warehouse' => [

    //                 'warehouse_id'   => $warehouse->id,

    //                 'warehouse_name' => $warehouse->name,
    //             ],

    //             'data' => collect($orders->items())->map(function ($order) use ($warehouse) {

    //                 return [

    //                     'order_id'         => $order->id,

    //                     'order_number'     => $order->order_number,

    //                     'payment_status'   => $order->payment_status,

    //                     'order_status'     => $order->order_status,

    //                     'subtotal'         => $order->subtotal,

    //                     'b2b_discount'     => $order->b2b_discount,

    //                     'shipping_charges' => $order->shipping_charges,

    //                     'gst_amount'       => $order->gst_amount,

    //                     'total_amount'     => $order->total_amount,

    //                     'paid_amount'      => $order->paid_amount,

    //                     'due_amount'       => $order->due_amount,

    //                     'order_date'       => $order->order_date,

    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | CUSTOMER DETAILS
    //                     |--------------------------------------------------------------------------
    //                     */

    //                     'customer' => [

    //                         'customer_id'   => $order->user?->id,

    //                         'customer_name' => $order->user?->full_name,

    //                         'email'         => $order->user?->email,

    //                         'phone'         => $order->user?->phone,

    //                         'company_name'  =>
    //                             $order->user?->businessProfile?->company_name,
    //                     ],

    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | SHIPPING ADDRESS
    //                     |--------------------------------------------------------------------------
    //                     */

    //                     'shipping_address' => $order->address ? [

    //                         'id'             => $order->address->id,

    //                         'full_name'      => $order->address->full_name,

    //                         'phone'          => $order->address->phone,

    //                         'address_line_1' => $order->address->address_line_1,

    //                         'address_line_2' => $order->address->address_line_2,

    //                         'city'           => $order->address->city,

    //                         'state'          => $order->address->state,

    //                         'postal_code'    => $order->address->postal_code,

    //                         'country'        => $order->address->country,

    //                     ] : null,

    //                     /*
    //                     |--------------------------------------------------------------------------
    //                     | ONLY ITEMS ASSIGNED TO THIS WAREHOUSE
    //                     |--------------------------------------------------------------------------
    //                     */

    //                     'items' => $order->items
    //                         ->filter(function ($item) use ($warehouse) {

    //                             return $item->allocations
    //                                 ->where(
    //                                     'warehouse_id',
    //                                     $warehouse->id
    //                                 )
    //                                 ->count() > 0;
    //                         })
    //                         ->values()
    //                         ->map(function ($item) use ($warehouse) {

    //                             $allocation = $item->allocations
    //                                 ->where(
    //                                     'warehouse_id',
    //                                     $warehouse->id
    //                                 )
    //                                 ->first();

    //                             return [

    //                                 'order_item_id' => $item->id,

    //                                 'item_id'       => $item->item_id,

    //                                 'item_name'     => $item->item?->name,

    //                                 'sku'           => $item->item?->sku,

    //                                 'ordered_qty'   => $item->quantity,

    //                                 'unit_price'    => $item->unit_price,

    //                                 'total_price'   => $item->total_price,

    //                                 'item_status'   => $item->status,

    //                                 /*
    //                                 |--------------------------------------------------------------------------
    //                                 | ALLOCATION DETAILS
    //                                 |--------------------------------------------------------------------------
    //                                 */

    //                                 'allocation' => [

    //                                     'allocation_id' =>
    //                                         $allocation?->id,

    //                                     'allocated_qty' =>
    //                                         $allocation?->allocated_qty,

    //                                     'dispatched_qty' =>
    //                                         $allocation?->dispatched_qty,

    //                                     'remaining_qty' =>

    //                                         ($allocation?->allocated_qty ?? 0)

    //                                         -

    //                                         ($allocation?->dispatched_qty ?? 0),

    //                                     'allocation_status' =>
    //                                         $allocation?->status,

    //                                     'allocated_by' =>
    //                                         $allocation?->salesExecutive?->name,

    //                                     'warehouse_id' =>
    //                                         $allocation?->warehouse_id,

    //                                     'warehouse_name' =>
    //                                         $allocation?->warehouse?->name,
    //                                 ],

    //                                 /*
    //                                 |--------------------------------------------------------------------------
    //                                 | CURRENT WAREHOUSE STOCK
    //                                 |--------------------------------------------------------------------------
    //                                 */

    //                                 'warehouse_stock' => optional(

    //                                     WarehouseItem::where(
    //                                         'warehouse_id',
    //                                         $warehouse->id
    //                                     )
    //                                     ->where(
    //                                         'item_id',
    //                                         $item->item_id
    //                                     )
    //                                     ->first()

    //                                 )->quantity ?? 0,

    //                                 'images' =>
    //                                     $item->item?->images,
    //                             ];
    //                         }),
    //                 ];
    //             }),

    //             'pagination' => [

    //                 'current_page' => $orders->currentPage(),

    //                 'last_page'    => $orders->lastPage(),

    //                 'per_page'     => $orders->perPage(),

    //                 'total'        => $orders->total(),
    //             ]

    //         ], 200);

    //     } catch (\Exception $e) {

    //         return response()->json([

    //             'status'  => false,

    //             'message' => $e->getMessage(),

    //             'line'    => $e->getLine()

    //         ], 500);
    //     }
    // }
    public function getPendingB2BOrders(Request $request)
    {
        try {

            $authUser = Auth::user();

            /*
            |--------------------------------------------------------------------------
            | AUTH USER WAREHOUSE
            |--------------------------------------------------------------------------
            */

            $warehouse = Warehouse::find($authUser->warehouse_id);
            if (!$warehouse) {

                return response()->json([

                    'status'  => false,

                    'message' => 'Warehouse not assigned to this user'

                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | BASE QUERY
            |--------------------------------------------------------------------------
            */

            $orders = Order::with([

                'user.businessProfile',

                'address',

                'items.item.images',

                'items.allocations' => function ($query) use ($warehouse) {

                    $query->where(
                        'warehouse_id',
                        $warehouse->id
                    );
                },

                'items.allocations.warehouse',

                'items.allocations.salesExecutive',

                'items.allocations.admin'
            ])

                /*
                |--------------------------------------------------------------------------
                | ONLY THOSE ORDERS
                | WHICH HAVE ALLOCATIONS FOR THIS WAREHOUSE
                |--------------------------------------------------------------------------
                */

                ->whereHas('items.allocations', function ($query) use ($warehouse) {

                    $query->where(
                        'warehouse_id',
                        $warehouse->id
                    );
                });

            /*
            |--------------------------------------------------------------------------
            | SEARCH FILTER
            |--------------------------------------------------------------------------
            */

            if ($request->filled('search')) {

                $search = $request->search;

                $orders->where(function ($query) use ($search) {

                    $query->where(
                        'order_number',
                        'like',
                        '%' . $search . '%'
                    )

                        ->orWhereHas('user', function ($q) use ($search) {

                            $q->where(
                                'full_name',
                                'like',
                                '%' . $search . '%'
                            );
                        });
                });
            }

            /*
            |--------------------------------------------------------------------------
            | ORDER STATUS FILTER
            |--------------------------------------------------------------------------
            */

            if ($request->filled('order_status')) {

                $orders->where(
                    'order_status',
                    $request->order_status
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SORTING + PAGINATION
            |--------------------------------------------------------------------------
            */

            $orders = $orders
                ->orderBy('id', 'desc')
                ->paginate($request->per_page ?? 10);

            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'status'  => true,

                'message' =>
                'Warehouse assigned B2B orders fetched successfully',

                'total_orders' => $orders->total(),

                'warehouse' => [

                    'warehouse_id'   => $warehouse->id,

                    'warehouse_name' => $warehouse->name,
                ],

                'data' => collect($orders->items())->map(function ($order) use ($warehouse) {

                    return [

                        'order_id'         => $order->id,

                        'order_number'     => $order->order_number,

                        'payment_status'   => $order->payment_status,

                        'order_status'     => $order->order_status,

                        'subtotal'         => $order->subtotal,

                        'b2b_discount'     => $order->b2b_discount,

                        'shipping_charges' => $order->shipping_charges,

                        'gst_amount'       => $order->gst_amount,

                        'total_amount'     => $order->total_amount,

                        'paid_amount'      => $order->paid_amount,

                        'due_amount'       => round($order->due_amount),

                        'order_date'       => $order->order_date,

                        /*
                        |--------------------------------------------------------------------------
                        | CUSTOMER DETAILS
                        |--------------------------------------------------------------------------
                        */

                        'customer' => [

                            'customer_id'   => $order->user?->id,

                            'customer_name' => $order->user?->full_name,

                            'email'         => $order->user?->email,

                            'phone'         => $order->user?->phone,

                            'account_type'  => $order->user?->account_type,

                            'company_name'  =>
                            $order->user?->businessProfile?->company_name,
                        ],

                        /*
                        |--------------------------------------------------------------------------
                        | SHIPPING ADDRESS
                        |--------------------------------------------------------------------------
                        */

                        'shipping_address' => $order->address ? [

                            'id'             => $order->address->id,

                            'full_name'      => $order->address->full_name,

                            'phone'          => $order->address->phone,

                            'address_line_1' => $order->address->address_line_1,

                            'address_line_2' => $order->address->address_line_2,

                            'city'           => $order->address->city,

                            'state'          => $order->address->state,

                            'postal_code'    => $order->address->postal_code,

                            'country'        => $order->address->country,

                        ] : null,

                        /*
                        |--------------------------------------------------------------------------
                        | ONLY ITEMS ASSIGNED TO THIS WAREHOUSE
                        |--------------------------------------------------------------------------
                        */

                        'items' => $order->items
                            ->filter(function ($item) use ($warehouse) {

                                return $item->allocations
                                    ->where(
                                        'warehouse_id',
                                        $warehouse->id
                                    )
                                    ->count() > 0;
                            })
                            ->values()
                            ->map(function ($item) use ($warehouse) {

                                $allocation = $item->allocations
                                    ->where(
                                        'warehouse_id',
                                        $warehouse->id
                                    )
                                    ->first();

                                return [

                                    'order_item_id' => $item->id,

                                    'item_id'       => $item->item_id,

                                    'item_name'     => $item->item?->name,

                                    'sku'           => $item->item?->sku,

                                    'ordered_qty'   => $item->quantity,

                                    'unit_price'    => $item->unit_price,

                                    'total_price'   => $item->total_price,

                                    'item_status'   => $item->status,

                                    /*
                                    |--------------------------------------------------------------------------
                                    | ALLOCATION DETAILS
                                    |--------------------------------------------------------------------------
                                    */

                                    'allocation' => [

                                        'allocation_id' =>
                                        $allocation?->id,

                                        'allocated_qty' =>
                                        $allocation?->allocated_qty,

                                        'dispatched_qty' =>
                                        $allocation?->dispatched_qty,

                                        'remaining_qty' => ($allocation?->allocated_qty ?? 0)

                                            -

                                            ($allocation?->dispatched_qty ?? 0),

                                        'allocation_status' =>
                                        $allocation?->status,

                                        /*
                                        |--------------------------------------------------------------------------
                                        | SALES EXECUTIVE DETAILS
                                        |--------------------------------------------------------------------------
                                        */

                                        'allocated_by_sales_executive' =>
                                        $allocation?->salesExecutive?->full_name,

                                        'sales_executive' =>
                                        $allocation?->salesExecutive ? [

                                            'id' =>
                                            $allocation->salesExecutive->id,

                                            'name' =>
                                            $allocation->salesExecutive->full_name,

                                            'email' =>
                                            $allocation->salesExecutive->email,

                                            'phone' =>
                                            $allocation->salesExecutive->phone,

                                        ] : null,

                                        /*
                                        |--------------------------------------------------------------------------
                                        | ADMIN DETAILS
                                        |--------------------------------------------------------------------------
                                        */

                                        'allocated_by_admin' =>
                                        $allocation?->admin?->name,

                                        'admin' =>
                                        $allocation?->admin ? [

                                            'id' =>
                                            $allocation->admin->id,

                                            'name' =>
                                            $allocation->admin->name,

                                            'email' =>
                                            $allocation->admin->email,

                                        ] : null,

                                        /*
                                        |--------------------------------------------------------------------------
                                        | WAREHOUSE DETAILS
                                        |--------------------------------------------------------------------------
                                        */

                                        'warehouse_id' =>
                                        $allocation?->warehouse_id,

                                        'warehouse_name' =>
                                        $allocation?->warehouse?->name,
                                    ],

                                    /*
                                    |--------------------------------------------------------------------------
                                    | CURRENT WAREHOUSE STOCK
                                    |--------------------------------------------------------------------------
                                    */

                                    'warehouse_stock' => optional(

                                        WarehouseItem::where(
                                            'warehouse_id',
                                            $warehouse->id
                                        )
                                            ->where(
                                                'item_id',
                                                $item->item_id
                                            )
                                            ->first()

                                    )->quantity ?? 0,

                                    'images' =>
                                    $item->item?->images,
                                ];
                            }),
                    ];
                }),

                'pagination' => [

                    'current_page' => $orders->currentPage(),

                    'last_page'    => $orders->lastPage(),

                    'per_page'     => $orders->perPage(),

                    'total'        => $orders->total(),
                ]

            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'status'  => false,

                'message' => $e->getMessage(),

                'line'    => $e->getLine()

            ], 500);
        }
    }
    
     public function markAsDelivered(Request $request)
    {
        DB::beginTransaction();
    
        try {
    
            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */
    
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
            ]);
    
            /*
            |--------------------------------------------------------------------------
            | FETCH ORDER
            |--------------------------------------------------------------------------
            */
    
            $order = Order::findOrFail($validated['order_id']);
    
            /*
            |--------------------------------------------------------------------------
            | CHECK ORDER STATUS
            |--------------------------------------------------------------------------
            */
    
            if ($order->order_status !== 'dispatched') {
    
                return response()->json([
                    'status'  => false,
                    'message' => 'Only dispatched orders can be marked as delivered'
                ], 422);
            }
    
            /*
            |--------------------------------------------------------------------------
            | UPDATE ORDER STATUS
            |--------------------------------------------------------------------------
            */
    
            $order->update([
                'order_status' => 'delivered',
            ]);
    
            /*
            |--------------------------------------------------------------------------
            | UPDATE ORDER ITEMS STATUS
            |--------------------------------------------------------------------------
            */
    
            OrderItem::where('order_id', $order->id)
                ->update([
                    'status' => 'delivered'
                ]);
    
            /*
            |--------------------------------------------------------------------------
            | UPDATE ORDER ITEM ALLOCATIONS STATUS
            |--------------------------------------------------------------------------
            */
    
            OrderItemAllocation::where('order_id', $order->id)
                ->update([
                    'status' => 'delivered'
                ]);
    
            /*
            |--------------------------------------------------------------------------
            | UPDATE INVOICE STATUS
            |--------------------------------------------------------------------------
            */
    
            Invoice::where('order_id', $order->id)
                ->update([
                    'pi_status' => 'delivered'
                ]);
    
            DB::commit();
    
            return response()->json([
                'status'  => true,
                'message' => 'Order marked as delivered successfully',
                'data'    => [
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                    'order_status' => 'delivered'
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
    
            DB::rollBack();
    
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsShipped(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */

            $validated = $request->validate([

                'order_id'         => 'required|exists:orders,id',

                'transport_mode'   => 'nullable|string',
                'vehicle_number'   => 'nullable|string',
                'approx_distance'  => 'nullable|string',
                'eway_bill_number' => 'nullable|string',

                'tracking_link'    => 'nullable|string',
            ]);

            /*
            |--------------------------------------------------------------------------
            | FETCH ORDER
            |--------------------------------------------------------------------------
            */

            $order = Order::findOrFail($validated['order_id']);

            /*
            |--------------------------------------------------------------------------
            | STORE ORDER DETAILS
            |--------------------------------------------------------------------------
            */

            $orderDetail = OrderDetail::updateOrCreate(

                [
                    'order_id' => $order->id
                ],

                [
                    'transport_mode'   => $request->transport_mode,
                    'vehicle_number'   => $request->vehicle_number,
                    'approx_distance'  => $request->approx_distance,
                    'eway_bill_number' => $request->eway_bill_number,
                    'tracking_link'    => $request->tracking_link,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | UPDATE ORDER STATUS
            |--------------------------------------------------------------------------
            */

            $order->update([
                'order_status' => 'shipped'
            ]);
            Invoice::where('order_id', $order->id)
                ->update([
                    'pi_status' => 'shipped'
                ]);

            DB::commit();

            return response()->json([

                'status'  => true,
                'message' => 'Order marked as shipped successfully',

                'data' => [

                    'order_id'      => $order->id,
                    'order_status'  => $order->order_status,

                    'shipping_details' => $orderDetail
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // public function adjustItemQuantity(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {

    //         /*
    //         |--------------------------------------------------------------------------
    //         | VALIDATION
    //         |--------------------------------------------------------------------------
    //         */

    //         $validated = $request->validate([
    //             'sku'      => 'required|exists:items,sku',
    //             'quantity' => 'required|numeric|min:1',
    //         ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | FETCH ITEM
    //         |--------------------------------------------------------------------------
    //         */

    //         $item = Item::where('sku', $validated['sku'])->firstOrFail();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | OLD QUANTITY
    //         |--------------------------------------------------------------------------
    //         */

    //         $oldQuantity = (int) $item->quantity;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | ADD NEW QUANTITY
    //         |--------------------------------------------------------------------------
    //         */

    //         $newQuantity = $oldQuantity + (int) $validated['quantity'];

    //         /*
    //         |--------------------------------------------------------------------------
    //         | UPDATE QUANTITY
    //         |--------------------------------------------------------------------------
    //         */

    //         $item->update([
    //             'quantity' => $newQuantity
    //         ]);

    //         DB::commit();

    //         return response()->json([

    //             'status'  => true,
    //             'message' => 'Item quantity adjusted successfully',

    //             'data' => [

    //                 'item_id'         => $item->id,
    //                 'item_name'       => $item->name,
    //                 'sku'             => $item->sku,

    //                 'old_quantity'    => $oldQuantity,
    //                 'added_quantity'  => (int) $validated['quantity'],
    //                 'current_quantity' => $newQuantity,
    //             ]
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'errors' => $e->errors()
    //         ], 422);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function sendBackInStockWhatsApp($phone, $item)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');

        $client = new \Twilio\Rest\Client($sid, $token);

        $message = "Good news \n\n" .
            "{$item->name} is back in stock.\n\n" .
            "SKU: {$item->sku}\n\n" .
            "Order now before stock runs out again.";

        $client->messages->create(
            "whatsapp:$phone",
            [
                'from' => env('TWILIO_WHATSAPP_FROM'),
                'body' => $message
            ]
        );
    }

    // public function adjustItemQuantity(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {

    //         /*
    //     |--------------------------------------------------------------------------
    //     | VALIDATION
    //     |--------------------------------------------------------------------------
    //     */

    //         $validated = $request->validate([
    //             'sku'          => 'required|exists:items,sku',
    //             'warehouse_id' => 'required|exists:warehouses,id',
    //             'quantity'     => 'required|numeric|min:1',
    //         ]);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | FETCH ITEM
    //     |--------------------------------------------------------------------------
    //     */

    //         $item = Item::where('sku', $validated['sku'])->firstOrFail();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | FETCH WAREHOUSE
    //     |--------------------------------------------------------------------------
    //     */

    //         $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | OLD ITEM QUANTITY
    //     |--------------------------------------------------------------------------
    //     */

    //         $oldQuantity = (int) $item->quantity;

    //         /*
    //     |--------------------------------------------------------------------------
    //     | NEW ITEM QUANTITY
    //     |--------------------------------------------------------------------------
    //     */

    //         $newQuantity = $oldQuantity + (int) $validated['quantity'];

    //         /*
    //     |--------------------------------------------------------------------------
    //     | UPDATE MAIN ITEMS TABLE
    //     |--------------------------------------------------------------------------
    //     */

    //         $item->update([
    //             'quantity' => $newQuantity
    //         ]);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | UPDATE / INSERT WAREHOUSE ITEM
    //     |--------------------------------------------------------------------------
    //     */

    //         $warehouseItem = WarehouseItem::where('warehouse_id', $warehouse->id)
    //             ->where('item_id', $item->id)
    //             ->first();

    //         if ($warehouseItem) {

    //             /*
    //         |--------------------------------------------------------------------------
    //         | UPDATE EXISTING WAREHOUSE ITEM
    //         |--------------------------------------------------------------------------
    //         */

    //             $warehouseOldQty = (int) $warehouseItem->quantity;

    //             $warehouseItem->update([
    //                 'quantity' => $warehouseOldQty + (int) $validated['quantity']
    //             ]);

    //             $warehouseCurrentQty = $warehouseItem->quantity;
    //         } else {

    //             /*
    //         |--------------------------------------------------------------------------
    //         | CREATE NEW WAREHOUSE ITEM
    //         |--------------------------------------------------------------------------
    //         */

    //             $warehouseItem = WarehouseItem::create([
    //                 'warehouse_id'      => $warehouse->id,
    //                 'item_id'           => $item->id,
    //                 'quantity'          => (int) $validated['quantity'],
    //                 'updated_quantity'  => $request->quantity,
    //                 'reserved_quantity' => 0,
    //             ]);

    //             $warehouseOldQty = 0;
    //             $warehouseCurrentQty = $warehouseItem->quantity;
    //         }

    //         DB::commit();

    //         if ($oldQuantity == 0 && $newQuantity > 0) {

    //             $notifications = BackInStockNotification::with('user')
    //                 ->where('item_id', $item->id)
    //                 ->where('is_notified', false)
    //                 ->get();

    //             foreach ($notifications as $notification) {

    //                 /*
    //     |--------------------------------------------------------------------------
    //     | SEND EMAIL
    //     |--------------------------------------------------------------------------
    //     */

    //                 Mail::to($notification->user->email)
    //                     ->send(new BackInStockMail(
    //                         $notification->user,
    //                         $item
    //                     ));

    //                 /*
    //     |--------------------------------------------------------------------------
    //     | SEND WHATSAPP
    //     |--------------------------------------------------------------------------
    //     */

    //                 // $this->sendBackInStockWhatsApp(
    //                 //     $notification->user->phone,
    //                 //     $item
    //                 // );

    //                 /*
    //     |--------------------------------------------------------------------------
    //     | MARK AS NOTIFIED
    //     |--------------------------------------------------------------------------
    //     */

    //                 $notification->update([
    //                     'is_notified' => true,
    //                     'notified_at' => now()
    //                 ]);
    //             }
    //         }

    //         return response()->json([

    //             'status'  => true,
    //             'message' => 'Item quantity adjusted successfully',

    //             'data' => [

    //                 'item_id'           => $item->id,
    //                 'item_name'         => $item->name,
    //                 'sku'               => $item->sku,

    //                 /*
    //             |--------------------------------------------------------------------------
    //             | MAIN ITEM STOCK
    //             |--------------------------------------------------------------------------
    //             */

    //                 'old_quantity'      => $oldQuantity,
    //                 'added_quantity'    => (int) $validated['quantity'],
    //                 'current_quantity'  => $newQuantity,

    //                 /*
    //             |--------------------------------------------------------------------------
    //             | WAREHOUSE DETAILS
    //             |--------------------------------------------------------------------------
    //             */

    //                 'warehouse' => [

    //                     'warehouse_id'      => $warehouse->id,
    //                     'warehouse_name'    => $warehouse->name,

    //                     'old_quantity'      => $warehouseOldQty,
    //                     'added_quantity'    => (int) $validated['quantity'],
    //                     'current_quantity'  => $warehouseCurrentQty,
    //                 ]
    //             ]
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'errors' => $e->errors()
    //         ], 422);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status'  => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    //     public function adjustItemQuantity(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {

    //         $validated = $request->validate([
    //             'warehouse_id'      => 'required|exists:warehouses,id',
    //             'items'             => 'required|array|min:1',
    //             'items.*.item_id'   => 'required|exists:items,id',
    //             'items.*.quantity'  => 'required|numeric|min:1',
    //         ]);

    //         $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

    //         $responseItems = [];

    //         foreach ($validated['items'] as $row) {

    //             $item = Item::findOrFail($row['item_id']);

    //             $oldQuantity = (int) $item->quantity;
    //             $addQuantity = (int) $row['quantity'];
    //             $newQuantity = $oldQuantity + $addQuantity;

    //             /*
    //             |--------------------------------------------------------------------------
    //             | UPDATE ITEM STOCK
    //             |--------------------------------------------------------------------------
    //             */

    //             $item->update([
    //                 'quantity' => $newQuantity
    //             ]);

    //             /*
    //             |--------------------------------------------------------------------------
    //             | UPDATE WAREHOUSE STOCK
    //             |--------------------------------------------------------------------------
    //             */

    //             $warehouseItem = WarehouseItem::where('warehouse_id', $warehouse->id)
    //                 ->where('item_id', $item->id)
    //                 ->first();

    //             if ($warehouseItem) {

    //                 $warehouseOldQty = (int) $warehouseItem->quantity;

    //                 $warehouseItem->increment('quantity', $addQuantity);

    //                 $warehouseCurrentQty = $warehouseItem->fresh()->quantity;
    //             } else {

    //                 $warehouseOldQty = 0;

    //                 $warehouseItem = WarehouseItem::create([
    //                     'warehouse_id'      => $warehouse->id,
    //                     'item_id'           => $item->id,
    //                     'quantity'          => $addQuantity,
    //                     'updated_quantity'  => $addQuantity,
    //                     'reserved_quantity' => 0,
    //                 ]);

    //                 $warehouseCurrentQty = $warehouseItem->quantity;
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | BACK IN STOCK NOTIFICATION
    //             |--------------------------------------------------------------------------
    //             */

    //             if ($oldQuantity == 0 && $newQuantity > 0) {

    //                 $notifications = BackInStockNotification::with('user')
    //                     ->where('item_id', $item->id)
    //                     ->where('is_notified', false)
    //                     ->get();

    //                 foreach ($notifications as $notification) {

    //                     Mail::to($notification->user->email)
    //                         ->send(new BackInStockMail(
    //                             $notification->user,
    //                             $item
    //                         ));

    //                     $notification->update([
    //                         'is_notified' => true,
    //                         'notified_at' => now()
    //                     ]);
    //                 }
    //             }

    //             $responseItems[] = [
    //                 'item_id' => $item->id,
    //                 'item_name' => $item->name,
    //                 'old_quantity' => $oldQuantity,
    //                 'added_quantity' => $addQuantity,
    //                 'current_quantity' => $newQuantity,

    //                 'warehouse' => [
    //                     'warehouse_id' => $warehouse->id,
    //                     'warehouse_name' => $warehouse->name,
    //                     'old_quantity' => $warehouseOldQty,
    //                     'added_quantity' => $addQuantity,
    //                     'current_quantity' => $warehouseCurrentQty,
    //                 ]
    //             ];
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Item quantities adjusted successfully',
    //             'data' => $responseItems
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'errors' => $e->errors()
    //         ], 422);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function adjustItemQuantity(Request $request)
    {
        DB::beginTransaction();

        try {

            $validated = $request->validate([
                'warehouse_id'      => 'required|exists:warehouses,id',
                'items'             => 'required|array|min:1',
                'items.*.item_id'   => 'required|exists:items,id',
                'items.*.quantity'  => 'required|numeric|min:1',
            ]);

            $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

            $responseItems = [];

            foreach ($validated['items'] as $row) {

                $item = Item::findOrFail($row['item_id']);

                $addQuantity = (int) $row['quantity'];

                /*
                |--------------------------------------------------------------------------
                | ITEM STOCK
                |--------------------------------------------------------------------------
                */

                $oldItemQty = (int) $item->quantity;
                $newItemQty = $oldItemQty + $addQuantity;

                $item->update([
                    'quantity' => $newItemQty
                ]);

                /*
                |--------------------------------------------------------------------------
                | WAREHOUSE STOCK
                |--------------------------------------------------------------------------
                */

                $warehouseItem = WarehouseItem::where('warehouse_id', $warehouse->id)
                    ->where('item_id', $item->id)
                    ->first();

                if ($warehouseItem) {

                    $warehouseOldQty = (int) $warehouseItem->quantity;

                    $warehouseCurrentQty = $warehouseOldQty + $addQuantity;

                    $warehouseItem->update([
                        'quantity'         => $warehouseCurrentQty,
                        'updated_quantity' => $addQuantity, // stock movement
                        'updated_at' => now(), // stock movement
                    ]);
                } else {

                    $warehouseOldQty = 0;

                    $warehouseItem = WarehouseItem::create([
                        'warehouse_id'      => $warehouse->id,
                        'item_id'           => $item->id,
                        'quantity'          => $addQuantity,
                        'updated_quantity'  => $addQuantity,
                        'reserved_quantity' => 0,
                    ]);

                    $warehouseCurrentQty = $warehouseItem->quantity;
                }

                /*
                |--------------------------------------------------------------------------
                | BACK IN STOCK NOTIFICATION
                |--------------------------------------------------------------------------
                */

                if ($oldItemQty == 0 && $newItemQty > 0) {

                    $notifications = BackInStockNotification::with('user')
                        ->where('item_id', $item->id)
                        ->where('is_notified', false)
                        ->get();

                    foreach ($notifications as $notification) {

                        if ($notification->user && $notification->user->email) {

                            Mail::to($notification->user->email)
                                ->send(new BackInStockMail(
                                    $notification->user,
                                    $item
                                ));
                        }

                        $notification->update([
                            'is_notified' => true,
                            'notified_at' => now(),
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | RESPONSE DATA
                |--------------------------------------------------------------------------
                */

                $responseItems[] = [

                    'item' => [
                        'item_id'          => $item->id,
                        'item_name'        => $item->name,
                        'old_quantity'     => $oldItemQty,
                        'added_quantity'   => $addQuantity,
                        'current_quantity' => $newItemQty,
                    ],

                    'warehouse' => [
                        'warehouse_id'      => $warehouse->id,
                        'warehouse_name'    => $warehouse->name,
                        'old_quantity'      => $warehouseOldQty,
                        'added_quantity'    => $addQuantity,
                        'current_quantity'  => $warehouseCurrentQty,
                        'updated_quantity'  => $addQuantity,
                    ]
                ];
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Item quantities adjusted successfully',
                'warehouse' => [
                    'warehouse_id' => $warehouse->id,
                    'warehouse_name' => $warehouse->name,
                ],
                'total_items_processed' => count($responseItems),
                'data' => $responseItems
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function reportDamagedStock(Request $request)
    {
        DB::beginTransaction();

        try {

            $validated = $request->validate([
                'warehouse_id'      => 'required|exists:warehouses,id',
                'reason'            => 'nullable|string|max:255',
                'notes'             => 'nullable|string',

                'items'             => 'required|array|min:1',
                'items.*.item_id'   => 'required|exists:items,id',
                'items.*.quantity'  => 'required|numeric|min:1',
            ]);

            $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

            $responseItems = [];

            foreach ($validated['items'] as $row) {

                $item = Item::findOrFail($row['item_id']);

                $warehouseItem = WarehouseItem::where('warehouse_id', $warehouse->id)
                    ->where('item_id', $item->id)
                    ->first();

                if (!$warehouseItem) {

                    throw new \Exception(
                        "Item '{$item->name}' not found in warehouse."
                    );
                }

                $damagedQty = (int) $row['quantity'];

                if ($warehouseItem->quantity < $damagedQty) {

                    throw new \Exception(
                        "Insufficient stock for '{$item->name}'. Available: {$warehouseItem->quantity}"
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | OLD QUANTITIES
                |--------------------------------------------------------------------------
                */

                $oldItemQty = (int) $item->quantity;
                $oldWarehouseQty = (int) $warehouseItem->quantity;

                /*
                |--------------------------------------------------------------------------
                | NEW QUANTITIES
                |--------------------------------------------------------------------------
                */

                $newItemQty = $oldItemQty - $damagedQty;
                $newWarehouseQty = $oldWarehouseQty - $damagedQty;

                /*
                |--------------------------------------------------------------------------
                | UPDATE MAIN ITEM STOCK
                |--------------------------------------------------------------------------
                */

                $item->update([
                    'quantity' => $newItemQty
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE WAREHOUSE STOCK
                |--------------------------------------------------------------------------
                */

                $warehouseItem->update([
                    'quantity' => $newWarehouseQty,

                    // stock movement
                    'updated_quantity' => -$damagedQty,
                    'updated_at' => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | STORE DAMAGED RECORD
                |--------------------------------------------------------------------------
                */

                $damaged = DamagedItem::create([
                    'warehouse_id' => $warehouse->id,
                    'item_id'      => $item->id,
                    'quantity'     => $damagedQty,
                    'reason'       => $validated['reason'] ?? null,
                    'notes'        => $validated['notes'] ?? null,
                    'reported_by'  => auth()->id(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | RESPONSE DATA
                |--------------------------------------------------------------------------
                */

                $responseItems[] = [

                    'record_id' => $damaged->id,

                    'item' => [
                        'item_id' => $item->id,
                        'item_name' => $item->name,

                        'old_quantity' => $oldItemQty,
                        'damaged_quantity' => $damagedQty,
                        'current_quantity' => $newItemQty,
                    ],

                    'warehouse' => [
                        'warehouse_id' => $warehouse->id,
                        'warehouse_name' => $warehouse->name,

                        'old_quantity' => $oldWarehouseQty,
                        'minus_quantity' => $damagedQty,
                        'current_quantity' => $newWarehouseQty,

                        // movement stored in warehouse_items.updated_quantity
                        'updated_quantity' => -$damagedQty,
                    ],

                    'reason' => $validated['reason'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ];
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Damaged stock reported successfully',
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'total_items_processed' => count($responseItems),
                'data' => $responseItems
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // public function reportDamagedStock(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {

    //         $validated = $request->validate([
    //             'warehouse_id'      => 'required|exists:warehouses,id',
    //             'reason'            => 'nullable|string|max:255',
    //             'notes'             => 'nullable|string',

    //             'items'             => 'required|array|min:1',
    //             'items.*.item_id'   => 'required|exists:items,id',
    //             'items.*.quantity'  => 'required|numeric|min:1',
    //         ]);

    //         $responseItems = [];

    //         foreach ($validated['items'] as $row) {

    //             $item = Item::findOrFail($row['item_id']);

    //             $warehouseItem = WarehouseItem::where('warehouse_id', $validated['warehouse_id'])
    //                 ->where('item_id', $item->id)
    //                 ->first();

    //             if (!$warehouseItem) {
    //                 throw new \Exception("{$item->name} not found in warehouse.");
    //             }

    //             if ($warehouseItem->quantity < $row['quantity']) {
    //                 throw new \Exception("Insufficient stock for {$item->name}.");
    //             }

    //             /*
    //             |--------------------------------------------------------------------------
    //             | DEDUCT STOCK
    //             |--------------------------------------------------------------------------
    //             */

    //             $item->decrement('quantity', $row['quantity']);

    //             $warehouseItem->decrement('quantity', $row['quantity']);

    //             /*
    //             |--------------------------------------------------------------------------
    //             | STORE DAMAGED RECORD
    //             |--------------------------------------------------------------------------
    //             */

    //             $damaged = DamagedItem::create([
    //                 'warehouse_id' => $validated['warehouse_id'],
    //                 'item_id'      => $item->id,
    //                 'quantity'     => $row['quantity'],
    //                 'reason'       => $validated['reason'],
    //                 'notes'        => $validated['notes'] ?? null,
    //                 'reported_by'  => auth()->id(),
    //             ]);

    //             $responseItems[] = [
    //                 'item_id' => $item->id,
    //                 'item_name' => $item->name,
    //                 'damaged_quantity' => $row['quantity'],
    //                 'record_id' => $damaged->id,
    //             ];
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Damaged stock reported successfully',
    //             'data' => $responseItems
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'errors' => $e->errors()
    //         ], 422);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    // public function reportDamagedStock(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {

    //         $validated = $request->validate([
    //             'sku'          => 'required|exists:items,sku',
    //             'warehouse_id' => 'required|exists:warehouses,id',
    //             'quantity'     => 'required|numeric|min:1',
    //             'reason'       => 'nullable|string|max:255',
    //             'notes'        => 'nullable|string',
    //         ]);

    //         $item = Item::where('sku', $validated['sku'])->firstOrFail();

    //         $warehouseItem = WarehouseItem::where('warehouse_id', $validated['warehouse_id'])
    //             ->where('item_id', $item->id)
    //             ->first();

    //         if (!$warehouseItem) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Item not found in warehouse'
    //             ], 404);
    //         }

    //         if ($warehouseItem->quantity < $validated['quantity']) {

    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Insufficient warehouse stock'
    //             ], 400);
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | DEDUCT STOCK
    //     |--------------------------------------------------------------------------
    //     */

    //         $item->decrement('quantity', $validated['quantity']);

    //         $warehouseItem->decrement('quantity', $validated['quantity']);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | STORE DAMAGED RECORD
    //     |--------------------------------------------------------------------------
    //     */

    //         $damaged = DamagedItem::create([
    //             'warehouse_id' => $validated['warehouse_id'],
    //             'item_id'      => $item->id,
    //             'quantity'     => $validated['quantity'],
    //             'reason'       => $validated['reason'],
    //             'notes'        => $validated['notes'] ?? null,
    //             'reported_by'  => auth()->id(),
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Damaged stock reported successfully',
    //             'data' => $damaged
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'errors' => $e->errors()
    //         ], 422);
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    // private function notifyWarehouseManagers($warehouseId, $title, $message)
    // {
    //     $managers = User::where('account_type', 'warehouse_manager')
    //         ->where('warehouse_id', $warehouseId)
    //         ->whereNotNull('phone')
    //         ->get();

    //     foreach ($managers as $manager) {

    //         /*
    //     |--------------------------------------------------------------------------
    //     | SEND EMAIL
    //     |--------------------------------------------------------------------------
    //     */

    //         if ($manager->email) {

    //             Mail::raw($message, function ($mail) use ($manager, $title) {

    //                 $mail->to($manager->email)
    //                     ->subject($title);
    //             });
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | SEND WHATSAPP
    //     |--------------------------------------------------------------------------
    //     */

    //         try {

    //             $client = new \Twilio\Rest\Client(
    //                 env('TWILIO_SID'),
    //                 env('TWILIO_AUTH_TOKEN')
    //             );

    //             $phone = $manager->phone;

    //             /*
    //         |--------------------------------------------------------------------------
    //         | FORMAT PHONE
    //         |--------------------------------------------------------------------------
    //         */

    //             if (!str_starts_with($phone, '+')) {
    //                 $phone = '+91' . ltrim($phone, '0');
    //             }

    //             $client->messages->create(
    //                 "whatsapp:" . $phone,
    //                 [
    //                     "from" => env('TWILIO_WHATSAPP_NUMBER'),
    //                     "body" => $message
    //                 ]
    //             );
    //         } catch (\Exception $e) {

    //             Log::error('WhatsApp Error: ' . $e->getMessage());
    //         }
    //     }
    // }



    public function createTransfer(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

            $validated = $request->validate([

                'from_warehouse' => 'required|exists:warehouses,id',
                'to_warehouse'   => 'required|exists:warehouses,id',

                'items' => 'required|array|min:1',

                'items.*.item_id'  => 'required|exists:items,id',
                'items.*.quantity' => 'required|numeric|min:1',
            ]);

            /*
        |--------------------------------------------------------------------------
        | CREATE TRANSFER
        |--------------------------------------------------------------------------
        */

            $transfer = WarehouseTransfer::create([

                'transfer_ref' => 'LZ-TRF-' . rand(1000, 9999),

                'from_warehouse' => $validated['from_warehouse'],
                'to_warehouse'   => $validated['to_warehouse'],

                'status' => 'draft',
            ]);

            $totalItems = 0;
            $totalUnits = 0;
            $totalValue = 0;

            /*
        |--------------------------------------------------------------------------
        | STORE ITEMS
        |--------------------------------------------------------------------------
        */

            foreach ($validated['items'] as $transferItem) {

                $item = Item::findOrFail($transferItem['item_id']);

                /*
            |--------------------------------------------------------------------------
            | CHECK SOURCE WAREHOUSE STOCK
            |--------------------------------------------------------------------------
            */

                $warehouseItem = WarehouseItem::where(
                    'warehouse_id',
                    $validated['from_warehouse']
                )
                    ->where(
                        'item_id',
                        $item->id
                    )
                    ->first();

                if (!$warehouseItem) {

                    return response()->json([
                        'status' => false,
                        'message' => "{$item->name} not found in source warehouse"
                    ], 422);
                }

                if ($warehouseItem->quantity < $transferItem['quantity']) {

                    return response()->json([
                        'status' => false,
                        'message' => "Insufficient stock for {$item->name}",
                        'available_quantity' => $warehouseItem->quantity
                    ], 422);
                }

                $totalPrice = $item->price * $transferItem['quantity'];

                WarehouseTransferItem::create([

                    'warehouse_transfer_id' => $transfer->id,
                    'item_id'               => $item->id,

                    'quantity'              => $transferItem['quantity'],
                    'unit_price'            => $item->price,
                    'total_price'           => $totalPrice,
                ]);

                $totalItems++;
                $totalUnits += $transferItem['quantity'];
                $totalValue += $totalPrice;
            }

            /*
        |--------------------------------------------------------------------------
        | UPDATE TOTALS
        |--------------------------------------------------------------------------
        */

            $transfer->update([
                'total_items' => $totalItems,
                'total_units' => $totalUnits,
                'total_value' => $totalValue,
            ]);

            DB::commit();

            /*
        |--------------------------------------------------------------------------
        | NOTIFY DESTINATION MANAGER
        |--------------------------------------------------------------------------
        */

            $this->notifyWarehouseManagers(

                $transfer->to_warehouse,

                'New Warehouse Transfer',

                "A new transfer {$transfer->transfer_ref} has been created for your warehouse."
            );

            return response()->json([
                'status' => true,
                'message' => 'Transfer created successfully',
                'data' => $transfer
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // public function getTransfers(Request $request)
    // {
    //     try {

    //         $query = WarehouseTransfer::with([
    //             'items.item.images'
    //         ])->latest();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | STATUS FILTER
    //     |--------------------------------------------------------------------------
    //     */

    //         if ($request->filled('status')) {

    //             $query->where(
    //                 'status',
    //                 $request->status
    //             );
    //         }

    //         /*
    //     |--------------------------------------------------------------------------
    //     | SEARCH
    //     |--------------------------------------------------------------------------
    //     */

    //         if ($request->filled('search')) {

    //             $query->where(
    //                 'transfer_ref',
    //                 'like',
    //                 '%' . $request->search . '%'
    //             );
    //         }

    //         $transfers = $query->paginate(10);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Transfers fetched successfully',
    //             'data' => $transfers
    //         ]);
    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getTransfers(Request $request)
    {
        try {

            $query = WarehouseTransfer::with([
                'items.item.images',
                'fromWarehouse:id,name',
                'toWarehouse:id,name'
            ])->latest();

            /*
            |--------------------------------------------------------------------------
            | STATUS FILTER
            |--------------------------------------------------------------------------
            */

            if ($request->filled('status')) {

                $query->where(
                    'status',
                    $request->status
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */

            if ($request->filled('search')) {

                $query->where(
                    'transfer_ref',
                    'like',
                    '%' . $request->search . '%'
                );
            }

            $transfers = $query->paginate(10);

            /*
            |--------------------------------------------------------------------------
            | MODIFY RESPONSE
            |--------------------------------------------------------------------------
            */

            $transfers->getCollection()->transform(function ($transfer) {

                $transfer->from_warehouse = [
                    'id'   => $transfer->fromWarehouse?->id,
                    'name' => $transfer->fromWarehouse?->name,
                ];

                $transfer->to_warehouse = [
                    'id'   => $transfer->toWarehouse?->id,
                    'name' => $transfer->toWarehouse?->name,
                ];

                unset($transfer->fromWarehouse);
                unset($transfer->toWarehouse);

                return $transfer;
            });

            return response()->json([
                'status' => true,
                'message' => 'Transfers fetched successfully',
                'data' => $transfers
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function dispatchTransfer(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | FETCH TRANSFER
        |--------------------------------------------------------------------------
        */

            $transfer = WarehouseTransfer::with([
                'items.item'
            ])->findOrFail($id);



            /*
        |--------------------------------------------------------------------------
        | CHECK & DEDUCT STOCK
        |--------------------------------------------------------------------------
        */

            foreach ($transfer->items as $transferItem) {

                $warehouseItem = WarehouseItem::where(
                    'warehouse_id',
                    $transfer->from_warehouse
                )
                    ->where(
                        'item_id',
                        $transferItem->item_id
                    )
                    ->first();

                if (!$warehouseItem) {

                    return response()->json([
                        'status' => false,
                        'message' => "{$transferItem->item->name} not found in source warehouse"
                    ], 422);
                }

                if ($warehouseItem->quantity < $transferItem->quantity) {

                    return response()->json([
                        'status' => false,
                        'message' => "Insufficient stock for {$transferItem->item->name}",
                        'available_quantity' => $warehouseItem->quantity
                    ], 422);
                }

                /*
            |--------------------------------------------------------------------------
            | DEDUCT FROM SOURCE WAREHOUSE
            |--------------------------------------------------------------------------
            */

                $warehouseItem->decrement(
                    'quantity',
                    $transferItem->quantity
                );

                /*
            |--------------------------------------------------------------------------
            | DEDUCT GLOBAL STOCK
            |--------------------------------------------------------------------------
            */

                Item::where(
                    'id',
                    $transferItem->item_id
                )->decrement(
                    'quantity',
                    $transferItem->quantity
                );
            }

            /*
        |--------------------------------------------------------------------------
        | UPDATE TRANSFER STATUS
        |--------------------------------------------------------------------------
        */

            $transfer->update([

                'status' => 'in_transit',
                'eway_status' => 'generated',
                'eway_bill_number' => $request->eway_bill_number,
                'dispatched_at' => now()
            ]);

            DB::commit();

            /*
        |--------------------------------------------------------------------------
        | NOTIFY DESTINATION MANAGER
        |--------------------------------------------------------------------------
        */

            $this->notifyWarehouseManagers(

                $transfer->to_warehouse,

                'Transfer Dispatched',

                "Transfer {$transfer->transfer_ref} has been dispatched and is in transit."
            );

            return response()->json([
                'status' => true,
                'message' => 'Transfer dispatched successfully',
                'data' => $transfer
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // public function approveTransfer($id)
    // {
    //     try {

    //         $transfer = WarehouseTransfer::findOrFail($id);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | UPDATE STATUS
    //     |--------------------------------------------------------------------------
    //     */

    //         $transfer->update([

    //             'status' => 'approved',
    //             'eway_status' => 'ready_to_generate'
    //         ]);

    //         /*
    //     |--------------------------------------------------------------------------
    //     | NOTIFY DESTINATION MANAGER
    //     |--------------------------------------------------------------------------
    //     */

    //         $this->notifyWarehouseManagers(

    //             $transfer->to_warehouse,

    //             'Transfer Approved',

    //             "Transfer {$transfer->transfer_ref} has been approved."
    //         );

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Transfer approved successfully',
    //             'data' => $transfer
    //         ]);
    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function approveTransfer($id)
    {
        try {

            $transfer = WarehouseTransfer::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | UPDATE STATUS
            |--------------------------------------------------------------------------
            */

            $transfer->update([
                'status'       => 'approved',
                'eway_status'  => 'ready_to_generate',
                'received_at'  => now(),
                
            ]);

            /*
            |--------------------------------------------------------------------------
            | NOTIFY DESTINATION WAREHOUSE MANAGERS
            |--------------------------------------------------------------------------
            */

            $this->notifyWarehouseManagers(
                $transfer->to_warehouse,
                'Transfer Approved',
                "Transfer {$transfer->transfer_ref} has been approved.",
                'warehouse_transfer',
                $transfer->id
            );

            return response()->json([
                'status'  => true,
                'message' => 'Transfer approved successfully',
                'data'    => $transfer->fresh()
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function receiveTransfer(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | FETCH TRANSFER
        |--------------------------------------------------------------------------
        */

            $transfer = WarehouseTransfer::with([
                'items.item'
            ])->findOrFail($id);

            /*
        |--------------------------------------------------------------------------
        | VALIDATE STATUS
        |--------------------------------------------------------------------------
        */

            if ($transfer->status != 'in_transit') {

                return response()->json([
                    'status' => false,
                    'message' => 'Only in transit transfers can be received'
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | ADD STOCK TO DESTINATION WAREHOUSE
        |--------------------------------------------------------------------------
        */

            foreach ($transfer->items as $transferItem) {

                $warehouseItem = WarehouseItem::where(
                    'warehouse_id',
                    $transfer->to_warehouse
                )
                    ->where(
                        'item_id',
                        $transferItem->item_id
                    )
                    ->first();

                if ($warehouseItem) {

                    /*
                |--------------------------------------------------------------------------
                | UPDATE EXISTING STOCK
                |--------------------------------------------------------------------------
                */

                    $warehouseItem->increment(
                        'quantity',
                        $transferItem->quantity
                    );
                } else {

                    /*
                |--------------------------------------------------------------------------
                | CREATE NEW STOCK ENTRY
                |--------------------------------------------------------------------------
                */

                    WarehouseItem::create([

                        'warehouse_id'      => $transfer->to_warehouse,
                        'item_id'           => $transferItem->item_id,
                        'quantity'          => $transferItem->quantity,
                        'reserved_quantity' => 0,
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | ADD GLOBAL STOCK BACK
            |--------------------------------------------------------------------------
            */

                $transferItem->item->increment(
                    'quantity',
                    $transferItem->quantity
                );
            }

            /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS
        |--------------------------------------------------------------------------
        */

            $transfer->update([

                'status' => 'received',
                'receiving_note' => $request->receiving_note,
                'condition' => $request->condition,
                'received_at' => now()
            ]);

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | NOTIFY SOURCE WAREHOUSE MANAGERS
            |--------------------------------------------------------------------------
            */

            $this->notifyWarehouseManagers(
                $transfer->from_warehouse,
                'Transfer Received',
                "Transfer {$transfer->transfer_ref} has been received successfully."
            );

            /*
            |--------------------------------------------------------------------------
            | NOTIFY DESTINATION WAREHOUSE MANAGERS
            |--------------------------------------------------------------------------
            */

            $this->notifyWarehouseManagers(
                $transfer->to_warehouse,
                'Transfer Received',
                "Transfer {$transfer->transfer_ref} has been received successfully."
            );

            /*
            |--------------------------------------------------------------------------
            | NOTIFY DESTINATION WAREHOUSE MANAGERS - STOCK ADDED
            |--------------------------------------------------------------------------
            */

            $this->notifyWarehouseManagers(
                $transfer->to_warehouse,
                'Stock Added',
                "Stock for transfer {$transfer->transfer_ref} has been added successfully."
            );

            return response()->json([
                'status' => true,
                'message' => 'Transfer received successfully',
                'data' => $transfer
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    private function notifyWarehouseManagers($warehouseId, $title, $message)
    {
        $managers = User::where('account_type', 'warehouse_manager')
            ->where('warehouse_id', $warehouseId)
            ->whereNotNull('phone')
            ->get();

        foreach ($managers as $manager) {

            /*
            |--------------------------------------------------------------------------
            | SAVE NOTIFICATION
            |--------------------------------------------------------------------------
            */

            Notification::create([
                'user_id'        => $manager->id,
                'type'           => 'warehouse',
                'title'          => $title,
                'message'        => $message,
                'reference_type' => 'warehouse',
                'reference_id'   => $warehouseId,
                'priority'       => 'high',
                'is_read'        => 0,
                'extra_data'     => json_encode([
                    'warehouse_id' => $warehouseId,
                ]),
            ]);

            /*
            |--------------------------------------------------------------------------
            | SEND EMAIL
            |--------------------------------------------------------------------------
            */

            if ($manager->email) {

                Mail::raw($message, function ($mail) use ($manager, $title) {

                    $mail->to($manager->email)
                        ->subject($title);
                });
            }

            /*
            |--------------------------------------------------------------------------
            | SEND WHATSAPP
            |--------------------------------------------------------------------------
            */

            try {

                $client = new \Twilio\Rest\Client(
                    env('TWILIO_SID'),
                    env('TWILIO_AUTH_TOKEN')
                );

                $phone = $manager->phone;

                if (!str_starts_with($phone, '+')) {
                    $phone = '+91' . ltrim($phone, '0');
                }

                $client->messages->create(
                    "whatsapp:" . $phone,
                    [
                        "from" => env('TWILIO_WHATSAPP_NUMBER'),
                        "body" => $message
                    ]
                );
            } catch (\Exception $e) {

                Log::error('WhatsApp Error: ' . $e->getMessage());
            }
        }
    }

    // public function warehouseDashboard()
    // {
    //     try {

    //         /*
    //     |--------------------------------------------------------------------------
    //     | ORDERS TO PROCESS
    //     |--------------------------------------------------------------------------
    //     */

    //         $ordersToProcess = Order::where(
    //             'order_status',
    //             'confirmed'
    //         )->count();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | READY TO DISPATCH
    //     |--------------------------------------------------------------------------
    //     */

    //         $readyToDispatch = Order::where(
    //             'order_status',
    //             'confirmed'
    //         )->count();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | LOW STOCK ITEMS
    //     |--------------------------------------------------------------------------
    //     */

    //         $lowStockItems = Item::where(
    //             'quantity',
    //             '<=',
    //             20
    //         )->count();

    //         $pendingTransfer = WarehouseTransfer::where(
    //             'status',
    //             'draft'
    //         )->count();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | ORDER QUEUE
    //     |--------------------------------------------------------------------------
    //     */

    //         $orderQueue = Order::with([
    //             'items',
    //             'user'
    //         ])
    //             ->where('order_status', [
    //                 'confirmed',
    //             ])
    //             // ->whereIn('order_status', [
    //             //     'processing',
    //             //     'picking',
    //             //     'ready'
    //             // ])
    //             ->latest()
    //             ->take(10)
    //             ->get()
    //             ->map(function ($order) {

    //                 return [

    //                     'order_id' => $order->id,

    //                     'order_number' => $order->order_number,

    //                     'customer' => $order->user?->full_name,

    //                     'items_count' => $order->items->count(),

    //                     'status' => $order->order_status,

    //                     'action' => match ($order->order_status) {

    //                         'confirmed' => 'ready',

    //                         // 'picking' => 'pack',

    //                         // 'ready' => 'dispatch',

    //                         default => null
    //                     }
    //                 ];
    //             });

    //         /*
    //     |--------------------------------------------------------------------------
    //     | LOW STOCK ALERTS
    //     |--------------------------------------------------------------------------
    //     */

    //         $lowStockAlerts = Item::where(
    //             'quantity',
    //             '<=',
    //             20
    //         )
    //             ->select(
    //                 'id',
    //                 'name',
    //                 'sku',
    //                 'quantity',
    //                 'updated_at'
    //             )
    //             ->latest()
    //             ->take(10)
    //             ->get();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | RECENT ACTIVITIES
    //     |--------------------------------------------------------------------------
    //     */

    //         $recentActivities = collect();

    //         /*
    //     |--------------------------------------------------------------------------
    //     | DISPATCHED ORDERS
    //     |--------------------------------------------------------------------------
    //     */

    //         $dispatchedOrders = Order::where(
    //             'order_status',
    //             'shipped'
    //         )
    //             ->latest()
    //             ->take(5)
    //             ->get()
    //             ->map(function ($order) {

    //                 return [

    //                     'type' => 'dispatch',

    //                     'title' => $order->order_number . ' dispatched via Porter',

    //                     'description' =>
    //                     'E-Way: ' .
    //                         ($order->eway_bill_number ?? 'Pending'),

    //                     'created_at' => $order->updated_at,
    //                 ];
    //             });

    //         /*
    //     |--------------------------------------------------------------------------
    //     | RECEIVED TRANSFERS
    //     |--------------------------------------------------------------------------
    //     */

    //         $receivedTransfers = WarehouseTransfer::where(
    //             'status',
    //             'received'
    //         )
    //             ->latest()
    //             ->take(5)
    //             ->get()
    //             ->map(function ($transfer) {

    //                 return [

    //                     'type' => 'transfer',

    //                     'title' =>
    //                     'Transfer ' .
    //                         $transfer->transfer_ref .
    //                         ' received',

    //                     'description' =>
    //                     $transfer->total_units .
    //                         ' units from ' .
    //                         $transfer->from_warehouse,

    //                     'created_at' => $transfer->updated_at,
    //                 ];
    //             });

    //         /*
    //     |--------------------------------------------------------------------------
    //     | STOCK ACTIVITIES
    //     |--------------------------------------------------------------------------
    //     */

    //         $stockActivities = Item::latest('updated_at')
    //             ->take(5)
    //             ->get()
    //             ->map(function ($item) {

    //                 return [

    //                     'type' => 'stock',

    //                     'title' =>
    //                     'Stock updated — ' .
    //                         $item->sku,

    //                     'description' =>
    //                     'Current quantity: ' .
    //                         $item->quantity,

    //                     'created_at' => $item->updated_at,
    //                 ];
    //             });

    //         /*
    //     |--------------------------------------------------------------------------
    //     | MERGE ACTIVITIES
    //     |--------------------------------------------------------------------------
    //     */

    //         $recentActivities = $recentActivities
    //             ->merge($dispatchedOrders)
    //             ->merge($receivedTransfers)
    //             ->merge($stockActivities)
    //             ->sortByDesc('created_at')
    //             ->values()
    //             ->take(10);

    //         return response()->json([

    //             'status' => true,

    //             'message' => 'Warehouse dashboard fetched successfully',

    //             'data' => [

    //                 'cards' => [

    //                     'orders_to_process' => [
    //                         'count' => $ordersToProcess,
    //                         'label' => 'Awaiting pick/pack'
    //                     ],

    //                     'ready_to_dispatch' => [
    //                         'count' => $readyToDispatch,
    //                         'label' => 'E-Way Bill pending'
    //                     ],

    //                     'low_stock_skus' => [
    //                         'count' => $lowStockItems,
    //                         'label' => 'Below threshold'
    //                     ],
    //                     'pending_transfer' => [
    //                         'count' => $pendingTransfer,
    //                         'label' => 'Pending Transfer'
    //                     ]
    //                 ],

    //                 'order_queue' => $orderQueue,

    //                 'low_stock_alerts' => $lowStockAlerts,

    //                 'recent_activities' => $recentActivities
    //             ]
    //         ]);
    //     } catch (\Exception $e) {

    //         return response()->json([

    //             'status' => false,

    //             'message' => $e->getMessage()

    //         ], 500);
    //     }
    // }

    public function warehouseDashboard()
    {
        try {

            $warehouseId = Auth::user()->warehouse_id;

            /*
        |--------------------------------------------------------------------------
        | WAREHOUSE ORDER IDS
        |--------------------------------------------------------------------------
        */

            $orderIds = OrderItemAllocation::where('warehouse_id', $warehouseId)
                ->pluck('order_id')
                ->unique();

            /*
        |--------------------------------------------------------------------------
        | ORDERS TO PROCESS
        |--------------------------------------------------------------------------
        */

            $ordersToProcess = Order::whereIn('id', $orderIds)
                ->where('order_status', 'confirmed')
                ->count();

            /*
        |--------------------------------------------------------------------------
        | READY TO DISPATCH
        |--------------------------------------------------------------------------
        */

            $readyToDispatch = Order::whereIn('id', $orderIds)
                ->where('order_status', 'confirmed')
                ->count();

            /*
        |--------------------------------------------------------------------------
        | LOW STOCK ITEMS
        |--------------------------------------------------------------------------
        */

            $lowStockItems = Item::where(
                'quantity',
                '<=',
                20
            )->count();

            /*
        |--------------------------------------------------------------------------
        | PENDING TRANSFER
        |--------------------------------------------------------------------------
        */

            $pendingTransfer = WarehouseTransfer::where(
                'status',
                'draft'
            )->count();

            /*
        |--------------------------------------------------------------------------
        | ORDER QUEUE
        |--------------------------------------------------------------------------
        */

        $orderQueue = Order::with([
            'items',
            'user'
        ])
            ->whereIn('id', $orderIds)
            ->whereHas('items', function ($query) {
                $query->where('status', 'pending');
            })
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($order) {
        
                return [
        
                    'order_id' => $order->id,
        
                    'order_number' => $order->order_number,
        
                    'customer' => $order->user?->full_name,
        
                    'items_count' => $order->items->count(),
        
                    'status' => 'pending',
        
                    'action' => 'ready'
                ];
            });

            /*
        |--------------------------------------------------------------------------
        | LOW STOCK ALERTS
        |--------------------------------------------------------------------------
        */

            $lowStockAlerts = Item::where(
                'quantity',
                '<=',
                20
            )
                ->select(
                    'id',
                    'name',
                    'sku',
                    'quantity',
                    'updated_at'
                )
                ->latest()
                ->take(10)
                ->get();

            /*
        |--------------------------------------------------------------------------
        | RECENT ACTIVITIES
        |--------------------------------------------------------------------------
        */

            $recentActivities = collect();

            /*
        |--------------------------------------------------------------------------
        | DISPATCHED ORDERS
        |--------------------------------------------------------------------------
        */

            $dispatchedOrders = Order::whereIn('id', $orderIds)
                ->where('order_status', 'shipped')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($order) {

                    return [

                        'type' => 'dispatch',

                        'title' => $order->order_number . ' dispatched via Porter',

                        'description' =>
                        'E-Way: ' .
                            ($order->eway_bill_number ?? 'Pending'),

                        'created_at' => $order->updated_at,
                    ];
                });

            /*
        |--------------------------------------------------------------------------
        | RECEIVED TRANSFERS
        |--------------------------------------------------------------------------
        */

            $receivedTransfers = WarehouseTransfer::where(
                'status',
                'received'
            )
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($transfer) {

                    return [

                        'type' => 'transfer',

                        'title' =>
                        'Transfer ' .
                            $transfer->transfer_ref .
                            ' received',

                        'description' =>
                        $transfer->total_units .
                            ' units from ' .
                            $transfer->from_warehouse,

                        'created_at' => $transfer->updated_at,
                    ];
                });

            /*
        |--------------------------------------------------------------------------
        | STOCK ACTIVITIES
        |--------------------------------------------------------------------------
        */

            $stockActivities = Item::latest('updated_at')
                ->take(5)
                ->get()
                ->map(function ($item) {

                    return [

                        'type' => 'stock',

                        'title' =>
                        'Stock updated — ' .
                            $item->sku,
                            
                        'name' =>
                        $item->name,

                        'description' =>
                        'Current quantity: ' .
                            $item->quantity,

                        'created_at' => $item->updated_at,
                    ];
                });

            /*
        |--------------------------------------------------------------------------
        | MERGE ACTIVITIES
        |--------------------------------------------------------------------------
        */

            $recentActivities = $recentActivities
                ->merge($dispatchedOrders)
                ->merge($receivedTransfers)
                ->merge($stockActivities)
                ->sortByDesc('created_at')
                ->values()
                ->take(10);

            return response()->json([

                'status' => true,

                'message' => 'Warehouse dashboard fetched successfully',

                'data' => [

                    'cards' => [

                        'orders_to_process' => [
                            'count' => $ordersToProcess,
                            'label' => 'Awaiting pick/pack'
                        ],

                        'ready_to_dispatch' => [
                            'count' => $readyToDispatch,
                            'label' => 'E-Way Bill pending'
                        ],

                        'low_stock_skus' => [
                            'count' => $lowStockItems,
                            'label' => 'Below threshold'
                        ],

                        'pending_transfer' => [
                            'count' => $pendingTransfer,
                            'label' => 'Pending Transfer'
                        ]
                    ],

                    'order_queue' => $orderQueue,

                    'low_stock_alerts' => $lowStockAlerts,

                    'recent_activities' => $recentActivities
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'status' => false,

                'message' => $e->getMessage()

            ], 500);
        }
    }

    public function getItems(Request $request)
    {
        try {

            $query = Item::with([
                'category',
                'images'
            ]);

            /*
            |--------------------------------------------------------------------------
            | SEARCH BY SKU / NAME
            |--------------------------------------------------------------------------
            */

            if ($request->filled('search')) {

                $search = $request->search;

                $query->where(function ($q) use ($search) {

                    $q->where('sku', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%');
                });
            }

            /*
            |--------------------------------------------------------------------------
            | FILTER BY CATEGORY
            |--------------------------------------------------------------------------
            */

            if ($request->filled('category_id')) {

                $query->where('category_id', $request->category_id);
            }

            /*
            |--------------------------------------------------------------------------
            | FILTER BY WAREHOUSE
            |--------------------------------------------------------------------------
            | Assuming warehouse name stored in transfer tables
            |--------------------------------------------------------------------------
            */

            if ($request->filled('warehouse')) {

                $warehouse = $request->warehouse;

                $query->whereHas('warehouseTransferItems.warehouseTransfer', function ($q) use ($warehouse) {

                    $q->where('from_warehouse', $warehouse)
                        ->orWhere('to_warehouse', $warehouse);
                });
            }

            /*
            |--------------------------------------------------------------------------
            | STOCK FILTER
            |--------------------------------------------------------------------------
            | low_stock  => quantity < 20
            | in_stock   => quantity > 20
            | out_stock  => quantity <= 0
            |--------------------------------------------------------------------------
            */

            if ($request->filled('stock_type')) {

                switch ($request->stock_type) {

                    case 'low_stock':

                        $query->where('quantity', '<', 20)
                            ->where('quantity', '>', 0);

                        break;

                    case 'in_stock':

                        $query->where('quantity', '>', 20);

                        break;

                    case 'out_stock':

                        $query->where('quantity', '<=', 0);

                        break;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | SORTING
            |--------------------------------------------------------------------------
            */

            $query->latest();

            /*
            |--------------------------------------------------------------------------
            | PAGINATION
            |--------------------------------------------------------------------------
            */

            $items = $query->paginate($request->per_page ?? 10);

            return response()->json([
                'status' => true,
                'message' => 'Items fetched successfully',
                'data' => $items
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // public function getReplacementRequests(Request $request)
    // {
    //     try {

    //         $warehouseId = Auth::user()->warehouse_id;

    //         $requests = ReplacementRequest::with([
    //             'user',
    //             'order.items.item.images'
    //         ]);

    //         $requests->whereHas('order.items', function ($query) use ($warehouseId) {
    //             $query->where('warehouse_id', $warehouseId);
    //         });

    //         if ($request->filled('status')) {
    //             $requests->where('status', $request->status);
    //         }

    //         if ($request->filled('search')) {

    //             $search = $request->search;

    //             $requests->where(function ($query) use ($search) {

    //                 $query->where('request_number', 'like', "%{$search}%")
    //                     ->orWhereHas('user', function ($q) use ($search) {
    //                         $q->where('full_name', 'like', "%{$search}%");
    //                     });
    //             });
    //         }

    //         $requests = $requests
    //             ->latest()
    //             ->paginate($request->per_page ?? 10);

    //         // your existing transform code...

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage(),
    //             'line' => $e->getLine()
    //         ], 500);
    //     }
    // }

    public function getReplacementRequests(Request $request)
    {
        try {

            $warehouseId = auth()->user()->warehouse_id;

            $requests = ReplacementRequest::whereIn('status',['received','approved','dispatched'])->with([
                'user',
                'order.items.item.images'
            ]);

            if ($request->filled('status')) {
                $requests->where('status', $request->status);
            }

            if ($request->filled('search')) {

                $search = $request->search;

                $requests->where(function ($query) use ($search) {

                    $query->where(
                        'request_number',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhereHas('user', function ($q) use ($search) {

                            $q->where(
                                'full_name',
                                'like',
                                "%{$search}%"
                            );
                        });
                });
            }

            $requests = $requests->latest()->get();

            $requests = $requests->map(function ($replacement) use ($warehouseId) {
                $formattedItems = [];

                $replacementItems = is_array($replacement->items)
                    ? $replacement->items
                    : json_decode($replacement->items, true);

                if (empty($replacementItems)) {
                    return null;
                }

                foreach ($replacementItems as $replacementItem) {

                    $itemId = $replacementItem['order_item_id'] ?? null;

                    if (!$itemId) {
                        continue;
                    }

                    /*
                |--------------------------------------------------------------------------
                | FIND ORDER ITEM USING ITEM ID
                |--------------------------------------------------------------------------
                */
                    $orderItem = $replacement->order?->items
                        ->where('item_id', $itemId)
                        ->first();
                    // dd($itemId);
                    //  dd($replacement->order->items);

                    if (!$orderItem) {
                        continue;
                    }

                    /*
                |--------------------------------------------------------------------------
                | CHECK WAREHOUSE ALLOCATION
                |--------------------------------------------------------------------------
                */
                    $allocated = DB::table('order_item_allocations')
                        ->where('order_item_id', $orderItem->id)
                        ->where('warehouse_id', $warehouseId)
                        ->exists();

                    if (!$allocated) {
                        continue;
                    }

                    $formattedItems[] = [

                        'order_item_id' => $orderItem->id,

                        'item_id' => $orderItem->item?->id,

                        'item_name' => $orderItem->item?->name,
                        'item_sku' => $orderItem->item?->sku,

                        'image' => $orderItem->item?->images?->first()
                            ? asset('storage/' . $orderItem->item->images->first()->image)
                            : null,

                        'qty' => $replacementItem['quantity'] ?? 1,

                        'unit_price' => $orderItem->unit_price,

                        'total_price' => $orderItem->total_price,

                        'status' => $orderItem->status
                    ];
                }

                if (empty($formattedItems)) {
                    return null;
                }

                $replacement->items = $formattedItems;

                return $replacement;
            })
                ->filter()
                ->values();

            $perPage = $request->per_page ?? 10;

            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            $currentItems = $requests->slice(
                ($currentPage - 1) * $perPage,
                $perPage
            )->values();

            $paginated = new LengthAwarePaginator(
                $currentItems,
                $requests->count(),
                $perPage,
                $currentPage,
                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Replacement requests fetched successfully',
                'total_requests' => $paginated->total(),
                'data' => $paginated
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function packOrder(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'items'                 => 'required|array|min:1',
                'items.*.order_item_id' => 'required|exists:order_items,id',
                'items.*.packed_qty'    => 'required|integer|min:1',
                'number_of_boxes'       => 'nullable|integer|min:1',
                'total_weight'          => 'required|string',
            ]);

            $warehouseId = Auth::user()->warehouse_id;
            if (!$warehouseId) {
                return response()->json(['status' => false, 'message' => 'Warehouse not assigned'], 422);
            }

            $packedItems = [];

            foreach ($request->items as $itemData) {
                $orderItemId = $itemData['order_item_id'];
                $packedQty   = $itemData['packed_qty'];

                $orderItem = OrderItem::with(['order', 'item'])->lockForUpdate()->find($orderItemId);
                if (!$orderItem) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => "Order item {$orderItemId} not found"], 422);
                }

                $allocation = OrderItemAllocation::where('order_item_id', $orderItem->id)
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->first();

                if (!$allocation) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => "Allocation not found for item {$orderItemId}"], 422);
                }

                $remainingAllocation = $allocation->allocated_qty - $allocation->packed_qty;
                if ($packedQty > $remainingAllocation) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => false,
                        'message' => "Only {$remainingAllocation} quantity remaining for item {$orderItemId}"
                    ], 422);
                }

                $remainingOrderQty = $orderItem->quantity - $orderItem->packed_qty;
                if ($packedQty > $remainingOrderQty) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => false,
                        'message' => "Only {$remainingOrderQty} quantity remaining in order item {$orderItemId}"
                    ], 422);
                }

                $packedOrder = PackedOrder::create([
                    'order_id'         => $orderItem->order_id,
                    'order_item_id'    => $orderItem->id,
                    'item_id'          => $orderItem->item_id,
                    'number_of_boxes'  => $request->number_of_boxes,
                    'total_weight'     => $request->total_weight,
                    'packed_by'        => Auth::id(),
                    'packed_at'        => now(),
                ]);

                $newAllocPacked = $allocation->packed_qty + $packedQty;
                $allocationStatus = ($newAllocPacked >= $allocation->allocated_qty) ? 'packed' : 'partial_packed';
                $allocation->update([
                    'packed_qty' => $newAllocPacked,
                    'status'     => $allocationStatus,
                ]);

                $newItemPacked = $orderItem->packed_qty + $packedQty;
                $orderItemStatus = ($newItemPacked >= $orderItem->quantity) ? 'packed' : 'partial_packed';
                $orderItem->update([
                    'packed_qty' => $newItemPacked,
                    'status'     => $orderItemStatus,
                ]);

                $packedItems[] = [
                    'packed_order_id' => $packedOrder->id,
                    'order_item_id'   => $orderItem->id,
                    'packed_qty'      => $packedQty,
                    // aapke additional fields yahan add kar sakte hain
                ];
            }

            // Final order status update (loop ke baad ek baar)
            $firstOrderItem = OrderItem::find($request->items[0]['order_item_id']);
            $order = $firstOrderItem->order;

            // FIX: orderItems() -> items()
            $totalItems = $order->items()->count();
            $packedCount = $order->items()->where('status', 'packed')->count();
            $partialCount = $order->items()->where('status', 'partial_packed')->count();

            if ($packedCount == $totalItems) {
                $order->update(['order_status' => 'packed']);
            } elseif ($packedCount > 0 || $partialCount > 0) {
                $order->update(['order_status' => 'partial_packed']);
            } else {
                $order->update(['order_status' => 'pending']);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Items packed successfully',
                'data'    => ['packed_items' => $packedItems]
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

    public function getDamagedItems(Request $request, $warehouse_id = null)
    {
        try {

            $query = DamagedItem::with([
                'warehouse:id,name',
                'item:id,name,sku',
                'item.images:id,item_id,image',
                'reportedBy:id,full_name,email'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FILTER BY WAREHOUSE
            |--------------------------------------------------------------------------
            */

            if ($warehouse_id) {

                $query->where('warehouse_id', $warehouse_id);
            }

            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */

            if ($request->filled('search')) {

                $search = $request->search;

                $query->where(function ($q) use ($search) {

                    $q->whereHas('item', function ($itemQuery) use ($search) {

                        $itemQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('sku', 'like', '%' . $search . '%');
                    })

                        ->orWhereHas('warehouse', function ($warehouseQuery) use ($search) {

                            $warehouseQuery->where('name', 'like', '%' . $search . '%');
                        })

                        ->orWhere('reason', 'like', '%' . $search . '%');
                });
            }

            /*
            |--------------------------------------------------------------------------
            | PAGINATION
            |--------------------------------------------------------------------------
            */

            $damagedItems = $query
                ->latest()
                ->paginate($request->per_page ?? 10);

            /*
            |--------------------------------------------------------------------------
            | IMAGE URL FORMAT
            |--------------------------------------------------------------------------
            */

            $damagedItems->getCollection()->transform(function ($damagedItem) {

                if ($damagedItem->item && $damagedItem->item->images) {

                    $damagedItem->item->images->transform(function ($image) {

                        if (filter_var($image->image, FILTER_VALIDATE_URL)) {

                            $image->image_url = $image->image;
                        } else {

                            $image->image_url = asset('storage/' . $image->image);
                        }

                        return $image;
                    });
                }

                return $damagedItem;
            });

            return response()->json([

                'status' => true,

                'message' => 'Damaged items fetched successfully',

                'total_damaged_items' => $damagedItems->total(),

                'data' => $damagedItems

            ]);
        } catch (\Exception $e) {

            return response()->json([

                'status' => false,

                'message' => $e->getMessage(),

                'line' => $e->getLine()

            ], 500);
        }
    }

    public function replacementRequestDetails($id)
    {
        try {

            $requestData = ReplacementRequest::with([

                'user',
                'order.items.item.images'

            ])->findOrFail($id);

            return response()->json([

                'status' => true,

                'message' => 'Replacement request details fetched successfully',

                'data' => [

                    /*
                |--------------------------------------------------------------------------
                | REQUEST DETAILS
                |--------------------------------------------------------------------------
                */

                    'request_id' => $requestData->id,

                    'request_number' => $requestData->request_number,

                    'status' => $requestData->status,

                    'reason' => $requestData->reason,

                    'message' => $requestData->message,

                    'request_images' => $requestData->images
                        ? json_decode($requestData->images)
                        : [],

                    'request_date' => $requestData->request_date,

                    'approved_at' => $requestData->approved_at,

                    'rejected_at' => $requestData->rejected_at,

                    'received_at' => $requestData->received_at,

                    'admin_notes' => $requestData->admin_notes,

                    /*
                |--------------------------------------------------------------------------
                | USER DETAILS
                |--------------------------------------------------------------------------
                */

                    'customer' => [

                        'id' => $requestData->user?->id,
                        'name' => $requestData->user?->name,
                        'email' => $requestData->user?->email,
                        'phone' => $requestData->user?->phone,
                    ],

                    /*
                |--------------------------------------------------------------------------
                | ORDER DETAILS
                |--------------------------------------------------------------------------
                */

                    'order' => [

                        'order_id' => $requestData->order?->id,

                        'order_number' => $requestData->order?->order_number,

                        'total_amount' => $requestData->order?->total_amount,

                        'payment_status' => $requestData->order?->payment_status,

                        'order_status' => $requestData->order?->order_status,
                    ],

                    /*
                |--------------------------------------------------------------------------
                | ORDER ITEMS
                |--------------------------------------------------------------------------
                */

                    'items' => $requestData->order?->items->map(function ($orderItem) {

                        return [

                            'order_item_id' => $orderItem->id,

                            'item_id' => $orderItem->item?->id,

                            'item_name' => $orderItem->item?->name,

                            'sku' => $orderItem->item?->sku,

                            'price' => $orderItem->unit_price,

                            'quantity' => $orderItem->quantity,

                            'total_price' => $orderItem->total_price,

                            /*
                        |--------------------------------------------------------------------------
                        | ITEM IMAGES
                        |--------------------------------------------------------------------------
                        */

                            'images' => $orderItem->item?->images->map(function ($image) {

                                return [

                                    'id' => $image->id,

                                    'image' => $image->image
                                ];
                            }),
                        ];
                    }),
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'status' => false,

                'message' => $e->getMessage(),

                'line' => $e->getLine()

            ], 500);
        }
    }

    // public function warehouseManagerNotifications(Request $request)
    // {
    //     try {

    //         $notifications = Notification::with('user')

    //             ->whereHas('user', function ($query) {

    //                 $query->where('account_type', 'warehouse_manager');
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
    //             ->paginate($request->per_page ?? 10);

    //         return response()->json([

    //             'status' => true,

    //             'message' => 'Warehouse manager notifications fetched successfully',

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
    
    
    // public function warehouseManagerNotifications(Request $request)
    // {
    //     try {
    
    //         $notifications = Notification::with('user')
    
    //             ->whereHas('user', function ($query) {
    
    //                 $query->where('account_type', 'warehouse_manager');
    //             })
    
    //             /*
    //             |--------------------------------------------------------------------------
    //             | LAST 60 DAYS ONLY
    //             |--------------------------------------------------------------------------
    //             */
    //             ->where('created_at', '>=', Carbon::now()->subDays(60));
    
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
    
    //             'message' => 'Warehouse manager notifications fetched successfully',
    
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
    
    public function warehouseManagerNotifications(Request $request)
    {
        try {
    
            $notifications = Notification::with('user')
                ->where('user_id', auth()->id())
                ->whereHas('user', function ($query) {
                    $query->where('account_type', 'warehouse_manager');
                })
    
                // Last 60 days only
                ->where('created_at', '>=', Carbon::now()->subDays(60));
    
            /*
            |--------------------------------------------------------------------------
            | FILTER BY READ STATUS
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
    
                'message' => 'Warehouse manager notifications fetched successfully',
    
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

    public function markNotificationAsRead($id)
    {
        try {

            $notification = Notification::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | MARK AS READ
            |--------------------------------------------------------------------------
            */

            $notification->update([

                'is_read' => 1
            ]);

            return response()->json([

                'status' => true,

                'message' => 'Notification marked as read successfully',

                'data' => $notification
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'status' => false,

                'message' => $e->getMessage(),

                'line' => $e->getLine()

            ], 500);
        }
    }
    public function markNotificationAllAsRead()
    {
        try {
    
            Notification::where('user_id', auth()->id())
                ->update([
                    'is_read' => 1
                ]);
    
            return response()->json([
                'status' => true,
                'message' => 'All notifications marked as read successfully'
            ]);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    public function deleteNotification($id)
    {
        try {

            $notification = Notification::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | DELETE NOTIFICATION
            |--------------------------------------------------------------------------
            */

            $notification->delete();

            return response()->json([

                'status' => true,

                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'status' => false,

                'message' => $e->getMessage(),

                'line' => $e->getLine()

            ], 500);
        }
    }
  public function deleteAllNotification()
    {
        try {
    
            Notification::where('user_id', auth()->id())->delete();
    
            return response()->json([
                'status' => true,
                'message' => 'All notifications deleted successfully'
            ]);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function getWarehouseItems($warehouseId)
    {
        try {

            $warehouse = Warehouse::findOrFail($warehouseId);

            $warehouseItems = WarehouseItem::with([
                'item.images',
                'item.category'
            ])
                ->where('warehouse_id', $warehouseId)
                ->get();

            $data = $warehouseItems->map(function ($warehouseItem) {

                $item = $warehouseItem->item;

                return [
                    'warehouse_item_id' => $warehouseItem->id,

                    'warehouse_id'      => $warehouseItem->warehouse_id,

                    'available_quantity' => (
                        $warehouseItem->quantity -
                        $warehouseItem->reserved_quantity
                    ),

                    'reserved_quantity' => $warehouseItem->reserved_quantity,
                    'updated_quantity' => $warehouseItem->updated_quantity,

                    'total_quantity'    => $warehouseItem->quantity,

                    'item' => [

                        'id'               => $item->id,
                        'category_id'      => $item->category_id,
                        'name'             => $item->name,
                        'slug'             => $item->slug,
                        'sku'              => $item->sku,
                        'price'            => $item->price,
                        'type'             => $item->type,
                        'quantity'         => $item->quantity,
                        'rating'           => $item->rating,
                        'is_featured'      => $item->is_featured,
                        'reviews_count'    => $item->reviews_count,
                        'wishlist_count'   => $item->wishlist_count,
                        'description'      => $item->description,
                        'specifications'   => $item->specifications,
                        'updated_at'   => $item->updated_at,

                        'images' => $item->images->map(function ($image) {

                            return [
                                'id'    => $image->id,
                                'image' => asset('/storage/' . $image->image),
                            ];
                        }),
                    ]
                ];
            });

            return response()->json([
                'status'   => true,
                'message'  => 'Warehouse items fetched successfully',
                'warehouse' => [
                    'id'      => $warehouse->id,
                    'name'    => $warehouse->name,
                    'code'    => $warehouse->code,
                    'city'    => $warehouse->city,
                    'state'   => $warehouse->state,
                    'country' => $warehouse->country,
                ],
                'data' => $data
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // public function getWarehouseItemquantity($warehouseId)
    // {
    //     try {

    //         $warehouse = Warehouse::findOrFail($warehouseId);

    //         $warehouseItems = WarehouseItem::with([
    //             'item.images',
    //             'item.category'
    //         ])
    //             ->where('warehouse_id', $warehouseId)
    //             ->get();

    //         $data = $warehouseItems->map(function ($warehouseItem) {

    //             $item = $warehouseItem->item;

    //             return [
    //                 'warehouse_item_id' => $warehouseItem->id,

    //                 'warehouse_id'      => $warehouseItem->warehouse_id,

    //                 'available_quantity' => (
    //                     $warehouseItem->quantity -
    //                     $warehouseItem->reserved_quantity
    //                 ),

    //                 'reserved_quantity' => $warehouseItem->reserved_quantity,
    //                 'updated_quantity' => $warehouseItem->updated_quantity,

    //                 'total_quantity'    => $warehouseItem->quantity,

    //                 'item' => [

    //                     'id'               => $item->id,
    //                     'category_id'      => $item->category_id,
    //                     'name'             => $item->name,
    //                     'slug'             => $item->slug,
    //                     'sku'              => $item->sku,
    //                     'price'            => $item->price,
    //                     'type'             => $item->type,
    //                     'quantity'         => $item->quantity,
    //                     'rating'           => $item->rating,
    //                     'description'      => $item->description,
    //                     'specifications'   => $item->specifications,
    //                     'updated_at'   => $item->updated_at,

    //                     'images' => $item->images->map(function ($image) {

    //                         return [
    //                             'id'    => $image->id,
    //                             'image' => asset('/storage/' . $image->image),
    //                         ];
    //                     }),
    //                 ]
    //             ];
    //         });

    //         return response()->json([
    //             'status'   => true,
    //             'message'  => 'Warehouse items fetched successfully',
    //             'data' => $data
    //         ]);
    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'status'  => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getWarehouseItemquantity($warehouseId)
    {
        try {

            $warehouse = Warehouse::findOrFail($warehouseId);

            $warehouseItems = WarehouseItem::with([
                'item.images',
                'item.category'
            ])
                ->where('warehouse_id', $warehouseId)

                // Only today's records
                ->whereDate('updated_at', today())

                // Latest updated first
                ->orderByDesc('updated_at')

                ->get();

            $data = $warehouseItems->map(function ($warehouseItem) {

                $item = $warehouseItem->item;

                return [
                    'warehouse_item_id' => $warehouseItem->id,

                    'warehouse_id' => $warehouseItem->warehouse_id,

                    'available_quantity' => (
                        $warehouseItem->quantity -
                        $warehouseItem->reserved_quantity
                    ),

                    'reserved_quantity' => $warehouseItem->reserved_quantity,

                    // +10 added stock, -5 damaged stock
                    'updated_quantity' => $warehouseItem->updated_quantity,

                    'total_quantity' => $warehouseItem->quantity,

                    'updated_at' => $warehouseItem->updated_at,

                    'item' => [

                        'id' => $item->id,
                        'category_id' => $item->category_id,
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'sku' => $item->sku,
                        'price' => $item->price,
                        'type' => $item->type,
                        'quantity' => $item->quantity,
                        'rating' => $item->rating,
                        'description' => $item->description,
                        'specifications' => $item->specifications,
                        'updated_at' => $item->updated_at,

                        'images' => $item->images->map(function ($image) {

                            return [
                                'id' => $image->id,
                                'image' => asset('storage/' . $image->image),
                            ];
                        }),
                    ]
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Today warehouse stock movements fetched successfully',

                'warehouse' => [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                ],

                'total_records' => $data->count(),

                'data' => $data
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function notifyMe(Request $request)
    {
        try {

            $request->validate([
                'item_id' => 'required|exists:items,id'
            ]);

            $user = auth()->user();

            if (!$user) {

                return response()->json([
                    'status' => false,
                    'message' => 'Login to get notified when this product is back in stock.'
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | ONLY B2C USERS
        |--------------------------------------------------------------------------
        */

            if ($user->account_type != 'b2c') {

                return response()->json([
                    'status' => false,
                    'message' => 'Only B2C users can subscribe'
                ], 403);
            }

            /*
        |--------------------------------------------------------------------------
        | CHECK ITEM
        |--------------------------------------------------------------------------
        */

            $item = Item::findOrFail($request->item_id);

            /*
        |--------------------------------------------------------------------------
        | ITEM MUST BE OUT OF STOCK
        |--------------------------------------------------------------------------
        */

            if ($item->quantity > 0) {

                return response()->json([
                    'status' => false,
                    'message' => 'Item already in stock'
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | CREATE SUBSCRIPTION
        |--------------------------------------------------------------------------
        */

            BackInStockNotification::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'item_id' => $item->id
                ],
                [
                    'is_notified' => false
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'You will be notified when item is back in stock'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateTransfer(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            /*
    |--------------------------------------------------------------------------
    | GET TRANSFER
    |--------------------------------------------------------------------------
    */

            $transfer = WarehouseTransfer::with('items')->findOrFail($id);

            /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

            $validated = $request->validate([

                'from_warehouse' => 'required|exists:warehouses,id',
                'to_warehouse'   => 'required|exists:warehouses,id',

                'items' => 'required|array|min:1',

                'items.*.item_id'  => 'required|exists:items,id',
                'items.*.quantity' => 'required|numeric|min:1',
            ]);

            if ($transfer->status != 'draft') {

                return response()->json([
                    'status' => false,
                    'message' => 'Transfer cannot be edited because it is already ' . $transfer->status . '.'
                ], 422);
            }


            /*
    |--------------------------------------------------------------------------
    | PREVENT SAME WAREHOUSE
    |--------------------------------------------------------------------------
    */

            if ($validated['from_warehouse'] == $validated['to_warehouse']) {

                return response()->json([
                    'status' => false,
                    'message' => 'Source and destination warehouse cannot be same'
                ], 422);
            }

            /*
    |--------------------------------------------------------------------------
    | UPDATE TRANSFER
    |--------------------------------------------------------------------------
    */

            $transfer->update([

                'from_warehouse' => $validated['from_warehouse'],
                'to_warehouse'   => $validated['to_warehouse'],
            ]);

            /*
    |--------------------------------------------------------------------------
    | DELETE OLD ITEMS
    |--------------------------------------------------------------------------
    */

            WarehouseTransferItem::where(
                'warehouse_transfer_id',
                $transfer->id
            )->delete();

            $totalItems = 0;
            $totalUnits = 0;
            $totalValue = 0;

            /*
    |--------------------------------------------------------------------------
    | STORE NEW ITEMS
    |--------------------------------------------------------------------------
    */

            foreach ($validated['items'] as $transferItem) {

                $item = Item::findOrFail($transferItem['item_id']);

                /*
        |--------------------------------------------------------------------------
        | CHECK SOURCE WAREHOUSE STOCK
        |--------------------------------------------------------------------------
        */

                $warehouseItem = WarehouseItem::where(
                    'warehouse_id',
                    $validated['from_warehouse']
                )
                    ->where(
                        'item_id',
                        $item->id
                    )
                    ->first();

                if (!$warehouseItem) {

                    DB::rollBack();

                    return response()->json([
                        'status' => false,
                        'message' => "{$item->name} not found in source warehouse"
                    ], 422);
                }

                if ($warehouseItem->quantity < $transferItem['quantity']) {

                    DB::rollBack();

                    return response()->json([
                        'status' => false,
                        'message' => "Insufficient stock for {$item->name}",
                        'available_quantity' => $warehouseItem->quantity
                    ], 422);
                }

                $totalPrice = $item->price * $transferItem['quantity'];

                WarehouseTransferItem::create([

                    'warehouse_transfer_id' => $transfer->id,
                    'item_id'               => $item->id,

                    'quantity'              => $transferItem['quantity'],
                    'unit_price'            => $item->price,
                    'total_price'           => $totalPrice,
                ]);

                $totalItems++;
                $totalUnits += $transferItem['quantity'];
                $totalValue += $totalPrice;
            }

            /*
    |--------------------------------------------------------------------------
    | UPDATE TOTALS
    |--------------------------------------------------------------------------
    */

            $transfer->update([

                'total_items' => $totalItems,
                'total_units' => $totalUnits,
                'total_value' => $totalValue,
            ]);

            DB::commit();

            /*
    |--------------------------------------------------------------------------
    | NOTIFY DESTINATION MANAGER
    |--------------------------------------------------------------------------
    */

            $this->notifyWarehouseManagers(

                $transfer->to_warehouse,

                'Warehouse Transfer Updated',

                "Transfer {$transfer->transfer_ref} has been updated."
            );

            return response()->json([
                'status' => true,
                'message' => 'Transfer updated successfully',
                'data' => $transfer->load('items')
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // public function getInTransitItems()
    // {
    //     try {

    //         $user = Auth::user();

    //         $items = WarehouseTransferItem::with([
    //             'item.images',
    //             'transfer.fromWarehouse:id,name',
    //             'transfer.toWarehouse:id,name'
    //         ])
    //         ->whereHas('transfer', function ($q) use ($user) {

    //             $q->where('status', 'in_transit')
    //               ->where('to_warehouse', $user->warehouse_id);
    //         })
    //         ->get();
    //         dd($items);
    //         // ->paginate(10);

    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'In transit items fetched successfully',
    //             'data'    => $items
    //         ]);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'status'  => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function getInTransitItems()
    {
        try {

            $user = Auth::user();
            $items = WarehouseTransferItem::with([

                'item.images',

                'transfer:id,transfer_ref,from_warehouse,to_warehouse,status',

                'transfer.fromWarehouse:id,name',

                'transfer.toWarehouse:id,name'

            ])

                ->whereHas('transfer', function ($q) use ($user) {

                    $q->where('status', 'in_transit')

                        ->where(
                            'to_warehouse',
                            $user->warehouse_id
                        );
                })

                ->latest()

                ->paginate(10);

            return response()->json([

                'status'  => true,

                'message' => 'In transit items fetched successfully',

                'data'    => $items

            ]);
        } catch (\Exception $e) {

            return response()->json([

                'status'  => false,

                'message' => $e->getMessage(),

                'line'    => $e->getLine()

            ], 500);
        }
    }

    public function dispatchReplacement(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

            $validated = $request->validate([

                'replacement_request_id' => [
                    'required',
                    'exists:replacement_requests,id'
                ],

                'warehouse_id' => [
                    'required',
                    'exists:warehouses,id'
                ],

                'transport_mode' => [
                    'nullable',
                    'in:Road,Rail,Air,Ship'
                ],

                'vehicle_number' => [
                    'nullable',
                    'string',
                    'max:100'
                ],

                'tracking_link' => [
                    'nullable',
                    'url'
                ],

                'eway_bill_number' => [
                    'nullable',
                    'string',
                    'max:255'
                ],
            ]);

            /*
        |--------------------------------------------------------------------------
        | FETCH REQUEST
        |--------------------------------------------------------------------------
        */

            $replacement = ReplacementRequest::with([
                'order.items.item',
                'user'
            ])->findOrFail($validated['replacement_request_id']);

            /*
        |--------------------------------------------------------------------------
        | STATUS CHECK
        |--------------------------------------------------------------------------
        */

            if ($replacement->status != 'approved') {

                return response()->json([
                    'status'  => false,
                    'message' => 'Only received replacement requests can be dispatched, Kindly contact to admin.'
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | ALREADY DISPATCHED CHECK
        |--------------------------------------------------------------------------
        */

            if ($replacement->status == 'dispatched') {

                return response()->json([
                    'status'  => false,
                    'message' => 'Replacement already dispatched'
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | FETCH WAREHOUSE
        |--------------------------------------------------------------------------
        */

            $warehouse = Warehouse::findOrFail(
                $validated['warehouse_id']
            );

            /*
        |--------------------------------------------------------------------------
        | CHECK STOCK
        |--------------------------------------------------------------------------
        */

            foreach ($replacement->order->items as $orderItem) {

                $warehouseItem = WarehouseItem::where(
                    'warehouse_id',
                    $warehouse->id
                )
                    ->where(
                        'item_id',
                        $orderItem->item_id
                    )
                    ->first();

                if (!$warehouseItem) {

                    return response()->json([
                        'status'  => false,
                        'message' => 'Item not found in warehouse',
                        'item'    => $orderItem->item?->name
                    ], 422);
                }

                if ($warehouseItem->quantity < $orderItem->quantity) {

                    return response()->json([
                        'status'  => false,
                        'message' => 'Insufficient stock for item: ' .
                            ($orderItem->item?->name ?? ''),
                        'available_quantity' => $warehouseItem->quantity,
                        'required_quantity'  => $orderItem->quantity
                    ], 422);
                }
            }

            /*
        |--------------------------------------------------------------------------
        | CREATE REPLACEMENT INVOICE
        |--------------------------------------------------------------------------
        */

            $invoice = Invoice::create([

                'order_id'       => $replacement->order_id,
                'client_id'      => $replacement->user_id,
                'invoice_number' => 'RPL-' . now()->timestamp,
                'type'           => 'replacement',
                'pi_status'      => 'dispatched',

                'total_amount'   => 0,
                'amount_paid'    => 0,
            ]);

            /*
        |--------------------------------------------------------------------------
        | CREATE INVOICE ITEMS
        |--------------------------------------------------------------------------
        */

            foreach ($replacement->order->items as $orderItem) {

                InvoiceItem::create([

                    'invoice_id'    => $invoice->id,
                    'order_item_id' => $orderItem->id,
                    'item_id'       => $orderItem->item_id,

                    'quantity'      => $orderItem->quantity,

                    'unit_price'    => 0,
                    'total_price'   => 0,
                ]);

                /*
            |--------------------------------------------------------------------------
            | DEDUCT STOCK
            |--------------------------------------------------------------------------
            */

                $warehouseItem = WarehouseItem::where(
                    'warehouse_id',
                    $warehouse->id
                )
                    ->where(
                        'item_id',
                        $orderItem->item_id
                    )
                    ->first();

                $warehouseItem->decrement(
                    'quantity',
                    $orderItem->quantity
                );

                /*
            |--------------------------------------------------------------------------
            | MAIN STOCK DEDUCT
            |--------------------------------------------------------------------------
            */

                Item::where(
                    'id',
                    $orderItem->item_id
                )->decrement(
                    'quantity',
                    $orderItem->quantity
                );
            }

            /*
        |--------------------------------------------------------------------------
        | STORE DELIVERY DETAILS
        |--------------------------------------------------------------------------
        */

            OrderDetail::updateOrCreate(

                [
                    'order_id' => $replacement->order_id
                ],

                [
                    'transport_mode'   => $request->transport_mode ?? 'Road',

                    'vehicle_number'   => $request->vehicle_number,

                    'eway_bill_number' => $request->eway_bill_number,

                    'tracking_link'    => $request->tracking_link,
                ]
            );

            /*
        |--------------------------------------------------------------------------
        | UPDATE REPLACEMENT STATUS
        |--------------------------------------------------------------------------
        */

            $replacement->update([

                'status'        => 'dispatched',
                'dispatched_at' => now(),
            ]);

            DB::commit();

            return response()->json([

                'status'  => true,

                'message' => 'Replacement items dispatched successfully',

                'data' => [

                    'replacement_request_id' => $replacement->id,

                    'invoice_id'     => $invoice->id,

                    'invoice_number' => $invoice->invoice_number,

                    'warehouse_id'   => $warehouse->id,

                    'warehouse_name' => $warehouse->name,

                    'dispatch_date'  => now(),

                    'tracking_link'  => $request->tracking_link,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            DB::rollBack();

            return response()->json([

                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()

            ], 422);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status'  => false,
                'message' => $e->getMessage()

            ], 500);
        }
    }

    public function moveReplacementToDamaged(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

            $validated = $request->validate([

                'replacement_request_id' => [
                    'required',
                    'exists:replacement_requests,id'
                ],

                'notes' => [
                    'nullable',
                    'string'
                ],
            ]);

            /*
        |--------------------------------------------------------------------------
        | FETCH REPLACEMENT REQUEST
        |--------------------------------------------------------------------------
        */

            $replacement = ReplacementRequest::with([
                'order.items.item'
            ])->findOrFail(
                $validated['replacement_request_id']
            );
            /*
        |--------------------------------------------------------------------------
        | STATUS CHECK
        |--------------------------------------------------------------------------
        */

            if (!in_array($replacement->status, [
                'received'
            ])) {

                return response()->json([
                    'status'  => false,
                    'message' => 'Replacement items must be received first'
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | REASON CHECK
        |--------------------------------------------------------------------------
        */

            $damageKeywords = [

                'damage',
                'damaged',
                'broken',
                'defective',
                'crack',
                'cracked'
            ];

            $reason = strtolower($replacement->reason);

            $isDamaged = false;

            foreach ($damageKeywords as $keyword) {

                if (str_contains($reason, $keyword)) {

                    $isDamaged = true;
                    break;
                }
            }

            if (!$isDamaged) {

                return response()->json([
                    'status'  => false,
                    'message' => 'This replacement request is not marked as damaged'
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | WAREHOUSE
        |--------------------------------------------------------------------------
        */

            $warehouseId = auth()->user()->warehouse_id;

            /*
        |--------------------------------------------------------------------------
        | STORE DAMAGED ITEMS
        |--------------------------------------------------------------------------
        */

            foreach ($replacement->order->items as $orderItem) {

                DamagedItem::create([

                    'warehouse_id' => $warehouseId,

                    'item_id'      => $orderItem->item_id,

                    'quantity'     => $orderItem->quantity,

                    'reason'       => $replacement->reason,

                    'notes'        => $request->notes,

                    'reported_by'  => auth()->id(),
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | UPDATE REPLACEMENT STATUS
        |--------------------------------------------------------------------------
        */

            $replacement->update([

                'status' => 'received'
            ]);

            DB::commit();

            return response()->json([

                'status'  => true,

                'message' => 'Items moved to damaged inventory successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            DB::rollBack();

            return response()->json([

                'status'  => false,

                'message' => 'Validation failed',

                'errors'  => $e->errors()

            ], 422);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'status'  => false,

                'message' => $e->getMessage(),

            ], 500);
        }
    }
}
