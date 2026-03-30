<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     *@param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next */
   public function handle(Request $request, Closure $next, $role)
{
   $user = Auth::guard('api')->user();
   
    if (!$user || $user->role !== $role) {
        return response()->json(['message' => 'عذراً، ليس لديك الصلاحية!'], 403);
    }

    // 
    if ($user->role === 'exhibitor' && !$user->is_approved) {
        return response()->json([
            'message' => 'حسابك كعارض لا يزال قيد المراجعة من قبل الإدارة.'
        ], 403);
    }

    return $next($request);
}
}
