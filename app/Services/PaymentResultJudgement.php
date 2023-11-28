<?php

namespace App\Services;

class PaymentResultJudgement
{
    private $tradeData;
    private $tradeInfo;
    private $tradeSha;

    public function __construct($tradeInfo, $tradeSha, $tradeData)
    {
        $this->tradeData = $tradeData;
        $this->tradeSha = $tradeSha;
        $this->tradeInfo = $tradeInfo;
    }

    public function paymentResultJudge()
    {
        if ($this->isSuccess() && $this->isHashMatched()) {
            return true;
        }
        return false;
    }

    private function isSuccess()
    {
        return $this->tradeData['Status'] === config('payment.Transaction_successful');
    }

    private function isHashMatched()
    {
        $key = config('payment.key');
        $iv = config('payment.iv');
        $hashString = "HashKey=$key&$this->tradeInfo&HashIV=$iv"; 
        $hash = strtoupper(hash("sha256", $hashString));

        return $hash == $this->tradeSha;
    }
}
