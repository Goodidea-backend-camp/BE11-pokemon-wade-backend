<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use openssl_cipher_iv_length;
use openssl_decrypt;
use Illuminate\Support\Facades\Log;

class NewebpayMpgResponse
{
    protected $key;
    protected $iv;

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
        $slast = ord(substr($string, -1));
        $slastc = chr($slast);
        if (preg_match("/$slastc{" . $slast . "}/", $string)) {
            $string = substr($string, 0, strlen($string) - $slast);
            return $string;
        }
        throw new \RuntimeException('Invalid padding.');
    }
}

  
