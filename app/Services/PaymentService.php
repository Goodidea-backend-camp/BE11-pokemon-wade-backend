<?php

namespace App\Services;

class PaymentService
{
    public function preparePaymentInfo($totalPrice)
    {
        $paymentConfig = $this->getPaymentConfig();
        $tradeInfo = $this->buildTradeInfo($totalPrice, $paymentConfig);
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
            'notify_url' => config('payment.notify_url'),
            'return_url' => config('payment.return_url'),
            'payment_url' => config('payment.payment_url'),
        ];
    }

    private function buildTradeInfo($totalPrice, $paymentConfig)
    {
        return http_build_query(array(
            'MerchantID' => $paymentConfig['id'],
            'RespondType' => 'JSON',
            'TimeStamp' => time(),
            'Version' => '2.0',
            'MerchantOrderNo' => "test0315001" . time(),
            'Amt' => $totalPrice,
            'ItemDesc' => 'test',
            'NotifyURL' => $paymentConfig['notify_url'],
            'ReturnURL' => $paymentConfig['return_url'],
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
