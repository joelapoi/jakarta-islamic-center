<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Detect if this is an API request
        $isApiRequest = $request->expectsJson() || $request->is('api/*');

        // Check if user is authenticated
        if (!Auth::check()) {
            if ($isApiRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login first.'
                ], 401);
            } else {
                return redirect()->route('login')
                    ->with('error', 'Anda harus login terlebih dahulu');
            }
        }

        $user = Auth::user();

        // Check if user account is active
        if (!$user->is_active) {
            if ($isApiRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact administrator.'
                ], 403);
            } else {
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
            }
        }

        // If no specific roles required, just check authentication
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            if ($isApiRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You do not have permission to access this resource.',
                    'required_roles' => $roles,
                    'your_roles' => $user->getRoleNames()
                ], 403);
            } else {
                abort(403, 'Anda tidak memiliki akses ke halaman ini. Role yang dibutuhkan: ' . implode(', ', $roles));
            }
        }

        return $next($request);
    }
}