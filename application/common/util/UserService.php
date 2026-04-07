<?php
namespace app\common\util;

use think\Cache;
use think\Db;
use app\common\model\User as UserModel;

class UserService
{
    protected static $cachePrefix = 'user_service_';
    protected static $cacheTime = 3600;

    public static function getUserInfo($user_id, $use_cache = true)
    {
        $cacheKey = self::$cachePrefix . 'info_' . $user_id;
        
        if ($use_cache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $user = UserModel::get($user_id);
        if ($user && $use_cache) {
            Cache::set($cacheKey, $user, self::$cacheTime);
        }

        return $user;
    }

    public static function updateUserInfo($user_id, $data)
    {
        Db::startTrans();
        try {
            $user = UserModel::get($user_id);
            if (!$user) {
                Db::rollback();
                return ['code' => 0, 'msg' => '用户不存在'];
            }

            UserModel::update($data, ['user_id' => $user_id]);
            self::clearUserCache($user_id);

            Db::commit();
            return ['code' => 1, 'msg' => '更新成功'];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '更新失败: ' . $e->getMessage()];
        }
    }

    public static function updateUserPoints($user_id, $points, $type = 'add')
    {
        Db::startTrans();
        try {
            $user = UserModel::get($user_id);
            if (!$user) {
                Db::rollback();
                return ['code' => 0, 'msg' => '用户不存在'];
            }

            if ($type == 'add') {
                $newPoints = $user['user_points'] + $points;
            } else {
                if ($user['user_points'] < $points) {
                    Db::rollback();
                    return ['code' => 0, 'msg' => '积分不足'];
                }
                $newPoints = $user['user_points'] - $points;
            }

            UserModel::update(['user_points' => $newPoints], ['user_id' => $user_id]);
            self::clearUserCache($user_id);

            Db::commit();
            return ['code' => 1, 'msg' => '积分更新成功', 'points' => $newPoints];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '积分更新失败: ' . $e->getMessage()];
        }
    }

    public static function updateUserVip($user_id, $days)
    {
        Db::startTrans();
        try {
            $user = UserModel::get($user_id);
            if (!$user) {
                Db::rollback();
                return ['code' => 0, 'msg' => '用户不存在'];
            }

            $newEndTime = max(time(), $user['user_end_time']);
            $newEndTime += $days * 86400;

            UserModel::update([
                'group_id' => 3,
                'user_end_time' => $newEndTime
            ], ['user_id' => $user_id]);

            self::clearUserCache($user_id);
            Db::commit();

            return [
                'code' => 1,
                'msg' => 'VIP更新成功',
                'end_time' => date('Y-m-d H:i:s', $newEndTime)
            ];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => 'VIP更新失败: ' . $e->getMessage()];
        }
    }

    public static function checkVipStatus($user_id)
    {
        $user = self::getUserInfo($user_id);
        if (!$user) {
            return ['code' => 0, 'msg' => '用户不存在', 'is_vip' => false];
        }

        $isVip = ($user['group_id'] == 3 && $user['user_end_time'] > time());

        return [
            'code' => 1,
            'is_vip' => $isVip,
            'end_time' => $user['user_end_time'] > 0 ? date('Y-m-d H:i:s', $user['user_end_time']) : ''
        ];
    }

    public static function getUserList($where = [], $page = 1, $limit = 20, $order = 'user_id desc')
    {
        $query = UserModel::where('1=1');
        
        if (!empty($where)) {
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    $query->where($key, $value[0], $value[1]);
                } else {
                    $query->where($key, $value);
                }
            }
        }

        $list = $query->order($order)->page($page, $limit)->select();
        $total = $query->count();

        return [
            'code' => 1,
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }

    public static function getUserStatistics($start_time = 0, $end_time = 0)
    {
        $query = UserModel::where('1=1');

        if ($start_time > 0) {
            $query->where('user_reg_time', '>=', $start_time);
        }

        if ($end_time > 0) {
            $query->where('user_reg_time', '<=', $end_time);
        }

        $totalUsers = $query->count();
        $totalPoints = $query->sum('user_points');
        $vipUsers = UserModel::where('group_id', 3)->where('user_end_time', '>', time())->count();

        return [
            'code' => 1,
            'data' => [
                'total_users' => $totalUsers,
                'total_points' => $totalPoints,
                'vip_users' => $vipUsers
            ]
        ];
    }

    public static function banUser($user_id, $reason = '', $ban_time = 0)
    {
        Db::startTrans();
        try {
            $user = UserModel::get($user_id);
            if (!$user) {
                Db::rollback();
                return ['code' => 0, 'msg' => '用户不存在'];
            }

            $updateData = [
                'user_status' => 0
            ];

            if ($reason) {
                $updateData['user_remark'] = $reason;
            }

            UserModel::update($updateData, ['user_id' => $user_id]);
            self::clearUserCache($user_id);

            Db::commit();
            return ['code' => 1, 'msg' => '用户封禁成功'];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '用户封禁失败: ' . $e->getMessage()];
        }
    }

    public static function unbanUser($user_id)
    {
        Db::startTrans();
        try {
            $user = UserModel::get($user_id);
            if (!$user) {
                Db::rollback();
                return ['code' => 0, 'msg' => '用户不存在'];
            }

            UserModel::update(['user_status' => 1], ['user_id' => $user_id]);
            self::clearUserCache($user_id);

            Db::commit();
            return ['code' => 1, 'msg' => '用户解封成功'];
        } catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => '用户解封失败: ' . $e->getMessage()];
        }
    }

    protected static function clearUserCache($user_id)
    {
        $patterns = [
            self::$cachePrefix . 'info_' . $user_id
        ];

        foreach ($patterns as $pattern) {
            Cache::clear($pattern);
        }
    }

    public static function clearCache($type = null)
    {
        if ($type) {
            Cache::clear(self::$cachePrefix . '*' . $type . '*');
        } else {
            Cache::clear(self::$cachePrefix . '*');
        }
        return ['code' => 1, 'msg' => '缓存清除成功'];
    }
}
