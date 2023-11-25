<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CartItemRequest extends FormRequest
{
    protected $race;

    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        $this->race = $this->route('race'); 

        
    }

    public function rules(): array
    {
            $raceStock = $this->race->stock;
            return [
                'quantity' => 'required|int|min:1|max:' . $raceStock,
                
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

