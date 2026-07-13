<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDelete;
use App\Models\BusinessProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserDeleteController extends Controller
{
    public function index(Request $request)
    {
        $query = UserDelete::where('otp_verified',1)->latest();
        // dd($query);
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deletedUsers = $query->paginate(10)->withQueryString();
        return view('admin.users.deleted-users', compact('deletedUsers'));
    }

    /*
    --------------------------------
    APPROVE REQUEST
    --------------------------------
    */

    public function approve($id)
    {
        DB::beginTransaction();

        try {

            $requestDelete = UserDelete::find($id);

            if (!$requestDelete) {
                return back()->with('error', 'Request not found');
            }

            if (!$requestDelete->otp_verified) {
                return back()->with('error', 'OTP not verified');
            }


            if (!$user) {
                return back()->with('error', 'User not found');
            }

            $user = User::find($requestDelete->user_id);
            /*
            --------------------------------
            DELETE BUSINESS PROFILE
            --------------------------------
            */

            if ($user->account_type === 'b2b') {

                $businessProfile = BusinessProfile::where('user_id', $user->id)->first();

                if ($businessProfile) {
                    $businessProfile->delete();
                }
            }

            /*
            --------------------------------
            DELETE USER
            --------------------------------
            */

            $user->delete();

            /*
            --------------------------------
            UPDATE REQUEST STATUS
            --------------------------------
            */

            $requestDelete->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Account deleted successfully');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    /*
    --------------------------------
    REJECT REQUEST
    --------------------------------
    */

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejected_reason' => 'nullable|string'
        ]);

        $deleteRequest = UserDelete::find($id);

        if (!$deleteRequest) {
            return back()->with('error', 'Request not found');
        }

        $deleteRequest->update([
            'status' => 'rejected',
            'rejected_reason' => $request->rejected_reason,
        ]);

        return back()->with('success', 'Request rejected successfully');
    }
}
