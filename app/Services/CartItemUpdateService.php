<?php 

namespace App\Services;

use App\Models\CartItem;

class CartItemUpdateService
{
    public function updateCartItemAndCalculateTotal($userId, $raceId, $quantity)
    {
        $this->updateCartItem($userId, $raceId, $quantity);
        return $this->calculateTotalPrice($userId);
    }

    private function updateCartItem($userId, $raceId, $quantity)
    {
        $cartItem = CartItem::where('user_id', $userId)->where('race_id', $raceId)->first();

        if (!$cartItem) {
            // 處理找不到 CartItem 的情況
            throw new \Exception(config("error_messages.NO_CART_ITEM"));
        }

        $cartItem->update(['quantity' => $quantity]);
    }

    private function calculateTotalPrice($userId)
    {
        $total = CartItem::where('user_id', $userId)
            ->selectRaw('SUM(current_price * quantity) as total')
            ->value('total');

        if ($total === null) {
            // 處理無法計算總金額的情況
            throw new \Exception(config("error_messages.CACULATION_FAILED"));
        }

        return $total;
    }
}
