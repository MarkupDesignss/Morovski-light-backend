<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\ContactRequest;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\SupportTicketImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;


class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|min:10|max:15',
            'category_id' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:102400',
        ]);

        DB::beginTransaction();
        try {
            $contact = ContactRequest::create($request->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'category_id',
                'subject',
                'message',
            ]));

            // Upload attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('contact_attachments', 'public');

                    $contact->attachments()->create([
                        'file_path' => $path
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' =>  __('messages.contact_request_submitted')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createTicket(Request $request)
    {
        try {

            $user = Auth::user();

            /*
        --------------------------------
        ALLOWED QUERY TYPES
        --------------------------------
        */
            $allowedQueryTypes = [
                'order_issue',
                'payment_issue',
                'delivery_issue',
                'refund_request',
                'product_issue',
                'other'
            ];

            /*
        --------------------------------
        VALIDATION
        --------------------------------
        */
            $validator = Validator::make($request->all(), [

                'full_name' => 'required|string|max:255',

                'email' => 'required|email|max:255',

                'order_number' => 'required|string|max:255',

                'query_type' => [
                    'required',
                    Rule::in($allowedQueryTypes)
                ],

                'message' => 'required|string',

                'images' => 'nullable|array',

                'images.*' => 'image|mimes:jpg,jpeg,png,webp,avif',
            ]);

            if ($validator->fails()) {

                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            /*
        --------------------------------
        FIND ORDER
        --------------------------------
        */
            $order = Order::where('order_number', $request->order_number)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {

                return response()->json([
                    'status' => false,
                    'message' => 'No order found for this order number'
                ], 404);
            }

            /*
        --------------------------------
        CREATE TICKET
        --------------------------------
        */
            $ticket = SupportTicket::create([

                'user_id' => $user->id,

                'order_id' => $order->id,

                'full_name' => $request->full_name,

                'email' => $request->email,

                'order_number' => $request->order_number,

                'query_type' => $request->query_type,

                'message' => $request->message,

                'status' => 'in_progress',
            ]);

            /*
        --------------------------------
        STORE IMAGES
        --------------------------------
        */
            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $image) {

                    $path = $image->store('support_tickets', 'public');

                    SupportTicketImage::create([
                        'ticket_id' => $ticket->id,
                        'image_path' => $path,
                    ]);
                }
            }

            /*
        --------------------------------
        LOAD IMAGES
        --------------------------------
        */
            $ticket->load('images');

            return response()->json([
                'status' => true,
                'message' => 'Support ticket created successfully',

                'data' => [

                    'id' => $ticket->id,

                    'full_name' => $ticket->full_name,

                    'email' => $ticket->email,

                    'order_number' => $ticket->order_number,

                    'query_type' => $ticket->query_type,

                    'message' => $ticket->message,

                    'status' => $ticket->status,

                    'images' => $ticket->images->map(function ($img) {

                        return [
                            'id' => $img->id,
                            'image' => url('/storage/' . $img->image_path),
                        ];
                    }),

                    'created_at' => $ticket->created_at,
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => "Sometihng Went Wrong",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
