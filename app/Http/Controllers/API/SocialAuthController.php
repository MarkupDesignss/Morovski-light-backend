<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\BusinessProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;


class SocialAuthController extends Controller
{
    public function googleRedirect()
    {
        return Socialite::driver('google')
            ->stateless()
            ->with(['prompt' => 'select_account']) 
            ->redirect();
    }
    
    public function googleCallback()
    {
        try {
            $socialUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            // Redirect to frontend with error
            return redirect(config('app.frontend_url') . '/auth/callback?error=' . urlencode($e->getMessage()));
        }
    
        // Find or create user (same as your code)
        $user = User::where('email', $socialUser->getEmail())->first();
        if (!$user) {
            $user = User::create([
                'full_name' => $socialUser->getName() ?? 'User',
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(16)),
                'provider' => 'google',
                'provider_id' => $socialUser->getId(),
                'account_type' => 'b2c',               ]);
        }
    
        // Generate token
        $token = JWTAuth::fromUser($user);
    
        // Build redirect URL to frontend
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $redirectUrl = $frontendUrl . '/auth/callback?token=' . $token . '&user=' . urlencode(json_encode($user));
    
        // If business profile missing, add a flag
        if ($user->account_type === 'business' && !BusinessProfile::where('user_id', $user->id)->exists()) {
            $redirectUrl .= '&require_business_profile=1';
        }
    
        return redirect($redirectUrl);
    }

    public function updateProfile(Request $request)
    {
        try {

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'full_name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|min:10|max:15',
                'country' => 'nullable|string',

                // Profile Picture
                'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

                // NEW
                'account_type' => 'required|in:individual,business',

                // Business fields
                'company_name' => 'required_if:account_type,business|string|max:255',
                'vat_number' => 'nullable|string|max:100',
                'business_address' => 'required_if:account_type,business|string',
                'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            /*
        -----------------------------
        UPDATE USER PROFILE
        -----------------------------
        */

            $data = [
                'full_name' => $request->full_name ?? $user->full_name,
                'phone' => $request->phone,
                'country' => $request->country,
                'account_type' => $request->account_type
            ];

            // Handle profile picture
            if ($request->hasFile('profile_picture')) {

                // Delete old image
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $path = $request->file('profile_picture')
                    ->store('profile_pictures', 'public');

                $data['profile_picture'] = $path;
            }

            $user->update($data);

            $businessProfile = null;

            /*
        -----------------------------
        HANDLE BUSINESS PROFILE
        -----------------------------
        */

            if ($request->account_type === 'business') {

                $businessData = $request->only('company_name', 'vat_number', 'business_address');

                if ($request->hasFile('document')) {
                    $path = $request->file('document')
                        ->store('business_documents', 'public');

                    $businessData['document_path'] = $path;
                }

                // Create or update
                $businessProfile = BusinessProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    $businessData
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    ...$user->toArray(),
                    'profile_picture_url' => $user->profile_picture
                        ? asset('storage/' . $user->profile_picture)
                        : null,
                ],
                'business_profile' => $businessProfile
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}