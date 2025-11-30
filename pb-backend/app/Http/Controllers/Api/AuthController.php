<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\WelcomeMail;
use App\Mail\VerifyEmail;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Generate verification token
        $verificationToken = Str::random(64);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guest',
            'status' => 'active',
            'email_verification_token' => $verificationToken,
            'email_verified_at' => null,
        ]);

        // Log the registration
        ActivityLogService::logCreate('User', $user->id, $user->name, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        // Send verification email
        try {
            Mail::to($user->email)->send(new VerifyEmail($user, $verificationToken));
        } catch (\Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Registration successful. Please check your email to verify your account.',
            'user' => $user,
        ], 201);
    }

    /**
     * Verify email address
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = User::where('email_verification_token', $request->token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid verification token.'
            ], 400);
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);

        // Log the verification
        ActivityLogService::log('verify_email', 'Email verified', 'User', $user->id);

        // Send welcome email after verification
        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Email verified successfully. You can now login.'
        ]);
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified.'
            ], 400);
        }

        // Generate new verification token
        $verificationToken = Str::random(64);
        $user->update(['email_verification_token' => $verificationToken]);

        try {
            Mail::to($user->email)->send(new VerifyEmail($user, $verificationToken));
        } catch (\Exception $e) {
            Log::error('Failed to resend verification email: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send verification email.'
            ], 500);
        }

        return response()->json([
            'message' => 'Verification email sent successfully.'
        ]);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if email is verified (skip for admin)
        if ($user->role !== 'admin' && !$user->email_verified_at) {
            return response()->json([
                'message' => 'Please verify your email address before logging in.',
                'needs_verification' => true
            ], 403);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact support.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Log the login
        ActivityLogService::logLogin($user->id);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $userId = $request->user()->id;
        
        // Log the logout before deleting token
        ActivityLogService::logLogout($userId);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
