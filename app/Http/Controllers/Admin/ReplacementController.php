<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReplacementRequest;

class ReplacementController extends Controller
{

    public function index(Request $request)
    {
        $query = ReplacementRequest::with([
            'user',
            'order'
        ])->latest();

        // Search
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {

                        $uq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('order', function ($oq) use ($search) {

                        $oq->where('order_number', 'like', "%{$search}%");
                    });
            });
        }

        // Status Filter
        if ($request->filled('status')) {

            $query->where('status', $request->status);
        }

        $requests = $query->paginate(15);

        return view('admin.replacement_requests.index', compact('requests'));
    }

    public function show($id)
    {
        $replacement = ReplacementRequest::with([
            'user',
            'order'
        ])->findOrFail($id);

        return view('admin.replacement_requests.show', compact('replacement'));
    }

    public function approve(Request $request, $id)
    {
        $replacement = ReplacementRequest::findOrFail($id);

        $replacement->update([
            'status' => 'approved',
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes
        ]);

        return redirect()->back()->with(
            'success',
            'Replacement request approved successfully.'
        );
    }


    public function reject(Request $request, $id)
    {
        $replacement = ReplacementRequest::findOrFail($id);

        $replacement->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'admin_notes' => $request->admin_notes
        ]);

        return redirect()->back()->with(
            'success',
            'Replacement request rejected successfully.'
        );
    }

    public function markReceived(Request $request, $id)
    {
        $replacement = ReplacementRequest::findOrFail($id);

        $replacement->update([
            'status' => 'received',
            'received_at' => now(),
            'admin_notes' => $request->admin_notes
        ]);

        return redirect()->back()->with(
            'success',
            'Replacement item marked as received.'
        );
    }
}
