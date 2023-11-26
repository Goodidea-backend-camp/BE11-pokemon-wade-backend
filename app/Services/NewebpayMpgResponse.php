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
        $decrypted = openssl_decrypt(hex2bin($encrypted_data), "AES-256-CBC", $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $this->iv);
        return $this->stripPadding($decrypted);
    }

    private function stripPadding($string)
    {
        $slast = ord(substr($string, self::LAST_CHAR_INDEX));
        $slastc = chr($slast);
        if (preg_match("/$slastc{" . $slast . "}/", $string)) {
            $string = substr($string, self::START_INDEX, strlen($string) - $slast);
            return $string;
        }
        throw new \RuntimeException('Invalid padding.');
    }
}
