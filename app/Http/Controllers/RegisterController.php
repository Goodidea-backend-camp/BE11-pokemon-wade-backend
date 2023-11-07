<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
     *   "user": {
     *     "name": "John Doe",
     *     "email": "john.doe@example.com",
     *     // other user fields...
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": [
     *       "The email has already been taken."
     *     ],
     *     // other validation errors...
     *   }
     * }
     */
    public function register(RegisterRequest $request)
    {
        $validatedData = $request->validated();
        $existingUser = User::where('email', $validatedData['email'])->first();

        // 如果电子邮件已存在，并且用户没有google_id，则认为用户已注册
        if ($existingUser && is_null($existingUser->google_id)) {
            return response(['message' => 'Email already registered.'], Response::HTTP_CONFLICT);
        }

        // 如果电子邮件已存在，用户有google_id，且请求中提供了密码，更新密码
        if ($existingUser && !is_null($existingUser->google_id) && !empty($validatedData['password'])) {
            $existingUser->update([
                'password' => Hash::make($validatedData['password']),
                // 可能还需要更新其他字段...
            ]);
            // 不发送注册邮件，因为用户通过Google ID已存在
            return response(['message' => 'Password updated for the existing Google user.'], Response::HTTP_OK);
        }

        // 电子邮件不存在，创建新用户
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']), 
            'role' => 'user',
        ]);
        

        // 发送注册确认邮件（您需要定义发送邮件的逻辑）
        event(new Registered($user));

        return response(['message' => 'User successfully registered.'], Response::HTTP_CREATED);
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
        return redirect(config('services.frontend.url'));
    }
}
