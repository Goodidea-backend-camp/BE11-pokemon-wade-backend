<?php

namespace App\Http\Controllers;
use App\Http\Resources\PokemonResource;
use App\Models\Pokemon;


/**
 * @group Pokemons
 * Operations related to pokemons.
 * 
 * @authenticated
 */

class PokemonController extends Controller
{

    /**
     
     * 寶可夢列表
     *
     * @group Pokemons
     * @authenticated
     *
     * @response {
     *     "id": 123,
     *     "name": "myBaby",
     *     "level": 50,
     *     "race_id": 25,
     *     "race": "Pikachu",
     *     "photo": "http://example.com/pikachu.jpg",
     *     "ability": "Static",
     *     "nature": "Jolly",
     *     "skills": ["Thunderbolt", "Quick Attack"],
     *     "host": "Ash Ketchum"
     *     "evolution_level":16
     * }
 
     */

    public function index()
    {
        // 透過JWT取得當前登入的用戶
        $user = auth()->user();

        $pokemons = $user->pokemons()->with(['user', 'ability', 'nature', 'race'])->get();
        return PokemonResource::collection($pokemons);
    }

    /**
     * 刪除指定的寶可夢。
     *
     * 此方法允許授權的使用者刪除他們的寶可夢。
     * 成功刪除寶可夢後，將返回成功響應。
     *
     * @response 200 {
     *   "message": "pokemon deleted successfully"
     * }
     * @response 204 {
     *   描述：無內容響應，表示成功刪除了寶可夢。
     * }
     */
    public function destroy(Pokemon $pokemon)
    {
        $this->authorize('delete', $pokemon);
        // 刪除該寶可夢
        $pokemon->delete();
        // 返回成功響應
        return response(['message' => 'pokemon deleted successfully'], 200);
        return response()->noContent();
    }

}
