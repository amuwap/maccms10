<?php
namespace app\admin\controller;

use app\common\model\AiConfig as AiConfigModel;

class Aiconfig extends Base
{
    public function index()
    {
        $list = AiConfigModel::order('config_id desc')->paginate($this->_pagesize);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        return $this->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $data['config_create_time'] = time();
            $data['config_update_time'] = time();
            $res = AiConfigModel::create($data);
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
        $config_id = input('config_id/d');
        if ($this->request->isPost()) {
            $data = input('post.');
            $data['config_update_time'] = time();
            $res = AiConfigModel::update($data, ['config_id' => $config_id]);
            if ($res !== false) {
                return $this->success('修改成功', url('index'));
            } else {
                return $this->error('修改失败');
            }
        }
        $info = AiConfigModel::get($config_id);
        $this->assign('info', $info);
        return $this->fetch('info');
    }

    public function del()
    {
        $config_id = input('config_id/d');
        $res = AiConfigModel::destroy($config_id);
        if ($res) {
            return $this->success('删除成功', url('index'));
        } else {
            return $this->error('删除失败');
        }
    }

    public function set_default()
    {
        $config_id = input('config_id/d');
        AiConfigModel::where('config_id', '>', 0)->update(['config_is_default' => 0]);
        $res = AiConfigModel::update(['config_is_default' => 1], ['config_id' => $config_id]);
        if ($res !== false) {
            return $this->success('设置成功', url('index'));
        } else {
            return $this->error('设置失败');
        }
    }
}
