<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequst extends FormRequest
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
    public function rules(): array
    {
        return [
            'total_price' => 'required|int|min:1',
            'payment_method' => 'required|string|in:credit_card,cash_on_delivery',
            'payment_status' => 'required|string|in:paid,unpaid,canceled',

        ];
    }
}
