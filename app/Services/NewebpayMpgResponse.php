<?php

namespace App\Services;

class NewebpayMpgResponse
{
    protected $key;
    protected $iv;
    const START_INDEX = 0;
    const LAST_CHAR_INDEX = -1;

    public function __construct()
    {
        $this->key = config('payment.key');
        $this->iv = config('payment.iv');
    }

    public function decryptAndDecodeTradeInfo($tradeInfo)
    {
        if (!$tradeInfo) {
            throw new \InvalidArgumentException('TradeInfo not provided');
        }

        $decryptedString = $this->decrypt($tradeInfo);
        $tradeData = json_decode($decryptedString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON decode error: ' . json_last_error_msg());
        }

        return $tradeData;
    }

    private function decrypt($encrypted_data)
    {
        $decrypted = openssl_decrypt(hex2bin($encrypted_data), config("payment.encript_method"), $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv);
        return $this->stripPadding($decrypted);
    }

    private function stripPadding($string)
    {
        $lastCharValue = ord(substr($string, self::LAST_CHAR_INDEX));
        $lastChar = chr($lastCharValue);
        if (preg_match("/$lastChar{" . $lastCharValue . "}/", $string)) {
            $string = substr($string, self::START_INDEX, strlen($string) - $lastCharValue);
            return $string;
        }
        throw new \RuntimeException('Invalid padding.');
    }
}
