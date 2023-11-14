<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;


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
     * @bodyParam totalPrice float required 購物車中所有商品的總價格。
     * 
     * @param \Illuminate\Http\Request $request 用戶的HTTP請求。
     * 
     * @return \Illuminate\Http\JsonResponse 返回包含支付參數的JSON響應。
     */

    public function prepareForPaymentData(Request $request, PaymentService $paymentService)
    {
        // 获取用户的ID
        $userId = Auth::user()->id;

        // 更新與該用戶關聯的購物車狀態
        CartItem::where('user_id', $userId)->update(['checkout_status' => 'checked']);

        $totalPrice = $request->input('totalPrice');
        $paymentData = $paymentService->preparePaymentInfo($totalPrice);
    
        return response()->json($paymentData);

    

       
    }
}
