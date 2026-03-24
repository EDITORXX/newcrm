<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    // Step 1: Show "Forgot Password" form (email input)
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // Step 2: Send OTP to email
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No account found with this email address.',
        ]);

        $user = User::where('email', $request->email)->first();

        // Delete any old OTPs for this email
        DB::table('password_reset_otps')->where('email', $request->email)->delete();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);

        DB::table('password_reset_otps')->insert([
            'email'       => $request->email,
            'otp'         => $otp,
            'token'       => $token,
            'is_verified' => false,
            'expires_at'  => Carbon::now()->addMinutes(10),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        // Send OTP email
        Mail::to($request->email)->send(new PasswordResetOtpMail($otp, $user->name));

        return redirect()->route('password.otp.form', ['email' => $request->email])
            ->with('success', 'OTP sent to your email. Please check your inbox.');
    }

    // Step 3: Show OTP verification form
    public function showOtpForm(Request $request)
    {
        $email = $request->query('email');
        if (!$email) {
            return redirect()->route('password.forgot');
        }
        return view('auth.verify-otp', compact('email'));
    }

    // Step 4: Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        $record = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('is_verified', false)
            ->first();

        if (!$record) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.'])->withInput();
        }

        if (Carbon::now()->greaterThan($record->expires_at)) {
            DB::table('password_reset_otps')->where('id', $record->id)->delete();
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.'])->withInput();
        }

        // Mark as verified
        DB::table('password_reset_otps')
            ->where('id', $record->id)
            ->update(['is_verified' => true, 'updated_at' => Carbon::now()]);

        return redirect()->route('password.reset.form', ['token' => $record->token]);
    }

    // Step 5: Show new password form
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            return redirect()->route('password.forgot');
        }

        $record = DB::table('password_reset_otps')
            ->where('token', $token)
            ->where('is_verified', true)
            ->first();

        if (!$record || Carbon::now()->greaterThan($record->expires_at)) {
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'Session expired. Please start again.']);
        }

        return view('auth.reset-password', compact('token'));
    }

    // Step 6: Save new password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $record = DB::table('password_reset_otps')
            ->where('token', $request->token)
            ->where('is_verified', true)
            ->first();

        if (!$record || Carbon::now()->greaterThan($record->expires_at)) {
            return redirect()->route('password.forgot')
                ->withErrors(['email' => 'Session expired. Please start again.']);
        }

        // Update user password
        User::where('email', $record->email)->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete the OTP record
        DB::table('password_reset_otps')->where('id', $record->id)->delete();

        return redirect()->route('login')
            ->with('success', 'Password reset successfully! Please log in with your new password.');
    }

    // Resend OTP
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        DB::table('password_reset_otps')->where('email', $request->email)->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);

        DB::table('password_reset_otps')->insert([
            'email'       => $request->email,
            'otp'         => $otp,
            'token'       => $token,
            'is_verified' => false,
            'expires_at'  => Carbon::now()->addMinutes(10),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        Mail::to($request->email)->send(new PasswordResetOtpMail($otp, $user->name));

        return back()->with('success', 'New OTP sent to your email.');
    }
}
