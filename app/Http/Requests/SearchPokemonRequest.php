<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchPokemonRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {

        return [
            'name' => 'string|max:255',
            'race_id' => 'integer|exists:pokemons,race_id',
            'ability_id' => 'integer|exists:pokemons,ability_id',
            'nature_id' => 'integer|exists:pokemons,nature_id',
            'level' => 'integer|exists:pokemons,level',
            // 'skills' => 'integer|exists:pokemons,skills'
        ];
    }
}
