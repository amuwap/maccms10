<?php
namespace app\api\controller;

use think\Controller;
use app\common\util\PaymentService;

class Payment extends Controller
{
    public function yipay_notify()
    {
        $data = input('post.');
        if (empty($data)) {
            $data = input('get.');
        }
        
        $out_trade_no = isset($data['out_trade_no']) ? $data['out_trade_no'] : '';
        $trade_status = isset($data['trade_status']) ? $data['trade_status'] : '';
        
        if ($trade_status == 'TRADE_SUCCESS') {
            $result = PaymentService::updateOrderStatus($out_trade_no, 1, 'yipay');
            if ($result) {
                return 'success';
            }
        }
        return 'fail';
    }

    public function yipay_return()
    {
        $out_trade_no = input('out_trade_no');
        $this->redirect(url('index/user/order'));
    }

    public function paypal_notify()
    {
        $data = input('post.');
        $item_number = isset($data['item_number']) ? $data['item_number'] : '';
        $payment_status = isset($data['payment_status']) ? $data['payment_status'] : '';
        
        if ($payment_status == 'Completed') {
            $result = PaymentService::updateOrderStatus($item_number, 1, 'paypal');
            if ($result) {
                return 'success';
            }
        }
        return 'fail';
    }

    public function paypal_return()
    {
        $this->redirect(url('index/user/order'));
    }

    public function paypal_cancel()
    {
        $this->error('支付已取消', url('index/user/index'));
    }
}
