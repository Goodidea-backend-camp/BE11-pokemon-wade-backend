<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\RegisterService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Register
 * Operations related to register.
 */
class RegisterController extends Controller
{
    /**
     * 處理新使用者的註冊並寄送註冊信。
     *
     * 此方法會驗證輸入的資料，並在成功驗證後在`users`表中創建一個新的使用者紀錄。
     * 之後，它會觸發一個`Registered`事件，並返回一個成功的響應，包括新創建的使用者的資料。
     *
     * @bodyParam name string required 使用者的名字。示例：John Doe
     * @bodyParam email string required 使用者的電子郵件地址。必須是唯一的並且符合電子郵件格式。示例：john.doe@example.com
     * @bodyParam password string required 使用者的密碼。必須至少有6個字符長並且與`password_confirmation`參數匹配。示例：password123
     * @bodyParam password_confirmation string required 密碼確認。必須與`password`參數匹配。示例：password123
     *
     * @response 201 {
     *   "message": "User registered successfully!",
     *   
     * }
     * 
     * 
     * @response 200 {
     *   "message": "Password updated for the existing Google user.",
     *   
     * }
     * 
     * @response 422 {
     *     "error": [
     *       "The email has already been taken."
     *     ],
     *     // other validation errors...
     *   }
     * }
     * 
     * @response 409 {
     *   "error": "Email already registered.",
     *   
     * }
     * 
     * 
     * 
     * 
     */
    public function register(RegisterRequest $registerRequest, RegisterService $registerService)
    {
        // // 使用驗證器的 validated 方法來獲得驗證後的數據
        $validatedData = $registerRequest->validated();

        // 呼叫服務以註冊用戶
        $registerResponse = $registerService->registerUser($validatedData);

        // 返回相應的響應和狀態碼
        $response = ['error' => $registerResponse['message']];
        return response()->json($response, $registerResponse['status']);
    }

    /**
     * 註冊email驗證信確認
     * 
    
     * 電子郵件驗證確認
     *
     * 此端點用於確認用戶的電子郵件驗證(和前端較無關聯）。
     * 它會比對提供的hash值和用戶的電子郵件生成的hash值。
     * 如果驗證成功，該用戶的電子郵件將被標記為已驗證，並且將觸發一個已驗證的事件。
     * 系統會將email驗證的日期存入資料庫
     *
     * @param Request $request HTTP請求
     * @param int $id 用戶ID
     * @param string $hash 從驗證郵件中提供的hash值
     * 
     * @throws AuthorizationException 當提供的hash值不匹配時
     * 
     * @response 200 {
     *   "message": "Email verified successfully."
     * }
     * 
     * @response 200 {
     *   "message": "Email already verified."
     * }
     */

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // 此方法通常用來判斷文件是否被串改
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException();
        }

        // 判斷這個email是否已經驗證過
        if ($user->hasVerifiedEmail()) {
            return response(['message' => config('success_messages.Email_Verification')]);
        }

        // 到這一步就去將他的user表的email欄位標注日期
        $user->markEmailAsVerified();

        // 直接導回首頁
        return redirect(config('services.frontend.login_url'));
    }
}
