<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentErrorHandlingService
{
    public function handlePaymentException($exception, $paymentResult, $merchantOrderNo)
    {
        // 取得結果的狀態
        $errorCode = $paymentResult['Status'];
        $errorMessage = config("error_messages.{$errorCode}");
        Log::error('Exception:', [$exception->getMessage()]);
        // 更新訂單支付狀態為：取消
        $this->updateOrderPaymentStatus($merchantOrderNo, 0);
        return redirect("https://wadelee.shop/order?status=$errorMessage&order=$merchantOrderNo");
    }

    protected function updateOrderPaymentStatus($orderNo, $status)
    {
        $order = Order::where('order_no', $orderNo)->first();
        if ($order) {
            $order->payment_status = $status;
            $order->save();
        }
    }
}