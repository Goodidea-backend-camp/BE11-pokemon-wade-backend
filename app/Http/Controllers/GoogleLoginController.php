<?php

namespace App\Http\Controllers;

use App\Services\GoogleLoginService;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group GoogleLogin
 * Operations related to googleLogin.
 */

class GoogleLoginController extends Controller
{

    

    /**
     * 重定向到Google进行身份验证
     *
     * 调用此端点後端會回傳授權的url，前端再將用户重定向到Google的登录页面进行身份验证。
     * 成功后，Google会将用户重定向回应用的回调URL也就是以下的API。
     *
     * 
     *
     * @response 200 {
     *  "url": "https://accounts.google.com/o/oauth2/auth?response_type=code&client_id=..."
     * }
     * @response 500 {
     *  "error": "Failed to redirect"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function redirectToProvider()
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            return response()->json(['error' => config('error_messages.REDIRECTFAILED')], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     * 處理從 Google 第三方認證服務頁面返回的回調。
     *
     * 此處就是可以從google拿到使用者資訊並儲存在資料庫
     * 然後反回帶token的cookie
     * @response 200 {
     *     "message": "Login successful via Google",
     *     "user": "使用者的資料"
     * }
     * 
     * @return \Illuminate\Http\Response 用 JSON 格式返回的成功消息、JWT 令牌和使用者資訊。
     */


     public function handleProviderCallback(GoogleLoginService $googleLoginService)
    {
        $socialUser = Socialite::driver('google')->user();
        
        // 使用 GoogleLoginService 處理用户信息
        $token = $googleLoginService->handleGoogleUser($socialUser);

        // 將token设置在HTTP Only的Cookie中
        $cookie = cookie('jwt', $token, 60, null, null, false, true);

        // 重定向到前端路由，并带上cookie
        return redirect(config('services.frontend.url'))->withCookie($cookie);
    }
     
}
