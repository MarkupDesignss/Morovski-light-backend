<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminResetOtpMail;
use App\Models\Admin;
use Illuminate\Http\Request;
use App\Models\AdminPasswordOtp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\BusinessProfile;
use App\Models\RejectedUser;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        // ⃣ Validate input
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'remember' => 'nullable|boolean', // optional remember me
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // ⃣ Attempt login with admin guard
        if (Auth::guard('admin')->attempt($credentials, $remember)) {

            // Regenerate session to prevent session fixation
            $request->session()->regenerate();

            //  Redirect to admin dashboard
            return redirect()->route('admin.dashboard');
        }

        // Optional: check if admin exists for better error
        $admin = \App\Models\Admin::where('email', $request->email)->first();

        if (!$admin) {
             return back()->withErrors(['email' => __('admin.admin_not_found')]);
        }

        if (!Hash::check($request->password, $admin->password)) {
             return back()->withErrors(['password' => __('admin.incorrect_password')]);
        }

        // fallback
        return back()->withErrors(['email' => __('admin.invalid_credentials')]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerate();

        return redirect()->route('admin.loginn');
    }

    public function forgotPasswordForm()
    {
        return view('admin.auth.forgot-password');
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return back()->withErrors(['email' => 'Admin not found']);
        }

        $otp = random_int(100000, 999999);

        AdminPasswordOtp::updateOrCreate(
            ['email' => $request->email],
            [
                // 'otp' => Hash::make($otp),
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );


        Mail::to($request->email)->send(new AdminResetOtpMail($otp));

        return redirect()->route('admin.otp.form')
            ->with('email', $request->email)
            ->with('success', __('admin.otp_sent'));
    }

    public function otpForm()
    {
        return view('admin.auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        $record = AdminPasswordOtp::where('email', $request->email)->first();

        if (!$record || Carbon::now()->gt($record->expires_at)) {
             return back()->withErrors(['otp' => __('admin.otp_expired')]);
        }

        if ($request->otp != $record->otp) {
             return back()->withErrors(['otp' => __('admin.incorrect_otp')]);
        }
        // if (!Hash::check($request->otp, $record->otp)) {
        //     return back()->withErrors(['otp' => 'Incorrect OTP']);
        // }

        // tore verified email in session
        session(['admin_reset_email' => $request->email]);

        return redirect()->route('admin.reset.form');
    }


    /* ==========================
       RESET PASSWORD
    ========================== */

    public function resetPasswordForm()
    {
        return view('admin.auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $email = session('admin_reset_email');

        if (!$email) {
            return redirect()->route('admin.loginn')
                 ->withErrors(['error' => __('admin.session_expired')]);
        }

        Admin::where('email', $email)->update([
            'password' => Hash::make($request->password),
        ]);

        AdminPasswordOtp::where('email', $email)->delete();

        // Clear session
        session()->forget('admin_reset_email');

        return redirect()->route('admin.loginn')
            ->with('success', __('admin.password_reset_success'));
    }

    // public function update(Request $request)
    // {
    //     $admin = Auth::guard('admin')->user();

    //     $request->validate([
    //         'name'     => 'required|string|max:150',
    //         'email'    => 'required|email|unique:admins,email,' . $admin->id,
    //         'password' => 'nullable|min:6',
    //     ]);

    //     $admin->name  = $request->name;
    //     $admin->email = $request->email;

    //     // his is the critical line
    //     if ($request->filled('password')) {
    //         $admin->password = Hash::make($request->password);
    //     }

    //     $admin->save();

    //     return back()->with('success', __('admin.profile_updated'));
    // }
    
      public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'name'            => 'required|string|max:150',
            'email'           => 'required|email|unique:admins,email,' . $admin->id,
            'password'        => 'nullable|min:6',

            'account_name'    => 'nullable|string|max:255',
            'bank_name'       => 'nullable|string|max:255',
            'account_number'  => 'nullable|string|max:100',
            'ifsc_code'       => 'nullable|string|max:50',
            'branch_name'     => 'nullable|string|max:255',
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;

        $admin->account_name = $request->account_name;
        $admin->bank_name = $request->bank_name;
        $admin->account_number = $request->account_number;
        $admin->ifsc_code = strtoupper($request->ifsc_code);
        $admin->branch_name = $request->branch_name;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return back()->with('success', __('admin.profile_updated'));
    }
}
