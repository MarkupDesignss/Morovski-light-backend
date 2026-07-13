<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\StaffAccountCreatedMail;


class StaffController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $accountType = $request->account_type; // expected values: 'sales_executive' or 'warehouse_manager'

        $users = User::whereHas('roles', function ($q) {
            $q->whereIn('role_id', [2, 3]);
        })
            ->when($accountType, function ($query, $accountType) {
                $query->where('account_type', $accountType);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                });
            })
            ->with('roles')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.staff.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::whereIn('id', [2, 3])->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('admin.staff.create', compact('roles', 'warehouses'));
    }


    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'User status updated');
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|digits_between:10,15',
            'password' => 'required|min:6',
            'role_id' => 'required|in:2,3',
            'warehouse_id' => $request->role_id == 2 ? 'required|exists:warehouses,id' : 'nullable|exists:warehouses,id',
        ]);

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | ROLE MAPPING
            |--------------------------------------------------------------------------
            */
            $roleSlug = match ((int) $request->role_id) {
                2 => 'warehouse_manager',
                3 => 'sales_executive',
                default => null
            };

            // plain password for mail
            $plainPassword = $request->password;

            $user = User::create([
                'full_name'     => $request->full_name,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'warehouse_id' => $request->warehouse_id,
                'password'      => Hash::make($request->password),
                'is_registered' => 1,
                'role'          => $roleSlug,
                'account_type'  => $roleSlug,
            ]);

            /*
            |--------------------------------------------------------------------------
            | ATTACH ROLE
            |--------------------------------------------------------------------------
            */
            $user->roles()->attach($request->role_id);

            /*
            |--------------------------------------------------------------------------
            | SEND EMAIL DIRECTLY
            |--------------------------------------------------------------------------
            */
            // account type label
            $accountType = match ($user->account_type) {
                'sales_executive'   => 'Sales Executive',
                'warehouse_manager' => 'Warehouse Manager',
                default             => ucfirst(str_replace('_', ' ', $user->account_type)),
            };

            Mail::to($user->email)->send(
                new StaffAccountCreatedMail(
                    $user,
                    $plainPassword,
                    $accountType
                )
            );

            DB::commit();

            return redirect()
                ->route('admin.staff.index')
                ->with('success', 'User created successfully');
        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = User::with(['roles', 'warehouse'])->findOrFail($id);
        return view('admin.staff.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        if ($user->is_active == 0) {
            return redirect()->route('admin.staff.index')->with('error', "Unblock the user first");
        }
        $roles = Role::whereIn('id', [2, 3])->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('admin.staff.edit', compact('user', 'roles', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required',
            'email' => "required|email|unique:users,email,$id",
            'phone' => 'required|digits_between:10,15',
            'role_id' => 'nullable|in:2,3',
            'warehouse_id' => $request->role_id == 2 ? 'required|exists:warehouses,id' : 'nullable|exists:warehouses,id',
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($id);

            $user->update([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'warehouse_id' => $request->warehouse_id,
                'is_registered' => 1
            ]);

            if ($request->password) {
                $user->update([
                    'password' => Hash::make($request->password)
                ]);
            }

            $user->roles()->sync([$request->role_id]);

            DB::commit();

            return redirect()->route('admin.staff.index')->with('success', 'User updated');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->roles()->detach();
        $user->delete();

        return back()->with('success', 'User deleted');
    }
}
