<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = auth()->user();

        // Admin has all permissions
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check role-based permissions
        $hasPermission = match($permission) {
            'view_all_leads' => $user->canViewAllLeads(),
            'assign_leads' => $user->canAssignLeads(),
            'manage_users' => $user->canManageUsers(),
            'manage_site_visits' => !$user->isSalesExecutive(),
            default => false,
        };

        if (!$hasPermission) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Insufficient permissions.'], 403);
            }
            abort(403, 'Forbidden. Insufficient permissions.');
        }

        return $next($request);
    }
}

