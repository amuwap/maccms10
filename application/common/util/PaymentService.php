<?php
namespace app\common\util;

use think\Db;
use think\Log;
use think\Cache;
use app\common\model\Payment as PaymentModel;
use app\common\model\Order as OrderModel;

class PaymentService
{
    protected static $logPrefix = 'payment_';
    protected static $cachePrefix = 'payment_';

    public static function createOrder($user_id, $order_type, $amount, $points = 0, $vod_id = 0, $live_id = 0, $extra = [])
    {
        Db::startTrans();
        try {
            $order_code = self::generateOrderCode();
            
            $data = [
                'user_id' => $user_id,
                'order_code' => $order_code,
                'order_type' => $order_type,
                'order_price' => $amount,
                'order_points' => $points,
                'vod_id' => $vod_id,
                'live_id' => $live_id,
                'order_status' => 0,
                'order_time' => time(),
                'order_extra' => !empty($extra) ? json_encode($extra) : ''
            ];
            
            $order = OrderModel::create($data);
            
            if ($order) {
                Db::commit();
                self::log('创建订单', ['order_code' => $order_code, 'user_id' => $user_id]);
                return $order;
            }
            
            Db::rollback();
            return false;
        } catch (\Exception $e) {
            Db::rollback();
            self::log('创建订单失败', ['error' => $e->getMessage()], 'error');
            return false;
        }
    }

    protected static function generateOrderCode()
    {
        return 'ORD' . date('YmdHis') . rand(1000, 9999) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public static function getPaymentConfig($payment_id = 0, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'config_' . $payment_id;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $payment = null;
        if ($payment_id > 0) {
            $payment = PaymentModel::get($payment_id);
        } else {
            $payment = PaymentModel::where('payment_is_default', 1)->where('payment_status', 1)->find();
        }

        if ($payment && $use_cache) {
            Cache::set($cacheKey, $payment, 3600);
        }

        return $payment;
    }

    public static function getActivePayments()
    {
        return PaymentModel::where('payment_status', 1)->order('payment_sort asc')->select();
    }

    public static function processPayment($payment, $order, $params = [])
    {
        $payType = $payment['payment_type'];
        
        switch ($payType) {
            case 'yipay':
                return self::processYipay($payment, $order, $params);
            case 'paypal':
                return self::processPaypal($payment, $order, $params);
            case 'wechat':
                return self::processWechat($payment, $order, $params);
            case 'alipay':
                return self::processAlipay($payment, $order, $params);
            case 'usdt':
                return self::processUsdt($payment, $order, $params);
            default:
                return ['code' => 0, 'msg' => '不支持的支付方式'];
        }
    }

    public static function processYipay($payment, $order, $params = [])
    {
        $config = json_decode($payment['payment_config'], true);
        
        $params = [
            'pid' => $config['pid'],
            'type' => isset($params['pay_type']) ? $params['pay_type'] : $config['pay_type'],
            'notify_url' => url('api/payment/yipay_notify', '', true, true),
            'return_url' => url('api/payment/yipay_return', '', true, true),
            'out_trade_no' => $order['order_code'],
            'name' => isset($params['name']) ? $params['name'] : '订单支付',
            'money' => $order['order_price'],
            'sitename' => config('maccms.site_name')
        ];
        
        if (isset($params['client_ip'])) {
            $params['clientip'] = $params['client_ip'];
        }
        
        ksort($params);
        $sign_str = urldecode(http_build_query($params));
        $sign = md5($sign_str . $config['key']);
        $params['sign'] = $sign;
        $params['sign_type'] = 'MD5';
        
        self::log('易支付参数', ['order_code' => $order['order_code'], 'params' => $params]);
        
        return [
            'code' => 1,
            'type' => 'redirect',
            'url' => $config['api_url'] . '?' . http_build_query($params)
        ];
    }

    public static function processPaypal($payment, $order, $params = [])
    {
        $config = json_decode($payment['payment_config'], true);
        
        $paypalParams = [
            'business' => $config['business_email'],
            'cmd' => '_xclick',
            'item_name' => isset($params['name']) ? $params['name'] : '订单支付',
            'item_number' => $order['order_code'],
            'amount' => $order['order_price'],
            'currency_code' => isset($config['currency']) ? $config['currency'] : 'USD',
            'notify_url' => url('api/payment/paypal_notify', '', true, true),
            'return' => url('api/payment/paypal_return', '', true, true),
            'cancel_return' => url('api/payment/paypal_cancel', '', true, true),
            'custom' => $order['order_code'],
            'no_note' => 1,
            'no_shipping' => 1
        ];
        
        self::log('PayPal参数', ['order_code' => $order['order_code'], 'params' => $paypalParams]);
        
        $paypalUrl = isset($config['sandbox']) && $config['sandbox'] 
            ? 'https://www.sandbox.paypal.com/cgi-bin/webscr'
            : 'https://www.paypal.com/cgi-bin/webscr';
        
        return [
            'code' => 1,
            'type' => 'redirect',
            'url' => $paypalUrl . '?' . http_build_query($paypalParams)
        ];
    }

    public static function processWechat($payment, $order, $params = [])
    {
        $config = json_decode($payment['payment_config'], true);
        
        $orderData = [
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'nonce_str' => md5(uniqid(mt_rand(), true)),
            'body' => isset($params['name']) ? $params['name'] : '订单支付',
            'out_trade_no' => $order['order_code'],
            'total_fee' => intval($order['order_price'] * 100),
            'spbill_create_ip' => isset($params['client_ip']) ? $params['client_ip'] : request()->ip(),
            'notify_url' => url('api/payment/wechat_notify', '', true, true),
            'trade_type' => isset($params['trade_type']) ? $params['trade_type'] : 'NATIVE'
        ];
        
        if (isset($params['openid'])) {
            $orderData['openid'] = $params['openid'];
        }
        
        $orderData['sign'] = self::wechatSign($orderData, $config['api_key']);
        
        $xml = self::arrayToXml($orderData);
        
        self::log('微信支付参数', ['order_code' => $order['order_code'], 'xml' => $xml]);
        
        $response = self::curlPost($config['api_url'], $xml);
        $result = self::xmlToArray($response);
        
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            return [
                'code' => 1,
                'type' => 'qrcode',
                'code_url' => isset($result['code_url']) ? $result['code_url'] : '',
                'prepay_id' => isset($result['prepay_id']) ? $result['prepay_id'] : '',
                'qr_data' => $result
            ];
        }
        
        return [
            'code' => 0,
            'msg' => isset($result['return_msg']) ? $result['return_msg'] : '微信支付请求失败'
        ];
    }

    public static function processAlipay($payment, $order, $params = [])
    {
        $config = json_decode($payment['payment_config'], true);
        
        $orderData = [
            'app_id' => $config['app_id'],
            'method' => 'alipay.trade.page.pay',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => url('api/payment/alipay_notify', '', true, true),
            'return_url' => url('api/payment/alipay_return', '', true, true),
            'biz_content' => json_encode([
                'out_trade_no' => $order['order_code'],
                'product_code' => 'FAST_INSTANT_TRADE_PAY',
                'total_amount' => $order['order_price'],
                'subject' => isset($params['name']) ? $params['name'] : '订单支付'
            ], JSON_UNESCAPED_UNICODE)
        ];
        
        $orderData['sign'] = self::alipaySign($orderData, $config['private_key']);
        
        $payUrl = $config['gateway_url'] . '?' . http_build_query($orderData);
        
        self::log('支付宝参数', ['order_code' => $order['order_code'], 'url' => $payUrl]);
        
        return [
            'code' => 1,
            'type' => 'redirect',
            'url' => $payUrl
        ];
    }

    public static function processUsdt($payment, $order, $params = [])
    {
        $config = json_decode($payment['payment_config'], true);
        
        $orderData = [
            'order_code' => $order['order_code'],
            'amount' => $order['order_price'],
            'currency' => isset($config['currency']) ? $config['currency'] : 'USDT',
            'address' => $config['wallet_address'],
            'network' => isset($config['network']) ? $config['network'] : 'TRC20',
            'expire_time' => time() + 1800,
            'qr_url' => isset($config['qr_url']) ? $config['qr_url'] : ''
        ];
        
        $cacheKey = self::$cachePrefix . 'usdt_' . $order['order_code'];
        Cache::set($cacheKey, $orderData, 1800);
        
        self::log('USDT支付参数', ['order_code' => $order['order_code'], 'data' => $orderData]);
        
        return [
            'code' => 1,
            'type' => 'crypto',
            'data' => $orderData
        ];
    }

    public static function verifyYipayNotify($params, $payment)
    {
        $config = json_decode($payment['payment_config'], true);
        
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['sign_type']);
        
        ksort($params);
        $sign_str = urldecode(http_build_query($params));
        $verifySign = md5($sign_str . $config['key']);
        
        self::log('易支付回调验证', ['params' => $params, 'sign' => $sign, 'verify_sign' => $verifySign]);
        
        return $sign === $verifySign;
    }

    public static function verifyPaypalNotify($params, $payment)
    {
        $config = json_decode($payment['payment_config'], true);
        
        $validateParams = array_merge($params, ['cmd' => '_notify-validate']);
        
        $paypalUrl = isset($config['sandbox']) && $config['sandbox']
            ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr'
            : 'https://ipnpb.paypal.com/cgi-bin/webscr';
        
        $response = self::curlPost($paypalUrl, $validateParams);
        
        self::log('PayPal回调验证', ['params' => $params, 'response' => $response]);
        
        return $response === 'VERIFIED';
    }

    public static function verifyWechatNotify($xml, $payment)
    {
        $config = json_decode($payment['payment_config'], true);
        $data = self::xmlToArray($xml);
        
        if ($data['return_code'] != 'SUCCESS') {
            return false;
        }
        
        $sign = $data['sign'];
        unset($data['sign']);
        
        $verifySign = self::wechatSign($data, $config['api_key']);
        
        self::log('微信回调验证', ['data' => $data, 'sign' => $sign, 'verify_sign' => $verifySign]);
        
        return $sign === $verifySign;
    }

    public static function verifyAlipayNotify($params, $payment)
    {
        $config = json_decode($payment['payment_config'], true);
        
        $sign = $params['sign'];
        unset($params['sign']);
        unset($params['sign_type']);
        
        ksort($params);
        $sign_str = urldecode(http_build_query($params));
        
        $result = self::alipayVerifySign($sign_str, $sign, $config['public_key']);
        
        self::log('支付宝回调验证', ['params' => $params, 'result' => $result]);
        
        return $result;
    }

    public static function updateOrderStatus($order_code, $status, $pay_type = '', $transaction_id = '', $extra = [])
    {
        $order = OrderModel::where('order_code', $order_code)->find();
        
        if (!$order) {
            self::log('订单不存在', ['order_code' => $order_code], 'error');
            return ['code' => 0, 'msg' => '订单不存在'];
        }
        
        if ($order['order_status'] == 1) {
            self::log('订单已处理', ['order_code' => $order_code], 'warning');
            return ['code' => 1, 'msg' => '订单已处理'];
        }

        Db::startTrans();
        try {
            $updateData = [
                'order_status' => $status,
                'order_pay_time' => time()
            ];
            
            if ($pay_type) {
                $updateData['order_pay_type'] = $pay_type;
            }
            
            if ($transaction_id) {
                $updateData['order_transaction_id'] = $transaction_id;
            }
            
            if (!empty($extra)) {
                $updateData['order_pay_extra'] = json_encode($extra);
            }
            
            OrderModel::update($updateData, ['order_id' => $order['order_id']]);

            if ($status == 1) {
                $user = \app\common\model\User::get($order['user_id']);
                
                switch ($order['order_type']) {
                    case 'vip':
                        $days = isset($extra['days']) ? intval($extra['days']) : 30;
                        $new_end_time = max(time(), $user['user_end_time']);
                        $new_end_time += $days * 86400;
                        
                        \app\common\model\User::update([
                            'group_id' => 3,
                            'user_end_time' => $new_end_time
                        ], ['user_id' => $order['user_id']]);
                        break;
                        
                    case 'points':
                        \app\common\model\User::update([
                            'user_points' => ['exp', 'user_points+' . $order['order_points']]
                        ], ['user_id' => $order['user_id']]);
                        break;
                        
                    case 'video':
                        if ($order['vod_id']) {
                            \app\common\model\UserBehavior::create([
                                'user_id' => $order['user_id'],
                                'vod_id' => $order['vod_id'],
                                'behavior_type' => 'unlock',
                                'behavior_time' => time()
                            ]);
                        }
                        break;
                }
            }

            Db::commit();
            self::log('订单更新成功', ['order_code' => $order_code, 'status' => $status]);
            return ['code' => 1, 'msg' => '更新成功'];
        } catch (\Exception $e) {
            Db::rollback();
            self::log('订单更新失败', ['error' => $e->getMessage()], 'error');
            return ['code' => 0, 'msg' => '更新失败: ' . $e->getMessage()];
        }
    }

    protected static function wechatSign($data, $key)
    {
        ksort($data);
        $string = urldecode(http_build_query($data));
        $string = $string . '&key=' . $key;
        return strtoupper(md5($string));
    }

    protected static function alipaySign($data, $privateKey)
    {
        ksort($data);
        $string = urldecode(http_build_query($data));
        
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($privateKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        
        openssl_sign($string, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }

    protected static function alipayVerifySign($data, $sign, $publicKey)
    {
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($publicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        
        $result = openssl_verify($data, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256);
        return $result === 1;
    }

    protected static function arrayToXml($data)
    {
        $xml = '<xml>';
        foreach ($data as $key => $value) {
            $xml .= "<{$key}><![CDATA[{$value}]]></{$key}>";
        }
        $xml .= '</xml>';
        return $xml;
    }

    protected static function xmlToArray($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    protected static function curlPost($url, $data, $timeout = 30)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        if (is_string($data)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
        }
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }

    protected static function log($message, $context = [], $level = 'info')
    {
        $logMessage = $message . ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        
        switch ($level) {
            case 'error':
                Log::error(self::$logPrefix . $logMessage);
                break;
            case 'warning':
                Log::warning(self::$logPrefix . $logMessage);
                break;
            default:
                Log::info(self::$logPrefix . $logMessage);
        }
    }

    public static function clearCache()
    {
        Cache::clear(self::$cachePrefix . '*');
        return ['code' => 1, 'msg' => '缓存清除成功'];
    }

    public static function queryOrder($payment, $order_code)
    {
        $payType = $payment['payment_type'];
        
        switch ($payType) {
            case 'yipay':
                return self::queryYipayOrder($payment, $order_code);
            case 'wechat':
                return self::queryWechatOrder($payment, $order_code);
            case 'alipay':
                return self::queryAlipayOrder($payment, $order_code);
            default:
                return ['code' => 0, 'msg' => '不支持的订单查询方式'];
        }
    }

    public static function queryYipayOrder($payment, $order_code)
    {
        $config = json_decode($payment['payment_config'], true);
        
        $params = [
            'pid' => $config['pid'],
            'out_trade_no' => $order_code
        ];
        
        ksort($params);
        $sign_str = urldecode(http_build_query($params));
        $sign = md5($sign_str . $config['key']);
        $params['sign'] = $sign;
        $params['sign_type'] = 'MD5';
        
        $queryUrl = $config['api_url'] . 'query?' . http_build_query($params);
        $response = self::curlGet($queryUrl);
        $result = json_decode($response, true);
        
        self::log('易支付订单查询', ['order_code' => $order_code, 'result' => $result]);
        
        if (isset($result['code']) && $result['code'] == 1) {
            return ['code' => 1, 'status' => $result['status'], 'data' => $result];
        }
        
        return ['code' => 0, 'msg' => '订单查询失败', 'data' => $result];
    }

    public static function queryWechatOrder($payment, $order_code)
    {
        $config = json_decode($payment['payment_config'], true);
        
        $orderData = [
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'out_trade_no' => $order_code,
            'nonce_str' => md5(uniqid(mt_rand(), true))
        ];
        
        $orderData['sign'] = self::wechatSign($orderData, $config['api_key']);
        $xml = self::arrayToXml($orderData);
        
        $response = self::curlPost($config['api_query_url'], $xml);
        $result = self::xmlToArray($response);
        
        self::log('微信订单查询', ['order_code' => $order_code, 'result' => $result]);
        
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            return ['code' => 1, 'status' => $result['trade_state'], 'data' => $result];
        }
        
        return ['code' => 0, 'msg' => '订单查询失败', 'data' => $result];
    }

    public static function queryAlipayOrder($payment, $order_code)
    {
        $config = json_decode($payment['payment_config'], true);
        
        $orderData = [
            'app_id' => $config['app_id'],
            'method' => 'alipay.trade.query',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'biz_content' => json_encode([
                'out_trade_no' => $order_code
            ], JSON_UNESCAPED_UNICODE)
        ];
        
        $orderData['sign'] = self::alipaySign($orderData, $config['private_key']);
        $queryUrl = $config['gateway_url'] . '?' . http_build_query($orderData);
        $response = self::curlGet($queryUrl);
        $result = json_decode($response, true);
        
        self::log('支付宝订单查询', ['order_code' => $order_code, 'result' => $result]);
        
        if (isset($result['alipay_trade_query_response'])) {
            $tradeResponse = $result['alipay_trade_query_response'];
            if ($tradeResponse['code'] == '10000') {
                return ['code' => 1, 'status' => $tradeResponse['trade_status'], 'data' => $tradeResponse];
            }
        }
        
        return ['code' => 0, 'msg' => '订单查询失败', 'data' => $result];
    }

    public static function refund($payment, $order, $refund_amount = 0, $refund_reason = '')
    {
        $payType = $payment['payment_type'];
        
        switch ($payType) {
            case 'wechat':
                return self::wechatRefund($payment, $order, $refund_amount, $refund_reason);
            case 'alipay':
                return self::alipayRefund($payment, $order, $refund_amount, $refund_reason);
            default:
                return ['code' => 0, 'msg' => '不支持的退款方式'];
        }
    }

    public static function wechatRefund($payment, $order, $refund_amount = 0, $refund_reason = '')
    {
        $config = json_decode($payment['payment_config'], true);
        $refund_amount = $refund_amount > 0 ? $refund_amount : $order['order_price'];
        
        $orderData = [
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'nonce_str' => md5(uniqid(mt_rand(), true)),
            'out_trade_no' => $order['order_code'],
            'out_refund_no' => 'REF' . date('YmdHis') . mt_rand(1000, 9999),
            'total_fee' => intval($order['order_price'] * 100),
            'refund_fee' => intval($refund_amount * 100)
        ];
        
        if ($refund_reason) {
            $orderData['refund_desc'] = $refund_reason;
        }
        
        $orderData['sign'] = self::wechatSign($orderData, $config['api_key']);
        $xml = self::arrayToXml($orderData);
        
        $response = self::curlPost($config['api_refund_url'], $xml);
        $result = self::xmlToArray($response);
        
        self::log('微信退款', ['order_code' => $order['order_code'], 'result' => $result]);
        
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            return ['code' => 1, 'msg' => '退款成功', 'data' => $result];
        }
        
        return ['code' => 0, 'msg' => isset($result['return_msg']) ? $result['return_msg'] : '退款失败', 'data' => $result];
    }

    public static function alipayRefund($payment, $order, $refund_amount = 0, $refund_reason = '')
    {
        $config = json_decode($payment['payment_config'], true);
        $refund_amount = $refund_amount > 0 ? $refund_amount : $order['order_price'];
        
        $bizContent = [
            'out_trade_no' => $order['order_code'],
            'refund_amount' => $refund_amount
        ];
        
        if ($refund_reason) {
            $bizContent['refund_reason'] = $refund_reason;
        }
        
        $orderData = [
            'app_id' => $config['app_id'],
            'method' => 'alipay.trade.refund',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'biz_content' => json_encode($bizContent, JSON_UNESCAPED_UNICODE)
        ];
        
        $orderData['sign'] = self::alipaySign($orderData, $config['private_key']);
        $refundUrl = $config['gateway_url'] . '?' . http_build_query($orderData);
        $response = self::curlGet($refundUrl);
        $result = json_decode($response, true);
        
        self::log('支付宝退款', ['order_code' => $order['order_code'], 'result' => $result]);
        
        if (isset($result['alipay_trade_refund_response'])) {
            $refundResponse = $result['alipay_trade_refund_response'];
            if ($refundResponse['code'] == '10000') {
                return ['code' => 1, 'msg' => '退款成功', 'data' => $refundResponse];
            }
        }
        
        return ['code' => 0, 'msg' => '退款失败', 'data' => $result];
    }

    public static function pollOrderStatus($order, $max_attempts = 10, $interval = 3)
    {
        $payment = self::getPaymentConfig($order['payment_id']);
        if (!$payment) {
            return ['code' => 0, 'msg' => '支付配置不存在'];
        }
        
        for ($i = 0; $i < $max_attempts; $i++) {
            $result = self::queryOrder($payment, $order['order_code']);
            
            if ($result['code'] == 1) {
                $successStatuses = ['SUCCESS', 'TRADE_SUCCESS', 'TRADE_FINISHED', 1];
                if (in_array($result['status'], $successStatuses)) {
                    $updateResult = self::updateOrderStatus(
                        $order['order_code'],
                        1,
                        $payment['payment_type'],
                        isset($result['data']['transaction_id']) ? $result['data']['transaction_id'] : ''
                    );
                    return ['code' => 1, 'msg' => '支付成功', 'query_result' => $result, 'update_result' => $updateResult];
                }
            }
            
            if ($i < $max_attempts - 1) {
                sleep($interval);
            }
        }
        
        return ['code' => 0, 'msg' => '轮询超时，未检测到支付成功'];
    }

    public static function getOrderStatistics($user_id = 0, $start_time = 0, $end_time = 0)
    {
        $query = OrderModel::where('order_status', 1);
        
        if ($user_id > 0) {
            $query->where('user_id', $user_id);
        }
        
        if ($start_time > 0) {
            $query->where('order_time', '>=', $start_time);
        }
        
        if ($end_time > 0) {
            $query->where('order_time', '<=', $end_time);
        }
        
        $totalAmount = $query->sum('order_price');
        $totalCount = $query->count();
        $totalPoints = $query->sum('order_points');
        
        return [
            'code' => 1,
            'data' => [
                'total_amount' => floatval($totalAmount),
                'total_count' => intval($totalCount),
                'total_points' => intval($totalPoints)
            ]
        ];
    }

    protected static function curlGet($url, $timeout = 30)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
}
