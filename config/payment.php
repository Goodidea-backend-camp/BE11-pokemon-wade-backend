<?php


return [
    'key' => env('MERCHANT_KEY'),
    'iv' => env('MERCHANT_IV'),
    'id' => env('MERCHANT_ID'),
    'notify_url' => env('NOTIFY_URL'),
    'return_url' => env('RETURN_URL'),
    'payment_url'=> env('PAYMENT_URL'),
    'base_url' => 'https://wadelee.shop/order',
    'ItemDescribe' => 'Good choice!',
    'RespondType' => "JSON",
    'Version' => "2.0",
    'encript_method' =>"AES-256-CBC",
    'hash_method' =>"sha256"
];
