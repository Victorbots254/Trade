<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Admin access only.'], 403);
            }
            if ($user && $user->is_moderator) {
                return redirect('/admin/p2p')->with('error', 'This section is restricted to Master Administrators.');
            }
            return redirect('/terminal')->with('error', 'Unauthorized. Admin access only.');
        }

        return $next($request);
    }
}
