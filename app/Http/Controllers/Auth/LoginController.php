<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\UserAvailability;
use App\Models\TelecallerProfile;
use App\Models\SystemSettings;
use App\Services\UserAvailabilityService;
use App\Services\UserStatusService;
use Kreait\Firebase\Factory;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // If user is already authenticated, redirect to dashboard
        if (Auth::check()) {
            $user = Auth::user();
            $redirectUrl = $this->getRedirectUrlForRole($user);
            $redirect = redirect($redirectUrl);
            $redirect->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0, private');
            $redirect->headers->set('Pragma', 'no-cache');
            $redirect->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            return $redirect;
        }
        
        $response = response()->view('auth.login');
        
        // Add no-cache headers
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        
        return $response;
    }

    public function login(Request $request)
    {
        // Regenerate CSRF token to prevent 419 errors
        $request->session()->regenerateToken();
        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->only('email'));
        }
        
        // Check maintenance mode - only allow admin to login
        if (SystemSettings::isMaintenanceMode()) {
            if (!$user->isAdmin()) {
                return back()->withErrors([
                    'email' => SystemSettings::get('maintenance_message', 'System is under maintenance. Only admin can login during maintenance mode.'),
                ])->withInput($request->only('email'))->with('maintenance_mode', true);
            }
        }

        // Ensure role relationship is loaded before login
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        // Login user first
        Auth::login($user, $request->filled('remember'));
        
        // Regenerate session immediately after login (before storing data)
        $request->session()->regenerate();

        // Mark user as present (all users, not just telecallers)
        $this->markUserAsPresent($user);

        // Mark attendance for telecallers (Telecaller and Sales Executive)
        if ($user->isTelecaller()) {
            $this->markTelecallerAttendance($user);
            // Password intentionally NOT stored in session (security: users type current password themselves)
        }

        // Generate ONE API token for ALL roles at login and store in session
        // This prevents createToken() from being called on every page load
        $token = $user->createToken('web-session-token')->plainTextToken;
        $request->session()->put('api_token', $token);
        // Keep role-specific keys as aliases for backward compatibility
        if ($user->isTelecaller()) {
            $request->session()->put('telecaller_api_token', $token);
        }
        if ($user->isSalesExecutive()) {
            $request->session()->put('sales_executive_api_token', $token);
        }
        
        // Commit session to ensure all data is saved
        $request->session()->save();

        // Ensure user is authenticated before redirect
        if (!Auth::check()) {
            Log::error('User not authenticated after login', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            return back()->withErrors([
                'email' => 'Authentication failed. Please try again.',
            ])->withInput($request->only('email'));
        }

        // Redirect based on user role
        try {
            $redirectUrl = $this->getRedirectUrlForRole($user);
            $redirect = redirect($redirectUrl);
            
            // Add no-cache headers to redirect response
            $redirect->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0, private');
            $redirect->headers->set('Pragma', 'no-cache');
            $redirect->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            $redirect->headers->set('Location', $redirectUrl);
            
            // Force redirect with 302 status to prevent caching
            $redirect->setStatusCode(302);
            
            // Ensure session is committed before redirect
            $request->session()->save();
            
            Log::info('Login successful, redirecting', [
                'user_id' => $user->id,
                'email' => $user->email,
                'redirect_url' => $redirectUrl,
                'is_authenticated' => Auth::check(),
                'session_id' => $request->session()->getId(),
            ]);
            
            return $redirect;
        } catch (\Exception $e) {
            Log::error('Login redirect error', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Fallback redirect
            return redirect()->route('sales-manager.dashboard');
        }
    }

    public function loginWithFirebase(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
            $auth = $factory->createAuth();
            $verifiedToken = $auth->verifyIdToken($request->id_token);
            $firebaseClaims = $verifiedToken->claims();
            $email = $firebaseClaims->get('email');

            if (!$email) {
                return back()->withErrors(['email' => 'Google account does not have an email address.']);
            }

            $user = User::where('email', $email)->where('is_active', true)->first();
            if (!$user) {
                return back()->withErrors(['email' => 'No CRM account found for this email. Contact your admin.']);
            }

            if (SystemSettings::isMaintenanceMode() && !$user->isAdmin()) {
                return back()->withErrors([
                    'email' => SystemSettings::get('maintenance_message', 'System is under maintenance. Only admin can login.'),
                ]);
            }

            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }

            Auth::login($user, true);
            $request->session()->regenerate();
            $this->markUserAsPresent($user);

            // Generate ONE token for all roles at Firebase login
            $token = $user->createToken('web-session-token')->plainTextToken;
            $request->session()->put('api_token', $token);
            if ($user->isTelecaller()) {
                $request->session()->put('telecaller_api_token', $token);
            }
            if ($user->isSalesExecutive()) {
                $request->session()->put('sales_executive_api_token', $token);
            }

            $request->session()->save();

            $redirectUrl = $this->getRedirectUrlForRole($user);
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            Log::error('Firebase login failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['email' => 'Google Sign-In failed. Please try again.']);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Revoke all web session tokens from DB to prevent token accumulation
            if (Auth::check()) {
                Auth::user()->tokens()->where('name', 'web-session-token')->delete();
            }

            // Clear all API tokens and any legacy password from session
            $request->session()->forget(['api_token', 'telecaller_api_token', 'sales_executive_api_token', 'user_password_for_change']);

            // Logout user
            Auth::logout();

            // Invalidate and regenerate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Exception $e) {
            // Even if there's an error (like expired session), try to logout
            Auth::logout();
        }

        // Always redirect to login page with no-cache headers
        $response = redirect()->route('login')->with('success', 'You have been logged out successfully.');
        
        // Add no-cache headers to prevent browser caching
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        
        return $response;
    }


    private function redirectBasedOnRole($user)
    {
        $url = $this->getRedirectUrlForRole($user);
        return redirect($url);
    }
    
    private function getRedirectUrlForRole($user)
    {
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        $role = $user->role->slug ?? '';
        
        Log::info('Login redirect', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role_slug' => $role,
            'is_sales_head' => $user->isSalesHead(),
        ]);

        // Determine redirect URL based on role
        $redirectUrl = match($role) {
            'telecaller' => route('telecaller.dashboard'),
            'sales_executive' => route('sales-executive.dashboard'),
            'sales_manager' => $user->isSalesHead() 
                ? route('sales-head.dashboard')
                : route('sales-manager.dashboard'),
            'senior_manager' => route('sales-manager.dashboard'),  // Manager role → Sales Manager dashboard
            'assistant_sales_manager' => route('sales-manager.dashboard'),
            'admin' => route('admin.dashboard'),
            'crm' => route('dashboard'),
            default => '/',
        };
        
        Log::info('Login redirect target', [
            'redirect_url' => $redirectUrl,
        ]);
        
        return $redirectUrl;
    }

    /**
     * Mark user as present on login (all users)
     */
    private function markUserAsPresent(User $user): void
    {
        $userStatusService = app(UserStatusService::class);
        $userStatusService->markAsPresent($user->id);
    }

    /**
     * Mark telecaller attendance on login
     */
    private function markTelecallerAttendance(User $user): void
    {
        // Update UserAvailability - mark as online
        $availability = UserAvailability::firstOrCreate(
            ['user_id' => $user->id],
            [
                'is_online' => false,
                'timezone' => 'Asia/Kolkata',
                'current_day_leads' => 0,
                'is_available' => false,
            ]
        );
        
        $availability->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);
        $availability->updateAvailability();

        // Update TelecallerProfile - mark as present (not absent)
        $profile = TelecallerProfile::firstOrCreate(['user_id' => $user->id]);
        if ($profile->is_absent) {
            $profile->update([
                'is_absent' => false,
                'absent_reason' => null,
                'absent_until' => null,
            ]);
        }
    }
}

