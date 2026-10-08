<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PasswordResetController extends Controller
{
    /**
     * Display the forgot password page.
     */
    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Send password reset link to user email.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We could not find an account registered with that email address.',
        ]);

        $email = $request->email;
        $user = User::where('email', $email)->first();

        // Generate cryptographically secure reset token
        $token = Str::random(64);

        // Store or update in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $email], false));

        // Attempt sending email
        try {
            Mail::send([], [], function ($message) use ($user, $resetUrl) {
                $message->to($user->email)
                    ->subject('Reset Your TradeCo Password')
                    ->html("
                        <div style='background-color:#0b0e11; color:#eaecef; padding:32px; font-family:sans-serif;'>
                            <div style='max-width:480px; margin:0 auto; background-color:#1e2329; border-radius:12px; padding:24px; border:1px solid #2b3139;'>
                                <h2 style='color:#f0b90b; margin-top:0;'>TradeCo Password Reset</h2>
                                <p style='font-size:14px; color:#848e9c;'>Hello {$user->name},</p>
                                <p style='font-size:14px; line-height:1.6;'>You requested to reset your password. Click the button below to choose a new password. This link will expire in 60 minutes.</p>
                                <div style='text-align:center; margin:28px 0;'>
                                    <a href='{$resetUrl}' style='background-color:#f0b90b; color:#1e2329; text-decoration:none; padding:12px 24px; border-radius:8px; font-weight:bold; font-size:14px; display:inline-block;'>Reset Password</a>
                                </div>
                                <p style='font-size:12px; color:#5e6673;'>If you did not request a password reset, no further action is required.</p>
                                <hr style='border:none; border-top:1px solid #2b3139; margin:20px 0;'>
                                <p style='font-size:11px; color:#5e6673; word-break:break-all;'>Button not working? Copy and paste this link in your browser:<br><a href='{$resetUrl}' style='color:#f0b90b;'>{$resetUrl}</a></p>
                            </div>
                        </div>
                    ");
            });

            return response()->json([
                'message' => 'Password reset link sent to your email. Please check your inbox and spam folder.',
            ]);
        } catch (Exception $e) {
            Log::warning('Password reset email sending failed: ' . $e->getMessage());

            // If running on local or mail transport issue, return clean message with fallback for development
            return response()->json([
                'message' => 'Password reset request generated. If email delivery is delayed, you can use the direct link below.',
                'dev_reset_url' => app()->environment('local') ? $resetUrl : null,
            ]);
        }
    }

    /**
     * Display the reset password form.
     */
    public function showResetPassword(Request $request, string $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Process password reset.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired password reset token.'], 422);
        }

        // Verify token expiry (60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'This password reset token has expired. Please request a new one.'], 422);
        }

        // Verify token match
        if (!Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Invalid password reset token.'], 422);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ]);

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Your password has been successfully reset! You can now log in.',
        ]);
    }
}
