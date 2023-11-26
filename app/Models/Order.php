<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const PAID = 2;
    const UNPAID = 1;
    const CANCELED = 0;
    const CASH_ON_DELIVERY = 1;
    const CREDIT_CARD = 0;
    const DONE = 1;
    const PENDING = 0;
    const UNCOMPUTED = 0;
    const SEQUENCE_NUMBER_LENGTH = 6;
    const INITIAL_SEQUENCE_NUMBER = 1;
    const ORDER_NO_YEAR_MONTH_START = 0;
    const ORDER_NO_YEAR_MONTH_LENGTH = 6;
    const ORDER_NO_SEQUENCE_END_OFFSET = -6; // 從訂單号末尾开始的偏移量，用于提取序列号
    const SEQUENCE_INCREMENT = 1;




    protected $fillable = [
        'user_id',
        'total_price',
        'payment_status',
        'payment_method',
        'status',
        'order_no'
    ];

    // Accessor for Payment Status
    public function getPaymentStatusAttribute($value)
    {
        $paymentStatuses = [
            self::PAID => 'paid',
            self::UNPAID => 'unpaid',
            self::CANCELED => 'canceled'

        ];
        return $paymentStatuses[$value] ?? $value;
    }

    // Accessor for Payment Method
    public function getPaymentMethodAttribute($value)
    {
        $paymentMethods = [
            self::CREDIT_CARD => 'credit_card',
            self::CASH_ON_DELIVERY => 'cash_on_delivery',

        ];
        return $paymentMethods[$value] ?? $value;
    }

    // Accessor for Status
    public function getStatusAttribute($value)
    {
        $statuses = [
            self::PENDING => 'pending',
            self::DONE => 'done',

        ];
        return $statuses[$value] ?? $value;
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
