<?php
namespace App\Services;

use App\Models\{Ability, CartItem, Nature, Order, Pokemon};
use Illuminate\Support\Facades\DB;

class PokemonCreateService
{
    public function createPokemon($merchantOrderNo)
    {
        // 取的使用者id
        $checkedOutUserId = Order::where('order_no', $merchantOrderNo)
        ->pluck('user_id')
        ->unique()
        ->first();
        // 取得該使用者購物車
        $cartItems = CartItem::where('user_id', $checkedOutUserId)->get();
        
        foreach ($cartItems as $item) {
            // 這裡調用其他服務方法
            $this->createPokemonForCartItem($item);
        }
        // 刪除購物車資料
        CartItem::where('user_id', $checkedOutUserId)->delete();
    }

    protected function createPokemonForCartItem($cartItem)
    {
        $randomAbilityId = Ability::inRandomOrder()->first()->id;
        $randomNatureId = Nature::inRandomOrder()->first()->id;
        $skillsIdsForRace = $this->getSkillsForRace($cartItem->race_id);

        $pokemon = Pokemon::create([
            'name' => '', 
            'level' => rand(1, 100),
            'user_id' => $cartItem->user_id,
            'race_id' => $cartItem->race_id,
            'ability_id' => $randomAbilityId,
            'nature_id' => $randomNatureId,
            'skills' => $skillsIdsForRace,
        ]);

        return $pokemon;
    }

    protected function getSkillsForRace($raceId)
    {
        $skillsIdsForRace = DB::table('race_skill')
            ->where('race_id', $raceId)
            ->pluck('skill_id');

        // 隨機選擇最多四個技能 ID
        return $skillsIdsForRace->random(min(4, $skillsIdsForRace->count()))->toArray();
    }
}
