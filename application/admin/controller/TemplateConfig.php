<?php
namespace app\admin\controller;

use think\Db;
use app\common\model\TemplateConfig as TemplateConfigModel;

class TemplateConfig extends Base
{
    public function index()
    {
        $list = TemplateConfigModel::order('config_id desc')->paginate($this->_pagesize);
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
            $res = TemplateConfigModel::create($data);
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
            $res = TemplateConfigModel::update($data, ['config_id' => $config_id]);
            if ($res !== false) {
                return $this->success('修改成功', url('index'));
            } else {
                return $this->error('修改失败');
            }
        }
        $info = TemplateConfigModel::get($config_id);
        $this->assign('info', $info);
        return $this->fetch('info');
    }

    public function del()
    {
        $config_id = input('config_id/d');
        $res = TemplateConfigModel::destroy($config_id);
        if ($res) {
            return $this->success('删除成功', url('index'));
        } else {
            return $this->error('删除失败');
        }
    }

    public function set_default()
    {
        $config_id = input('config_id/d');
        TemplateConfigModel::where('config_id', '>', 0)->update(['config_is_default' => 0]);
        $res = TemplateConfigModel::update(['config_is_default' => 1], ['config_id' => $config_id]);
        if ($res !== false) {
            return $this->success('设置成功', url('index'));
        } else {
            return $this->error('设置失败');
        }
    }

    public function batch_save()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            Db::startTrans();
            try {
                foreach ($data['configs'] as $config) {
                    if (isset($config['config_id'])) {
                        TemplateConfigModel::update($config, ['config_id' => $config['config_id']]);
                    }
                }
                Db::commit();
                return $this->success('保存成功', url('index'));
            } catch (\Exception $e) {
                Db::rollback();
                return $this->error('保存失败: ' . $e->getMessage());
            }
        }
        return $this->error('非法访问');
    }
}
