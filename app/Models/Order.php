<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

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
        $statuses = [
            '2' => 'paid',
            '1' => 'unpaid',
            '0' => 'canceled'
            // ... 如果還有其他的映射，你可以繼續添加
        ];
        return $statuses[$value] ?? $value;
    }

    // Accessor for Payment Method
    public function getPaymentMethodAttribute($value)
    {
        $methods = [
            '0' => 'credit_card',
            '1' => 'cash_on_delivery',
            // ... 如果還有其他的映射，你可以繼續添加
        ];
        return $methods[$value] ?? $value;
    }

    // Accessor for Status
    public function getStatusAttribute($value)
    {
        $statuses = [
            '0' => 'Pending',
            '1' => 'Processed',
            // ... 如果還有其他的映射，你可以繼續添加
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
