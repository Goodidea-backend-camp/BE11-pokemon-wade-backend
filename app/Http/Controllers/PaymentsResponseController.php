<?php

namespace App\Http\Controllers;

use App\Events\TransactionSuccess;
use App\Models\Ability;
use App\Models\CartItem;
use App\Models\Nature;
use App\Models\Order;
use App\Models\Pokemon;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\NewebpayMpgResponse;
use App\Services\PaymentResultJudgement;
use App\Services\PokemonCreateService;
use App\Services\PostCheckoutService;
use Illuminate\Support\Facades\Log;

/**
 * @group PaymentResponse
 * 
 * 此為藍星金流在結帳完之後會通知後台的，接收結果用的API
 *
 * 
 * @authenticated
 */

class PaymentsResponseController extends Controller
{
    /**
     * 藍星金流結帳完後結果返回確認，寄通知信給使用者
     * 
     * 此方法主要功能如下：
     * 1. 從請求中提取支付相關的資訊。
     * 2. 驗證支付回調的數字簽名以確保資料的完整性。
     * 3. 根據支付結果記錄相關的日誌資訊。
     * 4. 在支付成功後向使用者發送通知郵件。
     * 
     * @apiGroup 支付
     * 
     * @bodyParam TradeInfo string required 支付相關的加密資料。
     * @bodyParam TradeSha string required 支付回調的數字簽名。
     * 
     * @param \Illuminate\Http\Request $request 使用者的HTTP請求，包含支付相關的資訊。
     * 
     * @return void
     */
    public function notifyResponse(Request $request, NewebpayMpgResponse $newebpayMpgResponse, PostCheckoutService $postCheckoutService, PokemonCreateService $pokemonCreateService)
    {
        $tradeInfo = $request->input('TradeInfo');
        $tradeSha = $request->input('TradeSha');
        $merchantOrderNo = $request->input('MerchantOrderNo');
        try {
            $paymentResult = $newebpayMpgResponse->decryptAndDecodeTradeInfo($tradeInfo);

            $merchantOrderNo = $paymentResult['Result']['MerchantOrderNo'];

            $paymentResultJudgement = new PaymentResultJudgement($tradeInfo, $tradeSha, $paymentResult);

            // 檢查支付是否成功，接著寄信
            if (!$paymentResultJudgement->paymentResultJudge()) {
                // 支付失败的處理...
                throw new \Exception(config('error_messages.PAYMENT_FAILED'));
            }

            $postCheckoutService->sendCheckoutSuccessEmailToUser($tradeInfo, $merchantOrderNo);

            // 創建寶可夢
            $pokemonCreateService->createPokemon($merchantOrderNo);
           
            

        } catch (\Exception $e) {
            Log::error('Exception:', [$e->getMessage()]);
        }
    }
}
