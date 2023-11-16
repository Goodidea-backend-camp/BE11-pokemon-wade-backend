<?php

namespace App\Services;

use App\Events\TransactionSuccess;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PostCheckoutService
{

    public function sendCheckoutSuccessEmailToUser($tradeInfo,$merchantOrderNo)
    {
        $userInfo = $this->getUserEmailFromTradeInfo($tradeInfo,$merchantOrderNo );
        if ($userInfo) {
            // 發送電子邮件
            $this->sendCheckoutSuccessEmail($userInfo['email'], $userInfo);
        }
    }

    private function getUserEmailFromTradeInfo($tradeInfo,$merchantOrderNo)
    {

        // 根據 tradeInfo 獲取用户ID
        $checkedOutUserId = Order::where('order_no', $merchantOrderNo)
            ->pluck('user_id')
            ->unique()
            ->first();

        if (!$checkedOutUserId) {
            throw new \Exception(config('error_messages.NO_CHECKOUT_USER_FOUND'. $tradeInfo));
            return null;
        }

        // 獲取用户信息
        $user = User::find($checkedOutUserId);

        if (!$user) {
            throw new \Exception(config('error_messages.USER_NOT_FOUND'. $checkedOutUserId));
            return null;
        }

        // 返回用户的 email 和 name
        return [
            'email' => $user->email,
            'name' => $user->name, // 假设用户模型有一个名字字段
        ];
    }


    private function sendCheckoutSuccessEmail($email, $userData)
    {
        // 發送电子邮件的逻辑
        event(new TransactionSuccess($email, $userData));
    }
}
