<?php
namespace App\Services;

use App\Models\CartItem;
use App\Models\Race;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CartItemStoreService
{
    protected $userId;
    protected $quantity;
    protected $cartItem;

    public function __construct()
    {
        $this->userId = Auth::id(); 
    }

    public function handleCartAddition(Race $race, $quantity)
    {
        $this->quantity = $quantity;
        $this->cartItem = CartItem::where('user_id', $this->userId)->where('race_id', $race->id)->first();

        return $this->updateOrCreateCartItem($race);
    }

    protected function updateOrCreateCartItem(Race $race)
    {
        try {
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
                throw ['error' => config('error_messages.shopping_cart.QUANTITY_EXCEED_STOCK'), 'status' => Response::HTTP_BAD_REQUEST];
            }
            $this->cartItem->quantity = $newQuantity;
            $this->cartItem->save();
            return ['success' => config('success_messages.ITEM_ADD_TO_CART'), 'status' => Response::HTTP_OK];
        } catch (HttpException $e) {
            // 處理異常
            return ['error' => $e->getMessage(), 'status' => $e->getStatusCode()];
        }
    }
    
}