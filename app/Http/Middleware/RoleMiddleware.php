<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Roles can be passed like: role:ADMIN or role:ADMIN,OPERATOR
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $userRoleValue = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;

        // Check against provided roles
        $allowed = array_map(fn($r) => strtoupper(trim($r)), $roles);

        if (!in_array(strtoupper($userRoleValue), $allowed, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. You do not have the required permissions for this action.',
                    'required_roles' => $allowed,
                    'your_role' => $userRoleValue,
                ], 403);
            }
            abort(403, 'Unauthorized action: Access denied for your user role.');
        }

        return $next($request);
    }
}
