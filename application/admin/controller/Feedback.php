<?php
namespace app\admin\controller;

use app\common\model\PlayError as PlayErrorModel;
use app\common\model\RequestFilm as RequestFilmModel;

class Feedback extends Base
{
    public function play_error()
    {
        $list = PlayErrorModel::order('error_id desc')->paginate($this->_pagesize);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        return $this->fetch();
    }

    public function reply_error()
    {
        $error_id = input('error_id/d');
        if ($this->request->isPost()) {
            $data = input('post.');
            $data['error_reply_time'] = time();
            $data['error_status'] = 1;
            $res = PlayErrorModel::update($data, ['error_id' => $error_id]);
            if ($res !== false) {
                return $this->success('回复成功', url('play_error'));
            } else {
                return $this->error('回复失败');
            }
        }
        $info = PlayErrorModel::get($error_id);
        $this->assign('info', $info);
        return $this->fetch();
    }

    public function del_error()
    {
        $error_id = input('error_id/d');
        $res = PlayErrorModel::destroy($error_id);
        if ($res) {
            return $this->success('删除成功', url('play_error'));
        } else {
            return $this->error('删除失败');
        }
    }

    public function request_film()
    {
        $list = RequestFilmModel::order('request_id desc')->paginate($this->_pagesize);
        $this->assign('list', $list);
        $this->assign('page', $list->render());
        return $this->fetch();
    }

    public function reply_request()
    {
        $request_id = input('request_id/d');
        if ($this->request->isPost()) {
            $data = input('post.');
            $data['request_reply_time'] = time();
            $data['request_status'] = 1;
            $res = RequestFilmModel::update($data, ['request_id' => $request_id]);
            if ($res !== false) {
                return $this->success('回复成功', url('request_film'));
            } else {
                return $this->error('回复失败');
            }
        }
        $info = RequestFilmModel::get($request_id);
        $this->assign('info', $info);
        return $this->fetch();
    }

    public function del_request()
    {
        $request_id = input('request_id/d');
        $res = RequestFilmModel::destroy($request_id);
        if ($res) {
            return $this->success('删除成功', url('request_film'));
        } else {
            return $this->error('删除失败');
        }
    }
}
