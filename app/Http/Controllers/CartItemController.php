<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Models\Race;
use App\Services\CartItemService;
use App\Services\CartItemStoreService;
use App\Services\CartItemUpdateService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group CartItem
 * Operations related to cartItems.
 * 
 * @authenticated
 */

class CartItemController extends Controller
{
    /**
     * 顯示購物車商品
     * 
     * 這部分主要是用來顯示購物車頁面的商品資訊
     *
     * 
     * @response 200 {

     *"data": [
     * {
     *   "id": 67,
     *   "amount": 1,
     *   "current_price": "147.00",
     *   "race_name": "charizard",
     *   "race_photo": "https://raw.githubusercontent.com/*PokeAPI/sprites/master/sprites/pokemon/6.png",
     *   "race_id": 6
     * },
     * {
     *  "id": 68,
     *  "amount": 1,
     *  "current_price": "478.00",
     *  "race_name": "wartortle",
     *  "race_photo": "https://raw.githubusercontent.com/*PokeAPI/sprites/master/sprites/pokemon/8.png",
     *  "race_id": 8
     * }
     * ],
     *  "subtotal": 625
     *}
     * 
     * 
     * 
     * @response 401 {
     *     "message": "Unauthenticated."
     * }
     * 
     * @return \Illuminate\Http\Response
     * 
     */

    public function index()
    {
        $user = auth()->user();

        $carts = $user->cartItems()->with(['race'])->get();
        return CartItemResource::collection($carts);
    }

    /**
     * 加入購物車
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @bodyParam quantity int required 購買的數量，必須在1到庫存的範圍內。Example: 2
     * @bodyParam race_id int required 種族的ID，必須存在於種族表中。Example: 5
     * 
     * @response 200 {
     *     "message": "Item added to cart successfully."
     * }
     * 
     * @response 400 {
     *     "error": "Requested quantity exceeds available stock."
     * }
     * 
     * @response 404 {
     *     "error": "Resource not found"
     * }
     * 
     * @response 422 {
     * "quantity": [
     *    "Requested quantity exceeds available stock"
     *]
     *}
     * 
     * @return \Illuminate\Http\Response
     * 
     */

    public function store(Race $race, CartItemRequest $request, CartItemStoreService $cartItemStoreService)
    {
        $validationData = $request->validated();
        $result = $cartItemStoreService->handleCartAddition($race, $validationData['quantity']);

        if (array_key_exists('error', $result)) {
            return response(['error' => $result['error']], $result['status']);
        }

        return response(['message' => $result['success']], $result['status']);
    }

    /**
     * 購物車更新
     * 
     * 在此API會更新購物車資訊，然後將總金額計算後回傳
     * 
     * @bodyParam products[] array required The list of products to update. Example: [{"product_id": "123", "quantity": 2}, {"product_id": "456", "quantity": 5}]
     * @bodyParam products[].product_id string required The ID of the product.
     * @bodyParam products[].quantity integer required The new quantity for the product. 
     *
     *
     * @bodyParam quantity int required 更新的商品數量，必須在1到庫存的範圍內。Example: 3
     * 
     * @response 200 {
     * "message": "Item added to cart successfully."
     *     
     * }
     * 
     * @response 400 {
     *     "error": "Validation error message."
     * 
     * }
     * @response 422{
     * "error": "No cart item found for the given user and race."
     *}
     * 
     * 
     * @response 422 {
     * "quantity": [
     *    "Requested quantity exceeds available stock"
     *]
     *}
     * @return \Illuminate\Http\Response 包含購物車的總金額的響應
     */
    public function update(Race $race, CartItemRequest $request, CartItemUpdateService $cartItemUpdateService)
    {
        try {
            $validationData = $request->validated();
            $userId = auth()->user()->id;
            $totalPrice = $cartItemUpdateService->updateCartItemAndCalculateTotal($userId, $race->id, $validationData['quantity']);

            return response(['total_price' => $totalPrice], Response::HTTP_OK);
        } catch (\Exception $e) {
            // 捕獲任何拋出的異常並返回錯誤響應
            return response(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * 購物車刪除
     * 
     * @param \App\Models\CartItem $cartItem 購物車的項目
     * 
     * @response 204
     * @response 404 {
     *     "error": "Resource not found."
     * }
     * 
     * * @response 403 {
     *     "error": "Unauthorized"
     * }
     * 
     * @return \Illuminate\Http\Response 返回無內容的204響應，表示成功刪除
     */
    public function destroy(CartItem $cartItem)
    {
        $this->authorize('delete', $cartItem);
        $cartItem->delete();
        return response()->noContent();
    }
}
