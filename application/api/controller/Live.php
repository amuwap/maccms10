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

    public function gifts()
    {
        return json(LiveService::getVirtualGifts());
    }

    public function send_gift()
    {
        $live_id = input('post.live_id', 0);
        $gift_id = input('post.gift_id', 0);
        $user_id = input('post.user_id', 0);
        $count = input('post.count', 1);

        if (empty($live_id) || empty($gift_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::sendVirtualGift($live_id, $gift_id, $user_id, $count);
        return json($result);
    }

    public function get_gifts()
    {
        $live_id = input('get.live_id', 0);
        $limit = input('get.limit', 20);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $gifts = LiveService::getLiveGifts($live_id, $limit);
        return json(['code' => 1, 'data' => $gifts]);
    }

    public function send_danmaku()
    {
        $live_id = input('post.live_id', 0);
        $content = input('post.content', '');
        $user_id = input('post.user_id', 0);

        if (empty($live_id) || empty($content)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::sendDanmaku($live_id, $content, $user_id);
        return json($result);
    }

    public function get_danmaku()
    {
        $live_id = input('get.live_id', 0);
        $limit = input('get.limit', 50);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $danmakus = LiveService::getDanmakus($live_id, $limit);
        return json(['code' => 1, 'data' => $danmakus]);
    }

    public function auto_interaction()
    {
        $live_id = input('post.live_id', 0);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::simulateAutoInteraction($live_id);
        return json($result);
    }

    public function start_auto_task()
    {
        $live_id = input('post.live_id', 0);
        $interval = input('post.interval', 30);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::startAutoInteractionTask($live_id, $interval);
        return json($result);
    }

    public function stop_auto_task()
    {
        $live_id = input('post.live_id', 0);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::stopAutoInteractionTask($live_id);
        return json($result);
    }

    public function add_announcement()
    {
        $live_id = input('post.live_id', 0);
        $announcement = input('post.announcement', '');
        $delay = input('post.delay', 60);

        if (empty($live_id) || empty($announcement)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::addTimedAnnouncement($live_id, $announcement, $delay);
        return json($result);
    }

    public function get_announcements()
    {
        $live_id = input('get.live_id', 0);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::getAnnouncements($live_id);
        return json($result);
    }

    public function viewer_diversity()
    {
        $live_id = input('get.live_id', 0);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::getViewerDiversity($live_id);
        return json($result);
    }

    public function gift_ranking()
    {
        $live_id = input('get.live_id', 0);
        $limit = input('get.limit', 10);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $ranking = LiveService::getGiftRanking($live_id, $limit);
        return json(['code' => 1, 'data' => $ranking]);
    }

    public function custom_comment()
    {
        $live_id = input('get.live_id', 0);
        $context = input('get.context', '');

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $comment = LiveService::generateCustomComment($live_id, $context);
        return json(['code' => 1, 'data' => $comment]);
    }

    public function user_level()
    {
        $points = input('get.points', 0);
        $level = LiveService::getUserLevel($points);
        return json(['code' => 1, 'data' => $level]);
    }

    public function update_points()
    {
        $live_id = input('post.live_id', 0);
        $user_id = input('post.user_id', 0);
        $points = input('post.points', 0);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::updateUserPoints($live_id, $user_id, $points);
        return json($result);
    }

    public function lottery_prizes()
    {
        return json(LiveService::getLotteryPrizes());
    }

    public function draw_lottery()
    {
        $live_id = input('post.live_id', 0);
        $user_id = input('post.user_id', 0);

        if (empty($live_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::drawLottery($live_id, $user_id);
        return json($result);
    }

    public function start_pk()
    {
        $live_id_1 = input('post.live_id_1', 0);
        $live_id_2 = input('post.live_id_2', 0);
        $duration = input('post.duration', 300);

        if (empty($live_id_1) || empty($live_id_2)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::startPk($live_id_1, $live_id_2, $duration);
        return json($result);
    }

    public function pk_status()
    {
        $pk_id = input('get.pk_id', '');

        if (empty($pk_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::getPkStatus($pk_id);
        return json($result);
    }

    public function update_pk_score()
    {
        $pk_id = input('post.pk_id', '');
        $live_side = input('post.live_side', 1);
        $score = input('post.score', 1);

        if (empty($pk_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::updatePkScore($pk_id, $live_side, $score);
        return json($result);
    }

    public function send_combo_gift()
    {
        $live_id = input('post.live_id', 0);
        $gift_id = input('post.gift_id', 0);
        $user_id = input('post.user_id', 0);
        $count = input('post.count', 1);
        $combo_count = input('post.combo_count', 1);

        if (empty($live_id) || empty($gift_id)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $result = LiveService::sendComboGift($live_id, $gift_id, $user_id, $count, $combo_count);
        return json($result);
    }

    public function super_gifts()
    {
        return json(LiveService::getSuperGifts());
    }
}
