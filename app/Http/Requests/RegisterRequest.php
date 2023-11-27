<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */


    /**
     * 定義用戶輸入驗證規則。
     *
     * 此函數返回一組驗證規則，用於檢查用戶提交的數據是否符合要求。
     * 包括以下字段的驗證：
     *  - name: 必須填寫，僅允許包含字母、數字、空格和中文字符，並且長度不能超過              User::MAX_NAME_LENGTH 定義的最大值。
     * 
     *  - email: 必須填寫，需要是有效的電子郵件格式，長度不能超過 User::MAX_EMAIL_LENGTH 定義的最大值。
     * 
     *  - password: 必須填寫，長度至少為 User::MIN_PASSWORD_LENGTH 定義的最小值，並且需要與確認密碼字段匹配。
     *
     * @return array 返回一個包含驗證規則的陣列。
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:' . User::MAX_NAME_LENGTH . '|regex:/^[a-zA-Z0-9\s\x{4e00}-\x{9fa5}]*$/u',
            'email' => 'required|string|email|max:' . User::MAX_EMAIL_LENGTH,
            'password' => 'required|string|min:' . User::MIN_PASSWORD_LENGTH . '|confirmed',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // 使用換行符合併所有的錯誤訊息
        $errorMessage = implode(" ", $validator->errors()->all());

        $response = response()->json(['error' => $errorMessage], Response::HTTP_UNPROCESSABLE_ENTITY);
        throw new HttpResponseException($response);
    }
}
