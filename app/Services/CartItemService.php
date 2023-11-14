<?php
namespace App\Services;

use App\Models\CartItem;
use App\Models\Race;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CartItemService
{
    protected $userId;
    protected $quantity;
    protected $cartItem;

    public function __construct()
    {
        $this->userId = Auth::id(); // 获取当前认证用户的ID
    }

    public function handleCartAddition(Race $race, $quantity)
    {
        $this->quantity = $quantity;
        $this->cartItem = CartItem::where('user_id', $this->userId)->where('race_id', $race->id)->first();

        return $this->updateOrCreateCartItem($race);
    }

    protected function updateOrCreateCartItem(Race $race)
    {
        // 假設此商品未加入購物車
        if (!$this->cartItem) {
            CartItem::create([
                'user_id' => $this->userId,
                'quantity' => $this->quantity,
                'current_price' => $race->price,
                'race_id' => $race->id,
            ]);
            return ['success' => config('success_messages.ITEM_ADD_TO_CART'), 'status' => Response::HTTP_OK];
        } 

        $newQuantity = $this->cartItem->quantity + $this->quantity;
        // 加設加總後超過庫存
        if ($newQuantity > $race->stock) {
            return ['error' => config('error_messages.QUANTITY_EXCEED_STOCK'), 'status' => Response::HTTP_BAD_REQUEST];
        }
        $this->cartItem->quantity = $newQuantity;
        $this->cartItem->save();
        return ['success' => config('success_messages.ITEM_ADD_TO_CART'), 'status' => Response::HTTP_OK];


namespace App\Services;

use App\Models\CartItem;

class CartItemService
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
