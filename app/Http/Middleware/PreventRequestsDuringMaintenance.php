<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Support\Facades\Auth;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [
        'admin/*',
        'login',
        'logout',
    ];
    
    /**
     * Determine if the request has a URI that should be accessible in maintenance mode.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        // Allow admin routes - admin can access all admin routes during maintenance
        $path = $request->path();
        $uri = $request->getRequestUri();
        
        if (str_starts_with($path, 'admin/') || str_starts_with($uri, '/admin/')) {
            // If session is loaded, check if user is admin
            try {
                if (Auth::check()) {
                    $user = Auth::user();
                    // Ensure role is loaded
                    if (!$user->relationLoaded('role')) {
                        $user->load('role');
                    }
                    if ($user->isAdmin()) {
                        return true;
                    }
                }
            } catch (\Exception $e) {
                // If Auth check fails, still allow admin routes
                // Let CheckMaintenanceMode handle the actual admin check
                return true;
            }
            // Always allow admin routes - CheckMaintenanceMode will handle admin check
            return true;
        }
        
        // Allow login route (GET and POST) so admin can login during maintenance
        if ($request->is('login') || $request->routeIs('login')) {
            return true;
        }
        
        // Allow logout route
        if ($request->is('logout') || $request->routeIs('logout')) {
            return true;
        }
        
        // Allow debug routes for testing
        if (str_starts_with($path, 'admin/debug') || str_starts_with($uri, '/admin/debug')) {
            return true;
        }
        
        return parent::inExceptArray($request);
    }
}

