<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Race;

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

    public function messages()
    {
        return [
            'quantity.max' => config('error_messages.QUANTITY_EXCEED_STOCK'),
        ];
    }
}
