<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // 訂單狀態相關常量
    // 訂單狀態相關常量
    const ORDER_STATUS = [
        'payment_status' => [
            'PAID' => 2,      // 已支付
            'UNPAID' => 1,    // 未支付
            'CANCELED' => 0,  // 已取消
        ],
        'process_status' => [
            'DONE' => 1,        // 已完成
            'PENDING' => 0,     // 待處理
            'UNCOMPUTED' => 0,  // 未計算或未處理（如果 UNCOMPUTED 與 PENDING 的意義不同）
        ],
    ];

    // 付款方式相關常量
    const PAYMENT_METHOD = [
        'CASH_ON_DELIVERY' => 1,  // 貨到付款
        'CREDIT_CARD' => 0,       // 信用卡支付
    ];

    // 訂單編號相關常量
    const ORDER_NUMBER_CONFIG = [
        'SEQUENCE_NUMBER_LENGTH' => 6,              // 序列號長度
        'INITIAL_SEQUENCE_NUMBER' => 1,             // 初始序列號
        'ORDER_NO_YEAR_MONTH_START' => 0,           // 訂單號中年月開始的位置
        'ORDER_NO_YEAR_MONTH_LENGTH' => 6,          // 訂單號中年月的長度
        'ORDER_NO_SEQUENCE_END_OFFSET' => -6,       // 從訂單號末尾開始的偏移量，用於提取序列號
        'SEQUENCE_INCREMENT' => 1,                  // 序列號遞增量
    ];


    protected $fillable = [
        'user_id',
        'total_price',
        'payment_status',
        'payment_method',
        'status',
        'order_no'
    ];

    public function getPaymentStatusAttribute($value)
    {
        $paymentStatusMap = [
            self::ORDER_STATUS['payment_status']['PAID'] => 'paid',
            self::ORDER_STATUS['payment_status']['UNPAID'] => 'unpaid',
            self::ORDER_STATUS['payment_status']['CANCELED'] => 'canceled'
        ];
        return $paymentStatusMap[$value] ?? $value;
    }

    // Accessor for Payment Method
    public function getPaymentMethodAttribute($value)
    {
        $paymentMethodMap = [
            self::PAYMENT_METHOD['CREDIT_CARD'] => 'credit_card',
            self::PAYMENT_METHOD['CASH_ON_DELIVERY'] => 'cash_on_delivery',
        ];
        return $paymentMethodMap[$value] ?? $value;
    }

    // Accessor for Status
    public function getStatusAttribute($value)
    {
        $statusMap = [
            self::ORDER_STATUS['process_status']['PENDING'] => 'pending',
            self::ORDER_STATUS['process_status']['DONE'] => 'done',
        ];
        return $statusMap[$value] ?? $value;
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
