<?php

namespace App\Http\Controllers;

use App\Models\Ability;
use Illuminate\Http\Request;

class AbilityController extends Controller
{
    public function index()
    {
        $allNatures = Ability::select('id', 'name')->get();
        return $allNatures;
    }

    public function store(Request $request)
    {

        $validationData = $request->validate(
            [
                'name' => 'required|max:255|alpha_unicode|unique:abilities,name',
            ],
            [
                'name.alpha_unicode' => '名稱只能包含中文和英文字。',
            ]
        );

        Ability::create([
            'name' => $validationData['name']
        ]);

        return response(['message' => 'Ability saved successfully'], 201);
    }


    public function update(Request $request, Ability $ability)
    {
        $validationData = $request->validate(
            [
                'name' => 'required|max:255|alpha_unicode|unique:abilities,name',
            ],
            [
                'name.alpha_unicode' => '名稱只能包含中文和英文字符。',
            ]
        );

        $ability->update([
            'name' => $validationData['name']
        ]);

        return response(['message' => 'Ability updated successfully'], 200);
    }
}
