<?php

namespace App\Services;

class PaymentService
{
    public function preparePaymentInfo($order)
    {
        $paymentConfig = $this->getPaymentConfig();
        $tradeInfo = $this->buildTradeInfo($order, $paymentConfig,);
        $encodedData = $this->encryptData($tradeInfo, $paymentConfig);
        $hash = $this->generateHash($encodedData, $paymentConfig);

        // 將各個資訊組裝成陣列
        return response()->json([
            'payment_url' => $paymentConfig['payment_url'],
            'mid' => $paymentConfig['id'],
            'edata1' => $encodedData,
            'hash' => $hash
        ]);
    }

    private function getPaymentConfig()
    {
        return [
            'key' => config('payment.key'),
            'iv' => config('payment.iv'),
            'id' => config('payment.id'),
            'payment_url' => config('payment.payment_url'),
        ];
    }

    private function buildTradeInfo($order, $paymentConfig)
    {
        return http_build_query(array(
            'MerchantID' => $paymentConfig['id'],
            'RespondType' => 'JSON',
            'TimeStamp' => time(),
            'Version' => '2.0',
            'Amt' => $order->total_price,
            'MerchantOrderNo' => $order->order_no,
            'ItemDesc' => 'Good choice!',
        ));
    }


    private function encryptData($tradeInfo, $paymentConfig)
    {
        return bin2hex(openssl_encrypt(
            $tradeInfo,
            "AES-256-CBC",
            $paymentConfig['key'],
            OPENSSL_RAW_DATA,
            $paymentConfig['iv']
        ));
    }

    private function generateHash($encodedData, $paymentConfig)
    {
        $hashString = "HashKey=" . $paymentConfig['key'] . "&" . $encodedData . "&HashIV=" . $paymentConfig['iv'];
        return strtoupper(hash("sha256", $hashString));
    }
}
