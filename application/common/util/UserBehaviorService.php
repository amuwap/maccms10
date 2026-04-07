<?php
namespace app\common\util;

use app\common\model\UserBehavior as UserBehaviorModel;

class UserBehaviorService
{
    public static function addPlayHistory($user_id, $vod_id, $sid = 0, $nid = 0, $play_time = 0)
    {
        $behavior = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'play',
            'vod_id' => $vod_id
        ])->find();

        $data = [
            'user_id' => $user_id,
            'behavior_type' => 'play',
            'vod_id' => $vod_id,
            'sid' => $sid,
            'nid' => $nid,
            'play_time' => $play_time,
            'behavior_time' => time()
        ];

        if ($behavior) {
            return UserBehaviorModel::update($data, ['behavior_id' => $behavior['behavior_id']]);
        } else {
            return UserBehaviorModel::create($data);
        }
    }

    public static function toggleFavorite($user_id, $vod_id)
    {
        $behavior = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'favorite',
            'vod_id' => $vod_id
        ])->find();

        if ($behavior) {
            return UserBehaviorModel::destroy($behavior['behavior_id']);
        } else {
            $data = [
                'user_id' => $user_id,
                'behavior_type' => 'favorite',
                'vod_id' => $vod_id,
                'behavior_time' => time()
            ];
            return UserBehaviorModel::create($data);
        }
    }

    public static function toggleFollow($user_id, $vod_id)
    {
        $behavior = UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'follow',
            'vod_id' => $vod_id
        ])->find();

        if ($behavior) {
            return UserBehaviorModel::destroy($behavior['behavior_id']);
        } else {
            $data = [
                'user_id' => $user_id,
                'behavior_type' => 'follow',
                'vod_id' => $vod_id,
                'behavior_time' => time()
            ];
            return UserBehaviorModel::create($data);
        }
    }

    public static function toggleLike($user_id, $vod_id, $art_id = 0)
    {
        $where = [
            'user_id' => $user_id,
            'behavior_type' => 'like'
        ];
        if ($vod_id > 0) {
            $where['vod_id'] = $vod_id;
        }
        if ($art_id > 0) {
            $where['art_id'] = $art_id;
        }

        $behavior = UserBehaviorModel::where($where)->find();

        if ($behavior) {
            return UserBehaviorModel::destroy($behavior['behavior_id']);
        } else {
            $data = [
                'user_id' => $user_id,
                'behavior_type' => 'like',
                'vod_id' => $vod_id,
                'art_id' => $art_id,
                'behavior_time' => time()
            ];
            return UserBehaviorModel::create($data);
        }
    }

    public static function getPlayHistory($user_id, $page = 1, $limit = 20)
    {
        return UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'play'
        ])->order('behavior_time desc')->page($page, $limit)->select();
    }

    public static function getFavorites($user_id, $page = 1, $limit = 20)
    {
        return UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'favorite'
        ])->order('behavior_time desc')->page($page, $limit)->select();
    }

    public static function getFollows($user_id, $page = 1, $limit = 20)
    {
        return UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'follow'
        ])->order('behavior_time desc')->page($page, $limit)->select();
    }

    public static function isFavorited($user_id, $vod_id)
    {
        return UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'favorite',
            'vod_id' => $vod_id
        ])->count() > 0;
    }

    public static function isFollowed($user_id, $vod_id)
    {
        return UserBehaviorModel::where([
            'user_id' => $user_id,
            'behavior_type' => 'follow',
            'vod_id' => $vod_id
        ])->count() > 0;
    }

    public static function isLiked($user_id, $vod_id = 0, $art_id = 0)
    {
        $where = [
            'user_id' => $user_id,
            'behavior_type' => 'like'
        ];
        if ($vod_id > 0) {
            $where['vod_id'] = $vod_id;
        }
        if ($art_id > 0) {
            $where['art_id'] = $art_id;
        }
        return UserBehaviorModel::where($where)->count() > 0;
    }
}
