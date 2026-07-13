<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;

class SupportTicketController extends Controller
{

    public function index()
    {
        $tickets = SupportTicket::with([
            'user',
            'order',
            'images'
        ])
            ->latest()
            ->paginate(20);

        return view('admin.support_tickets.index', compact('tickets'));
    }

    public function show($id)
    {
        $ticket = SupportTicket::with([
            'user',
            'images',
            'order.items.item.images'
        ])->findOrFail($id);

        return view('admin.support_tickets.show', compact('ticket'));
    }
}
