<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;

class CartItemPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function delete(User $user, CartItem $cartItem): bool
    {

        return $user->id === $cartItem->user_id;
    }
}
