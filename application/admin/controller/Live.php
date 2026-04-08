<?php
namespace app\admin\controller;

use app\common\model\Live as LiveModel;
use app\common\model\Gift as GiftModel;

class Live extends Base
{
    public function index()
    {
        $list = LiveModel::order('live_id desc')->paginate($this->_pagesize);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        return $this->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $data['live_create_time'] = time();
            $data['live_update_time'] = time();
            $res = LiveModel::create($data);
            if ($res) {
                return $this->success('添加成功', url('index'));
            } else {
                return $this->error('添加失败');
            }
        }
        $this->assign('categories', $this->getLiveCategories());
        return $this->fetch('info');
    }

    public function edit()
    {
        $live_id = input('live_id/d');
        if ($this->request->isPost()) {
            $data = input('post.');
            $data['live_update_time'] = time();
            $res = LiveModel::update($data, ['live_id' => $live_id]);
            if ($res !== false) {
                return $this->success('修改成功', url('index'));
            } else {
                return $this->error('修改失败');
            }
        }
        $info = LiveModel::get($live_id);
        $this->assign('info', $info);
        $this->assign('categories', $this->getLiveCategories());
        return $this->fetch('info');
    }

    private function getLiveCategories()
    {
        return [
            '' => '请选择分类',
            'movie' => '电影',
            'tv' => '电视剧',
            'variety' => '综艺',
            'anime' => '动漫',
            'sports' => '体育',
            'music' => '音乐',
            'game' => '游戏',
            'education' => '教育',
            'news' => '新闻',
            'other' => '其他'
        ];
    }

    public function del()
    {
        $live_id = input('live_id/d');
        $res = LiveModel::destroy($live_id);
        if ($res) {
            return $this->success('删除成功', url('index'));
        } else {
            return $this->error('删除失败');
        }
    }

    public function start_live()
    {
        $live_id = input('live_id/d');
        $res = LiveModel::update(['live_status' => 1], ['live_id' => $live_id]);
        if ($res !== false) {
            return $this->success('开播成功', url('index'));
        } else {
            return $this->error('开播失败');
        }
    }

    public function stop_live()
    {
        $live_id = input('live_id/d');
        $res = LiveModel::update(['live_status' => 0], ['live_id' => $live_id]);
        if ($res !== false) {
            return $this->success('下播成功', url('index'));
        } else {
            return $this->error('下播失败');
        }
    }

    public function gifts()
    {
        $list = GiftModel::order('gift_id desc')->paginate($this->_pagesize);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        return $this->fetch();
    }

    public function add_gift()
    {
        if ($this->request->isPost()) {
            $data = input('post.');
            $data['gift_create_time'] = time();
            $res = GiftModel::create($data);
            if ($res) {
                return $this->success('添加成功', url('gifts'));
            } else {
                return $this->error('添加失败');
            }
        }
        return $this->fetch('gift_info');
    }

    public function edit_gift()
    {
        $gift_id = input('gift_id/d');
        if ($this->request->isPost()) {
            $data = input('post.');
            $res = GiftModel::update($data, ['gift_id' => $gift_id]);
            if ($res !== false) {
                return $this->success('修改成功', url('gifts'));
            } else {
                return $this->error('修改失败');
            }
        }
        $info = GiftModel::get($gift_id);
        $this->assign('info', $info);
        return $this->fetch('gift_info');
    }

    public function del_gift()
    {
        $gift_id = input('gift_id/d');
        $res = GiftModel::destroy($gift_id);
        if ($res) {
            return $this->success('删除成功', url('gifts'));
        } else {
            return $this->error('删除失败');
        }
    }
}
