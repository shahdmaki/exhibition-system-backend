<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
   public function handle(Request $request, Closure $next, ...$roles)
    {
        // استخدام Auth::check() بدلاً من auth() مباشرة أكثر استقراراً أحياناً
        if (!Auth::guard('api')->check() || !in_array(Auth::guard('api')->user()->role, $roles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'عذراً، ليس لديك الصلاحية للقيام بهذا الإجراء.'
            ], 403);
        }

        return $next($request);
    }
}
