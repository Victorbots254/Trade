<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdminOrModerator
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || (!$user->is_admin && !$user->is_moderator)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Admin or Moderator access required.'], 403);
            }
            return redirect('/terminal')->with('error', 'Unauthorized. Admin or Moderator access required.');
        }

        return $next($request);
    }
}
