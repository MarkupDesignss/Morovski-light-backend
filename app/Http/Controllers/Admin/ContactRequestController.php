<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactRequestController extends Controller
{
    // 📌 List Page
    // public function index()
    // {
    //     $requests = DB::table('contact_requests')
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(10);

    //     return view('admin.contact_requests.index', compact('requests'));
    // }
    
    public function index()
    {
        $requests = \App\Models\ContactRequest::with(['attachments', 'category'])
            ->latest()
            ->paginate(10);
    
        return view('admin.contact_requests.index', compact('requests'));
    }

    // 📌 Detail Page
    public function show($id)
    {
        $request = DB::table('contact_requests')
            ->where('id', $id)
            ->first();

        $attachments = DB::table('contact_attachments')
            ->where('contact_request_id', $id)
            ->get();

        return view('admin.contact_requests.show', compact('request', 'attachments'));
    }
}
