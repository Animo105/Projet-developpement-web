<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EndImpersonationOnUserRoute
{
    /**
     * Handle an incoming request.
     *
     * This middleware automatically ends impersonation when accessing user-only routes.
     * It restores the original user session.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $impersonating = $request->session()->get('impersonating');

        if ($impersonating && Auth::guard('character')->check()) {
            Auth::guard('character')->logout();
            Auth::guard('web')->loginUsingId($impersonating['user_id']);

            $request->session()->forget('impersonating');
            $request->session()->flash('success', 'Impersonation terminée automatiquement');
        }

        return $next($request);
    }
}
