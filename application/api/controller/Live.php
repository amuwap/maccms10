<?php
namespace app\api\controller;

use think\Controller;
use app\common\util\LiveService;

class Live extends Controller
{
    public function interaction()
    {
        $live_id = input('get.live_id', 0);
        $type = input('get.type', 'view');

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::simulateViewerInteraction($live_id, $type);
        return json($result);
    }

    public function comments()
    {
        $live_id = input('get.live_id', 0);
        $limit = input('get.limit', 20);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $comments = LiveService::getFakeComments($live_id, $limit);
        return json(['code' => 1, 'data' => $comments]);
    }

    public function statistics()
    {
        $live_id = input('get.live_id', 0);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::getFakeLiveStatistics($live_id);
        return json($result);
    }

    public function stream()
    {
        $live_id = input('get.live_id', 0);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::getFakeLiveStreamUrl($live_id);
        return json($result);
    }

    public function create()
    {
        $data = input('post.');

        if (empty($data['live_name'])) {
            return json(['code' => 0, 'msg' => '请填写直播名称']);
        }

        if (empty($data['live_video_url'])) {
            return json(['code' => 0, 'msg' => '请填写视频地址']);
        }

        $result = LiveService::createFakeLive($data);
        return json($result);
    }

    public function start()
    {
        $live_id = input('post.live_id', 0);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::startFakeLive($live_id);
        return json($result);
    }

    public function stop()
    {
        $live_id = input('post.live_id', 0);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::stopFakeLive($live_id);
        return json($result);
    }

    public function list()
    {
        $page = input('get.page', 1);
        $limit = input('get.limit', 20);

        $result = LiveService::getFakeLives($page, $limit);
        return json($result);
    }

    public function detail()
    {
        $live_id = input('get.live_id', 0);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $live = LiveService::getLiveDetail($live_id);
        if (!$live) {
            return json(['code' => 0, 'msg' => '直播不存在']);
        }

        return json(['code' => 1, 'data' => $live]);
    }
}
