<?php
namespace app\admin\controller;

use app\common\model\Payment as PaymentModel;

class Payment extends Base
{
    public function index()
    {
        $list = PaymentModel::order('payment_id desc')->paginate($this->_pagesize);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        return $this->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $data['payment_create_time'] = time();
            $data['payment_update_time'] = time();
            $res = PaymentModel::create($data);
            if ($res) {
                return $this->success('添加成功', url('index'));
            } else {
                return $this->error('添加失败');
            }
        }
        return $this->fetch('info');
    }

    public function edit()
    {
        $payment_id = input('payment_id/d');
        if ($this->request->isPost()) {
            $data = input('post.');
            $data['payment_update_time'] = time();
            $res = PaymentModel::update($data, ['payment_id' => $payment_id]);
            if ($res !== false) {
                return $this->success('修改成功', url('index'));
            } else {
                return $this->error('修改失败');
            }
        }
        $info = PaymentModel::get($payment_id);
        $this->assign('info', $info);
        return $this->fetch('info');
    }

    public function del()
    {
        $payment_id = input('payment_id/d');
        $res = PaymentModel::destroy($payment_id);
        if ($res) {
            return $this->success('删除成功', url('index'));
        } else {
            return $this->error('删除失败');
        }
    }

    public function set_default()
    {
        $payment_id = input('payment_id/d');
        PaymentModel::where('payment_id', '>', 0)->update(['payment_is_default' => 0]);
        $res = PaymentModel::update(['payment_is_default' => 1], ['payment_id' => $payment_id]);
        if ($res !== false) {
            return $this->success('设置成功', url('index'));
        } else {
            return $this->error('设置失败');
        }
    }
}
