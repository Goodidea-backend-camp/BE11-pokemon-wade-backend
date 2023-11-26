<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'name' => 'required|string|max:'.User::MAX_NAME_LENGTH.'|regex:/^[a-zA-Z0-9\s]*$/',
            'email' => 'required|string|email|max:'.User::MAX_EMAIL_LENGTH,
            'password' => 'required|string|min:'.User::MIN_PASSWORD_LENGTH.'|confirmed',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // 使用換行符合併所有的錯誤訊息
        $errorMessage = implode(" ", $validator->errors()->all());

        $response = response()->json(['error' => $errorMessage], 422);
        throw new HttpResponseException($response);
    }
}
