<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthCookieMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 嘗試从 cookie 中獲取 token
        $token = $request->cookie('jwt');
    
        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        } 

        try {
            // 使用 token 認證用户
            $user = JWTAuth::setToken($token)->authenticate();
            // 如果認證通过，將用户信息設置到请求對象中
            Auth::setUser($user);
        } catch (JWTException $e) {
            // 如果在此過程中出现任何異常，返回錯誤響應
            return response()->json(['error' => 'error_messages.general.UNAUTHORIZED'], Response::HTTP_UNAUTHORIZED);
        }
        // 繼續處理请求
        return $next($request);
    }
    
}
