<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // **Blacklist Gate:** If status is blacklisted, kill sessions and block.
        if ($user->status === 'blacklisted') {
            // Invalidate all user tokens (force logout everywhere)
            $user->tokens()->delete();
            
            // Clear sessions from DB (if using database driver)
            // This is a hard block.
            return response()->json([
                'message' => 'Account is blacklisted. Contact administrator.',
            ], 403);
        }

        // **Role Check:** If specific roles are required, check them.
        if (!empty($roles) && !in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Forbidden. Insufficient permissions.',
            ], 403);
        }

        return $next($request);
    }
}