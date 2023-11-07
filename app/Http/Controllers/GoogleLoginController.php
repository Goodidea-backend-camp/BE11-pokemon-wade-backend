<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

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
     *  "error": "Unable to redirect to Google. Please try again later."
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function redirectToProvider()
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            return response()->json(['error' => '無法重定向到Google。請稍後再試。'], 500);
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


     public function handleProviderCallback()
     {
         $socialUser = Socialite::driver('google')->user();
     
         // 使用 email 查找本地用戶
         $user = User::where('email', $socialUser->getEmail())->first();
     
         // 如果用戶不存在，創建用戶並附加 google_id
         if (!$user) {
             $user = User::create([
                 'email' => $socialUser->getEmail(),
                 'name' => $socialUser->getName(),
                 'google_id' => $socialUser->getId(),
                 'email_verified_at' => now(), // 設置電子郵件驗證的時間
             ]);
         } else {
             // 如果用戶存在，並且 google_id 為空，則更新 google_id
             if (empty($user->google_id)) {
                 $user->google_id = $socialUser->getId();
                 $user->save();
             }
         }
     
         // 為用戶生成 JWT
         $token = JWTAuth::fromUser($user);
     
         // 將 token 設置在 HTTP Only Cookie 中
         $cookie = cookie('jwt', $token, 60, null, null, false, true);
         // 重定向到前端的某個路由，並攜帶 cookie
         return redirect(config('services.frontend.url'))->withCookie($cookie);
     }
     
}
