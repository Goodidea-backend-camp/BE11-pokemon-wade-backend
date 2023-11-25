<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


/**
 * @group Payment
 * Operations related to payments.
 * 
 * @authenticated
 */
class PaymentController extends Controller
{
    /**
     * 請求藍星金流結帳頁面
     * 
     * 主要使用者點選結帳後可以請求這個API，然後藍星金流會發結帳頁面給使用者，
     * 此方法主要功能如下：
     * 1. 驗證當前使用者。
     * 2. 更新與當前使用者關聯的購物車項目的結帳狀態。
     * 3. 生成與藍星金流相關的支付參數（包括加密和哈希）。
     * 4. 返回支付參數，以便前端將使用者重定向到藍星金流的支付頁面。
     * 
     * @apiGroup 支付
     * 
     * 
     * @response 200 {
     *{
    *"headers": {},
    *"original": {
        *"payment_url": "https://ccore.newebpay.com/MPG/mpg_gateway",
        *"mid": "MS150428218",
        *"edata1": "xxxxx",
        *"hash": "57E12xxxxx"
    *},
    *"exception": null
*}
     * }
     * 
     * @response 400 {
     * "error": "No products in the cart to checkout."
     *}
     * 
     * 
     * @param \Illuminate\Http\Request $request 用戶的HTTP請求。
     * 
     * @return \Illuminate\Http\JsonResponse 返回包含支付參數的JSON響應。
     */

    public function prepareForPaymentData(Request $request, PaymentService $paymentService, OrderService $orderService)
    {
        // 獲取用户的ID
        $user = Auth::user();
        $cartItems = CartItem::where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => config('error_messages.NO_CHECKOUT')], Response::HTTP_BAD_REQUEST);
        }

        // 生成訂單和訂單詳情
        $order = $orderService->createOrderAndDetails($user, $cartItems);
        // 包裝成要給藍星的資料
        $paymentData = $paymentService->preparePaymentInfo($order);

        return response()->json($paymentData);
    }
}
