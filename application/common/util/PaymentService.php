<?php
namespace app\common\util;

use think\Db;
use app\common\model\Payment as PaymentModel;
use app\common\model\Order as OrderModel;

class PaymentService
{
    public static function createOrder($user_id, $order_type, $amount, $points = 0, $vod_id = 0, $live_id = 0)
    {
        $order_code = 'ORD' . date('YmdHis') . rand(1000, 9999);
        
        $data = [
            'user_id' => $user_id,
            'order_code' => $order_code,
            'order_type' => $order_type,
            'order_price' => $amount,
            'order_points' => $points,
            'vod_id' => $vod_id,
            'live_id' => $live_id,
            'order_status' => 0,
            'order_time' => time()
        ];
        
        $order = OrderModel::create($data);
        if ($order) {
            return $order;
        }
        return false;
    }

    public static function getPaymentConfig($payment_id = 0)
    {
        if ($payment_id > 0) {
            return PaymentModel::get($payment_id);
        }
        return PaymentModel::where('payment_is_default', 1)->find();
    }

    public static function processYipay($payment, $order)
    {
        $config = json_decode($payment['payment_config'], true);
        $params = [
            'pid' => $config['pid'],
            'type' => $config['pay_type'],
            'notify_url' => url('api/payment/yipay_notify', '', true, true),
            'return_url' => url('api/payment/yipay_return', '', true, true),
            'out_trade_no' => $order['order_code'],
            'name' => '订单支付',
            'money' => $order['order_price'],
            'sitename' => config('site.site_name')
        ];
        
        ksort($params);
        $sign_str = http_build_query($params);
        $sign = md5($sign_str . $config['key']);
        $params['sign'] = $sign;
        $params['sign_type'] = 'MD5';
        
        return $config['api_url'] . '?' . http_build_query($params);
    }

    public static function processPaypal($payment, $order)
    {
        $config = json_decode($payment['payment_config'], true);
        return [
            'business' => $config['business_email'],
            'cmd' => '_xclick',
            'item_name' => '订单支付',
            'item_number' => $order['order_code'],
            'amount' => $order['order_price'],
            'currency_code' => 'USD',
            'notify_url' => url('api/payment/paypal_notify', '', true, true),
            'return' => url('api/payment/paypal_return', '', true, true),
            'cancel_return' => url('api/payment/paypal_cancel', '', true, true)
        ];
    }

    public static function updateOrderStatus($order_code, $status, $pay_type = '')
    {
        $order = OrderModel::where('order_code', $order_code)->find();
        if (!$order || $order['order_status'] == 1) {
            return false;
        }

        Db::startTrans();
        try {
            OrderModel::update([
                'order_status' => 1,
                'order_pay_type' => $pay_type,
                'order_pay_time' => time()
            ], ['order_id' => $order['order_id']]);

            $user = \app\common\model\User::get($order['user_id']);
            if ($order['order_type'] == 'vip') {
                $new_end_time = max(time(), $user['user_end_time']);
                $new_end_time += 30 * 86400;
                \app\common\model\User::update([
                    'group_id' => 3,
                    'user_end_time' => $new_end_time
                ], ['user_id' => $order['user_id']]);
            } elseif ($order['order_type'] == 'points') {
                \app\common\model\User::update([
                    'user_points' => ['exp', 'user_points+' . $order['order_points']]
                ], ['user_id' => $order['user_id']]);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }
}
