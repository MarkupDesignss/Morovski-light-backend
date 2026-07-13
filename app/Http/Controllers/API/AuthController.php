<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BusinessProfile;
use App\Models\RejectedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\UserDelete;
use App\Models\NotificationSetting;
use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use JWTAuth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /* -----------------------------
    | SendOtp Function
    ----------------------------- */
    public function sendOtp(Request $request)
    {
        try {

            /*
            --------------------------------
            FIND EXISTING USER FIRST
            --------------------------------
            */
            $existingUser = User::where('email', $request->email)->first();

            /*
            --------------------------------
            VALIDATION
            --------------------------------
            */
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'account_type' => 'required|in:b2b,b2c',
                'full_name' => 'required|string|max:255',

                'phone' => [
                    'nullable',
                    'digits_between:10,15',
                    Rule::unique('users', 'phone')
                        ->ignore(optional($existingUser)->id)
                        ->where(function ($query) {
                            return $query->where('is_registered', 1);
                        }),
                ],

                'country' => 'nullable|string|max:255',
                'terms_condition' => 'required|in:0,1',

                'password' => [
                    'required',
                    'string',
                    Password::min(8)->mixedCase()->numbers()->symbols()
                ],

                // B2B fields
                'company_name' => 'required_if:account_type,b2b',
                'gst_number' => 'nullable|string|max:255',
                'billing_address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'pin_code' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            /*
            --------------------------------
            BLOCK IF ALREADY REGISTERED
            --------------------------------
            */
            if ($existingUser && $existingUser->is_registered == 1) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.email_already_registered')
                ], 422);
            }

            /*
            --------------------------------
            GENERATE OTP
            --------------------------------
            */
            $otp = rand(1000, 9999);

            /*
            --------------------------------
            CREATE OR UPDATE USER
            --------------------------------
            */
            $user = User::updateOrCreate(
                ['email' => $request->email],
                [
                    'full_name' => $request->full_name,
                    'phone' => $request->phone,
                    'country' => $request->country,
                    'account_type' => $request->account_type,
                    'terms_condition' => $request->terms_condition,
                    'password' => Hash::make($request->password),

                    'otp' => $otp,
                    'otp_expires_at' => now()->addMinutes(10),

                    'is_registered' => 0
                ]
            );

            /*
            --------------------------------
            STORE B2B DATA
            --------------------------------
            */
            if ($request->account_type === 'b2b') {
                BusinessProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $request->company_name,
                        'gst_number' => $request->gst_number,
                        'billing_address' => $request->billing_address,
                        'city' => $request->city,
                        'state' => $request->state,
                        'pin_code' => $request->pin_code,
                        'country' => $request->country,
                    ]
                );
            }

            /*
            --------------------------------
            SEND OTP MAIL
            --------------------------------
            */
            Mail::raw("Your OTP is: $otp", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('OTP Verification');
            });

            /*
            --------------------------------
            RESPONSE
            --------------------------------
            */
            return response()->json([
                'status' => true,
                'message' => __('messages.otp_sent'),
                'otp' => $otp
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /* -----------------------------
    | VerifyOtp Function
    ----------------------------- */

    public function verifyOtp(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'otp' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.user_not_found')
                ], 422);
            }

            if ($user->otp != $request->otp) {
                return response()->json([
                    'status' => false,
                    'message' => "Invalid Otp"
                ], 422);
            }

            if (now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.otp_expired')
                ], 422);
            }

            /*
        --------------------------------
        FINAL REGISTRATION HERE
        --------------------------------
        */

            $user->update([
                'email_verified_at' => now(),
                'otp' => null,
                'otp_expires_at' => null,
                'is_registered' => 1,
                'business_status' => $user->account_type === 'b2b' ? 'pending' : null
            ]);

            if ($user->account_type === 'b2c') {

                $admin = DB::table('admins')->first();

                if ($admin) {

                    DB::table('admin_notifications')->insert([
                        'admin_id'       => $admin->id,
                        'type'           => 'new_b2c_registration',
                        'title'          => 'New B2C Registration',
                        'message'        => "{$user->full_name} has registered successfully.",
                        'reference_type' => 'user',
                        'reference_id'   => $user->id,
                        'priority'       => 'high',
                        'extra_data'     => json_encode([
                            'customer_id'    => $user->id,
                            'customer_name'  => $user->full_name,
                            'customer_email' => $user->email,
                            'phone'          => $user->phone
                        ]),
                        'created_at'     => now(),
                        'updated_at'     => now()
                    ]);
                }
            }

            /*
        --------------------------------
        ASSIGN ROLE
        --------------------------------
        */

            $roleId = $user->account_type === 'b2b' ? 4 : 5;

            DB::table('role_users')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            return response()->json([
                'status' => true,
                'message' => "Account created successfully"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */

            $request->validate([
                'email'        => 'required|email',
                'password'     => 'required',
                'account_type' => 'required|in:customer,warehouse_manager,sales_executive',
            ]);

            /*
            |--------------------------------------------------------------------------
            | ACCOUNT TYPE MAPPING
            |--------------------------------------------------------------------------
            | customer => b2b OR b2c
            */

            $allowedAccountTypes = [];

            if ($request->account_type === 'customer') {

                $allowedAccountTypes = ['b2b', 'b2c'];
            } else {

                $allowedAccountTypes = [$request->account_type];
            }

            /*
            |--------------------------------------------------------------------------
            | USER FETCH
            |--------------------------------------------------------------------------
            */

            $user = User::with('roles')
                ->where('email', $request->email)
                ->whereIn('account_type', $allowedAccountTypes)
                ->first();

            /*
            |--------------------------------------------------------------------------
            | USER NOT FOUND
            |--------------------------------------------------------------------------
            */

            if (!$user) {

                $rejectedUser = RejectedUser::where('email', $request->email)->first();

                if ($rejectedUser) {

                    $daysPassed = Carbon::parse($rejectedUser->rejected_at)
                        ->diffInDays(now());

                    if ($daysPassed < 30) {

                        $remaining = 30 - $daysPassed;

                        return response()->json([
                            'status'  => false,
                            'message' => __('messages.business_rejected_admin_try_after', [
                                'days' => $remaining
                            ])
                        ], 422);
                    }

                    return response()->json([
                        'status'  => false,
                        'message' => __('messages.business_rejected_30_passed')
                    ], 422);
                }

                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid email or account type'
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | REGISTRATION CHECK
            |--------------------------------------------------------------------------
            */

            if ($user->is_registered == 0) {

                return response()->json([
                    'status'  => false,
                    'message' => __('messages.please_complete_registration_first')
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | ACTIVE CHECK
            |--------------------------------------------------------------------------
            */

            if ($user->is_active == 0) {

                return response()->json([
                    'status'  => false,
                    'message' => 'Your account is blocked by admin, Please contact admin to continue'
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | PASSWORD CHECK
            |--------------------------------------------------------------------------
            */

            if (!Hash::check($request->password, $user->password)) {

                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid password'
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | JWT TOKEN CREATE
            |--------------------------------------------------------------------------
            */

            $token = Auth::login($user);

            $refreshToken = Str::random(100);

            RefreshToken::create([
                'user_id'      => $user->id,
                'token'        => hash('sha256', $refreshToken),
                'expires_at'   => now()->addDays(7),
                'last_used_at' => now()
            ]);

            /*
            |--------------------------------------------------------------------------
            | BUSINESS APPROVAL CHECK
            |--------------------------------------------------------------------------
            */

            if (
                $user->account_type === 'b2b' &&
                $user->business_status === 'pending'
            ) {

                return response()->json([
                    'status'  => false,
                    'message' => __('messages.business_waiting_admin')
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | SUCCESS RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => true,
                'message' => "Login Successfully",
                'token' => $token,
                'expires_in' => 3600,
                'refresh_token' => $refreshToken,
                'user' => $user
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function refreshToken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required'
        ]);

        $hashedToken = hash('sha256', $request->refresh_token);

        $refreshToken = RefreshToken::where(
            'token',
            $hashedToken
        )->first();

        if (!$refreshToken) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid refresh token'
            ], 401);
        }

        if ($refreshToken->expires_at->isPast()) {

            $refreshToken->delete();

            return response()->json([
                'status' => false,
                'message' => 'Refresh token expired. Please login again.'
            ], 401);
        }

        $user = User::find($refreshToken->user_id);

        $newAccessToken = Auth::login($user);

        /*
     * Rotate refresh token
     */
        $newRefreshToken = Str::random(100);

        $refreshToken->update([
            'token' => hash('sha256', $newRefreshToken),
            'expires_at' => now()->addDays(7),
            'last_used_at' => now()
        ]);

        return response()->json([
            'status' => true,
            'access_token' => $newAccessToken,
            'expires_in' => 3600,
            'refresh_token' => $newRefreshToken
        ]);
    }

    /* -----------------------------
    | Login Function
    ----------------------------- */
    public function profile()
    {
        try {

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            /*
            --------------------------------
            LOAD RELATIONS
            --------------------------------
            */
            $user->load('roles', 'businessProfile','warehouse');

            /*
            --------------------------------
            DOCUMENT FULL URL
            --------------------------------
            */
            if (
                $user->businessProfile &&
                $user->businessProfile->document_path
            ) {
                $user->businessProfile->document_path =
                    url('/storage/' . $user->businessProfile->document_path);
            }

            /*
            --------------------------------
            GET CURRENT TOKEN
            --------------------------------
            */
            $token = Auth::getToken();

            return response()->json([
                'status' => true,
                'message' => "Profile Fetched Successfully",

                'token' => $token ? $token->get() : null,

                'user' => $user,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            /*
        --------------------------------
        PREVENT INVALID SWITCH
        --------------------------------
        */

            if ($user->account_type === 'b2b' && $request->account_type === 'b2c') {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.business_cannot_convert')
                ], 403);
            }

            /*
        --------------------------------
        DYNAMIC VALIDATION
        --------------------------------
        */

            $accountType = $request->account_type ?? $user->account_type;

            $rules = [
                'full_name' => 'nullable|string|max:255',

                'phone' => [
                    'nullable',
                    'string',
                    'min:10',
                    'max:15',
                ],

                'country' => 'nullable|string|max:255',

                'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png',

                'account_type' => 'nullable|in:b2c,b2b',
            ];

            /*
        --------------------------------
        PASSWORD VALIDATION (OPTIONAL)
        --------------------------------
        */

            if ($request->filled('password')) {
                $rules['password'] = [
                    'required',
                    'string',
                    Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                ];

                // Only for B2C → confirm required
                if ($accountType === 'b2c') {
                    $rules['password'][] = 'confirmed';
                }
            }

            /*
        --------------------------------
        B2B VALIDATION
        --------------------------------
        */

            if ($accountType === 'b2b') {
                $rules = array_merge($rules, [
                    'company_name' => 'nullable|string|max:255',
                    'gst_number' => 'nullable|string|max:100',
                    'billing_address' => 'nullable|string',
                    'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
                ]);
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            /*
        --------------------------------
        UPDATE USER DATA
        --------------------------------
        */

            $data = [
                'full_name' => $request->full_name ?? $user->full_name,
                'phone' => $request->phone ?? $user->phone,
                'country' => $request->country ?? $user->country,
            ];

            $logoutRequired = false;
            $businessProfile = null;

            // Switch b2c → b2b
            /*
                --------------------------------
                SWITCH b2c → b2b
                --------------------------------
                */

            if ($user->account_type === 'b2c' && $accountType === 'b2b') {

                $data['account_type'] = 'b2b';
                $data['business_status'] = 'pending';

                /*
                    --------------------------------
                    UPDATE ROLE
                    --------------------------------
                    */

                // get b2b role
                $b2bRole = Role::where('slug', 'b2b')->first();

                if ($b2bRole) {

                    // update role_id in users table
                    $data['role_id'] = $b2bRole->id;

                    // update role_users table
                    RoleUser::updateOrCreate(
                        ['user_id' => $user->id],
                        ['role_id' => $b2bRole->id]
                    );
                }

                $logoutRequired = true;
            }

            // Password update
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            /*
        --------------------------------
        PROFILE IMAGE
        --------------------------------
        */

            if ($request->hasFile('profile_picture')) {

                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $data['profile_picture'] = $request->file('profile_picture')
                    ->store('profile_pictures', 'public');
            }

            $user->update($data);

            /*
        --------------------------------
        BUSINESS PROFILE
        --------------------------------
        */

            if ($accountType === 'b2b') {

                $businessData = $request->only(
                    'company_name',
                    'gst_number',
                    'billing_address'
                );

                if ($request->hasFile('document')) {
                    $businessData['document_path'] = $request->file('document')
                        ->store('business_documents', 'public');
                }

                $businessProfile = BusinessProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    $businessData
                );
            }

            /*
        --------------------------------
        FORCE LOGOUT IF SWITCHED
        --------------------------------
        */

            if ($logoutRequired) {

                Auth::logout();

                return response()->json([
                    'status' => true,
                    'message' => __('messages.business_request_submitted'),
                    'logout' => true
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => __('messages.profile_updated_successfully'),
                'user' => $user->fresh(),
                'business_profile' => $businessProfile
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function removeProfilePicture()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            // Check if profile picture exists
            if (!$user->profile_picture) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.no_profile_picture_to_remove')
                ], 404);
            }

            // Delete from storage
            if (Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Remove from DB
            $user->update([
                'profile_picture' => null
            ]);

            return response()->json([
                'status' => true,
                'message' => __('messages.profile_picture_removed_successfully'),
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /* -----------------------------
    | Forgot password Function
    ----------------------------- */
    public function forgotPassword(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email'
                // 'email' => 'required|email|exists:users,email'
            ]);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'success' => true,
                    'message' => "Email not registered"
                ], 422);
            }
            $otp = rand(1000, 9999);

            if ($user->is_registered == 0) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.please_complete_registration_first')
                ], 422);
            }

            $user->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10)
            ]);

            Mail::raw("Your password reset OTP is: $otp", function ($message) use ($user) {
                $message->to($user->email)->subject('Password Reset OTP');
            });

            return response()->json([
                'status' => true,
                'message' => __('messages.OTP_sent_to_your_email')
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /* -----------------------------
    | Verify forgot password otp Function
    ----------------------------- */
    public function verifyResetOtp(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
                'otp' => 'required'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.user_not_found')
                ], 422);
            }

            if ($user->otp != $request->otp) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid OTP'
                ], 422);
            }

            if (Carbon::now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'OTP expired'
                ], 422);
            }

            return response()->json([
                'status' => true,
                'message' => __('messages.otp_verified')
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /* -----------------------------
    | Reset password Function
    ----------------------------- */
    // public function resetPassword(Request $request)
    // {
    //     try {

    //         $request->validate([
    //             'email' => 'required|email|exists:users,email',
    //             'password' => 'required|confirmed|min:6'
    //         ]);

    //         $user = User::where('email', $request->email)->first();

    //         $user->update([
    //             'password' => Hash::make($request->password),
    //             'otp' => null,
    //             'otp_expires_at' => null
    //         ],422);

    //         return response()->json([
    //             'status' => true,
    //             'message' => "Password reset successfully"
    //         ]);
    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }


    public function resetPassword(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                ]
            ]);

            $user = User::where('email', $request->email)->first();

            $user->update([
                'password' => Hash::make($request->password),
                'otp' => null,
                'otp_expires_at' => null
            ]);

            return response()->json([
                'status' => true,
                'message' => "Password reset successfully"
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /* -----------------------------
    | Delect account Function
    ----------------------------- */

    public function deleteAccount(Request $request)
    {
        try {

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'password' => 'required',
                'reason'   => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            /*
            ---------------------------------
            CHECK PASSWORD
            ---------------------------------
            */

            if (!Hash::check($request->password, $user->password)) {

                return response()->json([
                    'status' => false,
                    'message' => __('messages.Incorrect_password'),
                ]);
            }

            /*
            ---------------------------------
            GENERATE OTP
            ---------------------------------
            */

            $otp = rand(100000, 999999);

            /*
            ---------------------------------
            SAVE OTP IN USERS TABLE
            ---------------------------------
            */

            $user->update([
                'delete_otp' => $otp,
                'delete_otp_expires_at' => now()->addMinutes(10),

                // temporary reason
                'delete_reason' => $request->reason,
            ]);

            /*
            ---------------------------------
            SEND EMAIL
            ---------------------------------
            */

            Mail::raw(
                "Your account deletion OTP is: {$otp}",
                function ($message) use ($user) {

                    $message->to($user->email)
                        ->subject('Account Deletion OTP');
                }
            );

            return response()->json([
                'status' => true,
                'message' => 'OTP sent to your email.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyDeleteAccountOtp(Request $request)
    {
        try {

            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'otp' => 'required'
            ]);

            if ($validator->fails()) {

                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            /*
            ---------------------------------
            CHECK OTP
            ---------------------------------
            */

            if (
                $user->delete_otp != $request->otp ||
                now()->gt($user->delete_otp_expires_at)
            ) {

                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired OTP'
                ]);
            }

            /*
            ---------------------------------
            CREATE DELETE REQUEST
            ---------------------------------
            */

            UserDelete::create([

                'user_id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'reason' => $user->delete_reason,

                'status' => 'pending',
                'otp_verified' => 1,
            ]);

            /*
            ---------------------------------
            CLEAR OTP
            ---------------------------------
            */

            $user->update([
                'delete_otp' => null,
                'delete_otp_expires_at' => null,
                'delete_reason' => null,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Account deletion request sent to admin.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function allUsers()
    {
        $users = \App\Models\User::select('id', 'full_name as name')->get();

        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    }

    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => ['required', 'string'],
                'new_password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                ],
            ]);

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.unauthorized')
                ], 401);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.current_password_is_incorrect'),
                ], 400);
            }

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' =>  "Please include an uppercase letter, a lowercase letter, a number, and a symbol.",
                    'errors' => $validator->errors()
                ], 422);
            }

            if (Hash::check($request->new_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.cant_be_same_as_current'),
                ], 400);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'status' => true,
                'message' => __('messages.password_changed_successfully'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('changePassword error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => false,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletter_subscribers,email'
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already subscribed.'
        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            NewsletterSubscriber::create([
                'email' => $request->email
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Subscribed successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function states()
    {
        $path = storage_path('app/states.json');

        $states = json_decode(file_get_contents($path), true);

        return response()->json([
            'success' => true,
            'message' => 'States fetched successfully',
            'data' => $states
        ]);
    }
}
