<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SystemSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if system_settings table exists, if not, allow all requests
        try {
            // Quick check if table exists
            DB::table('system_settings')->limit(1)->get();
        } catch (\Exception $e) {
            // Table doesn't exist yet, allow all requests
            return $next($request);
        }
        
        // Check if maintenance mode is enabled
        if (SystemSettings::isMaintenanceMode()) {
            // Always allow login and logout routes (both GET and POST)
            // Admin can login, non-admin will be blocked in LoginController
            $path = $request->path();
            $uri = $request->getRequestUri();
            
            // Check for login routes
            if ($request->is('login') || $request->routeIs('login') || str_starts_with($path, 'login')) {
                return $next($request);
            }
            
            // Check for logout routes
            if ($request->is('logout') || $request->routeIs('logout') || str_starts_with($path, 'logout')) {
                return $next($request);
            }
            
            // Allow debug routes for testing
            if (str_starts_with($path, 'admin/debug') || str_starts_with($uri, '/admin/debug')) {
                return $next($request);
            }
            
            // If user is logged in, check if they are admin
            if (Auth::check()) {
                $user = Auth::user();
                
                // Ensure role relationship is loaded
                if (!$user->relationLoaded('role')) {
                    $user->load('role');
                }
                
                // Allow admin to access all routes
                if ($user->isAdmin()) {
                    return $next($request);
                }
                
                // Auto-logout non-admin users
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redirect to login with maintenance message
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'maintenance_mode',
                        'message' => SystemSettings::get('maintenance_message', 'System is under maintenance. You have been logged out.'),
                        'redirect' => route('login')
                    ], 503);
                }
                
                return redirect()->route('login')->withErrors([
                    'email' => SystemSettings::get('maintenance_message', 'System is under maintenance. You have been logged out. Only admin can access the system during maintenance.')
                ]);
            }
            
            // Non-logged in users trying to access any route (except login/logout)
            // Redirect them to login page with maintenance message
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'maintenance_mode',
                    'message' => SystemSettings::get('maintenance_message', 'System is under maintenance. Only admin can access the system.'),
                    'redirect' => route('login')
                ], 503);
            }
            
            return redirect()->route('login')->withErrors([
                'email' => SystemSettings::get('maintenance_message', 'System is under maintenance. Only admin can access the system.')
            ]);
        }
        
        return $next($request);
    }
}
