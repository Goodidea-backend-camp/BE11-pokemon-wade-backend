<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Race;
use Illuminate\Support\Facades\DB;

class OrderService
{

    public function createOrderAndDetails($user, $cartItems)
    {
        DB::beginTransaction();
        try {
            // 檢查是否有未結帳的訂單，如果有，則不會再次新增訂單。
            $order = Order::where('user_id', $user->id)->where('payment_status', Order::ORDER_STATUS['payment_status']['UNPAID'])->first() ?? $this->createNewOrder($user->id, $this->generateUniqueOrderNo());

            // 計算總金額並更新訂單
            $order->total_price = $this->calculateTotalAmountAndCreateOrderDetails($cartItems, $order->id, $order);
            $order->save();
            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function createNewOrder($userId, $uniqueOrderNo)
    {
        $order = Order::create([
            'user_id' => $userId,
            'order_no' => $uniqueOrderNo,
            'total_price' => Order::ORDER_STATUS['process_status']['UNCOMPUTED'],
            'payment_status' => Order::ORDER_STATUS['payment_status']['UNPAID'],
            'payment_method' => Order::PAYMENT_METHOD['CREDIT_CARD'],
            'status' => Order::ORDER_STATUS['process_status']['PENDING'],
        ]);
        return $order;
    }

    private function calculateTotalAmountAndCreateOrderDetails($cartItems, $orderId, $pendingOrder)
    {
        $totalPrice = Order::ORDER_STATUS['process_status']['UNCOMPUTED'];

        // 如果是未結帳訂單，先刪除現有訂單細節，因為有可能使用者會更改內容。
        if ($pendingOrder) {
            OrderDetail::where('order_id', $orderId)->delete();
        }

        // 然後根據購物車創建新的訂單細節
        foreach ($cartItems as $item) {
            $race = Race::findOrFail($item['race_id']);
            $subtotal = $race->price * $item['quantity'];
            $totalPrice += $subtotal;

            // 創建訂單細節
            $this->createOrderDetail($orderId, $item, $race->price, $subtotal);
        }

        return $totalPrice;
    }


    private function createOrderDetail($orderId, $item, $unitPrice, $subtotal)
    {
        OrderDetail::create([
            'order_id' => $orderId,
            'race_id' => $item['race_id'],
            'unit_price' => $unitPrice,
            'quantity' => $item['quantity'],
            'subtotal_price' => $subtotal,
        ]);
    }

    private function generateUniqueOrderNo()
    {
        $yearMonth = date('Ym'); // 獲取當前年月
        $sequenceNumber = $this->getNextSequenceNumber($yearMonth); // 獲取序列號
        $formattedSequence = str_pad($sequenceNumber, Order::ORDER_NUMBER_CONFIG['SEQUENCE_NUMBER_LENGTH'], "0", STR_PAD_LEFT);
        return $yearMonth . $formattedSequence;
    }

    private function getNextSequenceNumber($yearMonth)
    {
        $lastOrder = Order::orderBy('id', 'desc')->first(); //取最後一筆訂單

        $currentSequence = Order::ORDER_NUMBER_CONFIG['INITIAL_SEQUENCE_NUMBER']; // 預設從1開始
        if ($lastOrder) {
            $lastOrderYearMonth = substr($lastOrder->order_no, Order::ORDER_NUMBER_CONFIG['ORDER_NO_YEAR_MONTH_START'], Order::ORDER_NUMBER_CONFIG['ORDER_NO_YEAR_MONTH_LENGTH'],); // 提取最後一筆訂單的年月
            $lastOrderSequence = intval(substr($lastOrder->order_no, Order::ORDER_NUMBER_CONFIG['ORDER_NO_SEQUENCE_END_OFFSET'])); // 提取最後一筆訂單的序列號

            if ($lastOrderYearMonth == $yearMonth) {
                $currentSequence = $lastOrderSequence + Order::ORDER_NUMBER_CONFIG['SEQUENCE_INCREMENT']; // 如果年月和今天相同，序列號加1，如果日期變了就回到1
            }
        }

        return $currentSequence;
    }

    public function orderStatusUpdate($merchantOrderNo)
    {
        // 更改訂單狀態
        $order = Order::where('order_no', $merchantOrderNo)->first();
        $order->update([
            'payment_status' => Order::ORDER_STATUS['payment_status']['PAID'], // 根據您的對應，2代表“已支付”
            'status' => Order::ORDER_STATUS['process_status']['DONE'] // 根據您的對應，1代表“已完成”
        ]);
    }
}
