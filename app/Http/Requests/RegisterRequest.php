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

    public function rules()
    {
        return [
            //   - ^ 和 $ 表示字符串的開始和結束。
            //   - a-zA-Z0-9 允許字母和數字。
            //   - \s 允許空格。
            //   - \x{4e00}-\x{9fa5} 允許中文字符（漢字），範圍涵蓋了絕大多數常用漢字。
            //   - * 表示前面的字符可以出現任意次（包括零次）。
            //   - u 修飾符表示正則表達式使用 UTF-8 編碼。
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
