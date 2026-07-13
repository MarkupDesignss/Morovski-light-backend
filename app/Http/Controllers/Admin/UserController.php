<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BusinessStatusMail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Models\BusinessProfile;
use App\Models\RejectedUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class UserController extends Controller
{
    /* -----------------------------
       List All Users
    ----------------------------- */

    // public function index()
    // {
    //     $users = User::with(['businessProfile', 'roles'])
    //         ->where(function ($query) {
    //             $query->whereHas('subscriptions.membership', function ($q) {
    //                 $q->where('name', 'Free');
    //             })
    //                 ->orWhereDoesntHave('subscriptions');
    //         })
    //         ->orderBy('id', 'desc')
    //         ->paginate(10);

    //     $roles = Role::all();

    //     $title = "Manage all users";

    //     return view('admin.users.index', compact('users', 'title', 'roles'));
    // }
    public function index()
    {
        $users = User::with('roles')
            ->where('is_registered', 1)
            ->where('account_type', 'b2c')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $roles = Role::all();

        // Totals for header
        $totalUsers = User::where('is_registered', '1')->count();
        $totalB2B = User::where('account_type', 'b2b')->where('is_registered', '1')->where('business_status', '!=', 'rejected')->count();
        $totalB2C = User::where('account_type', 'b2c')->where('is_registered', '1')->count();
        $title = "Manage B2C users";

        return view('admin.users.index', compact(
            'users',
            'title',
            'roles',
            'totalUsers',
            'totalB2B',
            'totalB2C'
        ));
    }

    public function standarduserindex()
    {

        $users = User::with(['businessProfile', 'roles'])
            ->whereHas('subscriptions.membership', function ($q) {
                $q->where('name', 'Gold Plan');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);
        // dd($users);
        $roles = Role::all();

        // totals
        $totalUsers = User::count();
        $totalB2B = User::where('account_type', 'b2b')->where('is_registered', '1')->count();
        $totalB2C = User::where('account_type', 'b2c')->where('is_registered', '1')->count();

        $title =  __('admin.manage_gold_users');

        return view('admin.users.index', compact('users', 'title', 'roles', 'totalUsers', 'totalB2B', 'totalB2C'));
    }

    public function businessUserindex()
    {
        $users = User::with(['businessProfile', 'roles'])
            ->where('account_type', 'b2b')
            ->whereHas('businessProfile', function ($q) {
                $q->whereNotNull('company_name');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        $roles = Role::all();
        // dd($users);
        // totals
        $totalUsers = User::where('is_registered', '1')->count();
        $totalB2B = User::where('account_type', 'b2b')->where('is_registered', '1')->where('business_status', '!=', 'rejected')->count();
        $totalB2C = User::where('account_type', 'b2c')->where('is_registered', '1')->count();

        $title =  __('admin.manage_verified_users');

        return view('admin.users.index', compact('users', 'title', 'roles', 'totalUsers', 'totalB2B', 'totalB2C'));
    }

    /* -----------------------------
       Show User Details
    ----------------------------- */
    public function show($id)
    {
        $user = User::with('businessProfile')->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }
    /* -----------------------------
    Update status User Details
    ----------------------------- */
    public function updateStatus(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'business_status' => 'required|in:approved,rejected',
            'reason' => 'required_if:business_status,rejected|string|max:500'
        ]);

        $user = User::findOrFail($id);

        if ($user->account_type !== 'b2b') {
            return redirect()->back()->with('error', 'This user is not a business account');
        }
        /*
        -----------------------------
        APPROVE FLOW
        -----------------------------
        */

        if ($request->business_status === 'approved') {

            $user->update([
                'business_status' => 'approved'
            ]);

            return back()->with('success', 'Business account approved successfully.');
        }

        /*
        -----------------------------
        REJECT FLOW
        -----------------------------
        -----------------------------
        */

        if ($request->business_status === 'rejected') {

            // save rejected user
            RejectedUser::create([
                'email' => $user->email,
                'phone' => $user->phone,
                'reason' => $request->reason,
                'rejected_at' => now()
            ]);

            // delete business profile
            BusinessProfile::where('user_id', $user->id)->delete();

            // delete user
            $user->delete();

            return back()->with('success', 'User rejected and removed successfully.');
        }
    }

    /* -----------------------------
       Update B2B discount percentage
    ----------------------------- */
    public function updateDiscount(Request $request, $id)
    {
        $request->validate([
            'discount_percentage' => 'required|numeric|min:0|max:100'
        ]);

        $user = User::with('businessProfile')->findOrFail($id);

        if ($user->account_type !== 'b2b') {
            return back()->with('error', 'Discount can only be updated for B2B users.');
        }

        BusinessProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['discount_percentage' => $request->discount_percentage]
        );

        return back()->with('success', 'Discount percentage updated successfully.');
    }

    /* -----------------------------
     Rejected User Details
    ----------------------------- */
    public function rejectedUsers()
    {
        $rejectedUsers = RejectedUser::orderBy('id', 'desc')->paginate(10);
        // Total
        $totalRejected = RejectedUser::count();

        // This Week
        $thisWeekRejected = RejectedUser::whereBetween(
            'rejected_at',
            [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
        )->count();

        // This Month
        $thisMonthRejected = RejectedUser::whereMonth('rejected_at', Carbon::now()->month)
            ->whereYear('rejected_at', Carbon::now()->year)
            ->count();

        return view('admin.rejected_users.index', compact(
            'rejectedUsers',
            'totalRejected',
            'thisWeekRejected',
            'thisMonthRejected'
        ));
    }
    /* -----------------------------
    Show Rejected User Details
    ----------------------------- */
    public function rejectedUserShow($id)
    {
        $rejectedUser = RejectedUser::findOrFail($id);

        return view('admin.rejected_users.show', compact('rejectedUser'));
    }
    /* -----------------------------
    Assign User Role Details
    ----------------------------- */
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::findOrFail($request->user_id);

        // Update role_id in users table
        $user->role_id = $request->role_id;
        $user->save();

        // Update pivot table
        $user->roles()->sync([$request->role_id]);

        return back()->with('success', 'Role assigned successfully');
    }

    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Soft delete user (deleted_at set karega)
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User soft deleted successfully.');
    }
}
