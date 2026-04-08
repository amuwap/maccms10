<?php
return [
    'frontend' => [
        'site_title' => '苹果CMS测试网站',
        'site_description' => '海量高清影视资源，畅享极致观影体验',
        'site_keywords' => '短视频,搞笑视频,视频分享,免费视频,在线视频,预告片',
        'btn_text' => [
            'play' => '播放',
            'download' => '下载',
            'collect' => '收藏',
            'follow' => '追剧',
            'like' => '点赞',
            'share' => '分享',
            'report' => '报错'
        ],
        'status_text' => [
            'vip' => 'VIP',
            'hot' => '热门',
            'new' => '最新',
            'recommend' => '推荐'
        ],
        'theme_color' => [
            'primary' => '#3b82f6',
            'secondary' => '#8b5cf6',
            'accent' => '#fbbf24',
            'success' => '#10b981',
            'danger' => '#ef4444',
            'warning' => '#f59e0b',
            'info' => '#06b6d4'
        ],
        'font' => [
            'family' => 'Microsoft YaHei, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'size' => [
                'base' => '16px',
                'heading1' => '32px',
                'heading2' => '24px',
                'heading3' => '20px'
            ]
        ],
        'layout' => [
            'container_width' => '1400px',
            'header_height' => '80px',
            'footer_height' => '300px',
            'section_spacing' => '32px',
            'card_spacing' => '24px'
        ],
        'features' => [
            'banner' => true,
            'category_nav' => true,
            'hot_recommend' => true,
            'latest_update' => true,
            'category_content' => true,
            'feature_section' => true,
            'pagination' => true,
            'search' => true,
            'user_menu' => true
        ],
        'homepage' => [
            'banner_count' => 5,
            'recommend_count' => 12,
            'latest_count' => 12,
            'category_count' => 8
        ],
        'vod_detail' => [
            'show_rating' => true,
            'show_meta' => true,
            'show_actions' => true,
            'show_plot' => true,
            'show_cast' => true,
            'show_related' => true
        ],
        'vod_play' => [
            'show_controls' => true,
            'show_episode_list' => true,
            'show_related' => true,
            'auto_play' => false
        ],
        'live' => [
            'show_category' => true,
            'show_stats' => true,
            'show_schedule' => true,
            'auto_play' => false,
            'auto_start' => false,
            'auto_start_time' => '00:00',
            'auto_end' => false,
            'auto_end_time' => '23:59',
            'auto_loop' => true,
            'danmaku' => [
                'enabled' => true,
                'content' => [
                    '精彩！',
                    '厉害',
                    '666',
                    '支持主播',
                    '太精彩了',
                    '继续加油',
                    '爱了爱了',
                    '主播好厉害',
                    '这个操作太秀了',
                    '期待更多内容'
                ],
                'interval' => 5
            ],
            'charging' => [
                'enabled' => false,
                'mode' => 'time', // time, watch, vip
                'start_time' => 0, // 开播后多少秒开始收费
                'price' => 0
            ],
            'stream_data' => [
                'enabled' => true,
                'stats_interval' => 5, // 数据统计间隔（秒）
                'max_online_users' => 1000, // 最大在线人数
                'bandwidth_limit' => 0, // 带宽限制（Mbps），0表示无限制
                'record' => [
                    'enabled' => false,
                    'duration' => 3600, // 录制时长（秒）
                    'quality' => '720p'
                ]
            ]
        ],
        'animations' => [
            'enabled' => true,
            'duration' => '0.3s',
            'easing' => 'ease-out'
        ],
        'responsive' => [
            'mobile_breakpoint' => '768px',
            'tablet_breakpoint' => '992px',
            'desktop_breakpoint' => '1200px'
        ],
        'api' => [
            'timeout' => 5000,
            'retry' => 3
        ],
        'cache' => [
            'enabled' => true,
            'duration' => 3600
        ],
        'lang' => [
            'zh' => [
                'welcome' => '欢迎来到{site_name}',
                'no_data' => '暂无数据',
                'loading' => '加载中...',
                'error' => '出错了，请稍后重试',
                'success' => '操作成功',
                'confirm' => '确认',
                'cancel' => '取消',
                'search_placeholder' => '搜索影片、演员、导演...',
                'page_not_found' => '页面不存在',
                'server_error' => '服务器错误',
                'access_denied' => '访问权限不足',
                'login_required' => '请先登录',
                'register_success' => '注册成功',
                'login_success' => '登录成功',
                'logout_success' => '退出成功',
                'collect_success' => '收藏成功',
                'collect_removed' => '取消收藏',
                'like_success' => '点赞成功',
                'share_success' => '分享成功',
                'report_success' => '举报成功',
                'follow_success' => '追剧成功',
                'follow_removed' => '取消追剧',
                'comment_success' => '评论成功',
                'comment_pending' => '评论待审核',
                'payment_success' => '支付成功',
                'payment_failed' => '支付失败',
                'vip_required' => '需要VIP权限',
                'points_required' => '积分不足',
                'episode_not_found' => '剧集不存在',
                'player_error' => '播放器错误',
                'network_error' => '网络错误',
                'loading_failed' => '加载失败',
                'try_again' => '重试',
                'back_home' => '返回首页',
                'view_more' => '查看更多',
                'latest_updates' => '最新更新',
                'hot_recommendations' => '热门推荐',
                'related_content' => '相关推荐',
                'episode_list' => '选集列表',
                'cast_info' => '演职人员',
                'director' => '导演',
                'actors' => '主演',
                'plot_summary' => '剧情简介',
                'episode_plot' => '分集剧情',
                'play_now' => '立即播放',
                'download_now' => '立即下载',
                'collect' => '收藏',
                'follow' => '追剧',
                'like' => '点赞',
                'share' => '分享',
                'report' => '报错',
                'year' => '年份',
                'area' => '地区',
                'language' => '语言',
                'status' => '状态',
                'type' => '类型',
                'views' => '浏览',
                'rating' => '评分',
                'update_time' => '更新时间',
                'play_count' => '播放次数',
                'comment_count' => '评论数',
                'sort_by' => '排序方式',
                'filter_by' => '筛选条件',
                'search_results' => '搜索结果',
                'no_results' => '没有找到相关内容',
                'page' => '页',
                'of' => '共',
                'total' => '条',
                'prev_page' => '上一页',
                'next_page' => '下一页',
                'first_page' => '首页',
                'last_page' => '末页'
            ]
        ]
    ],
    'app' => [
        'app_name' => '苹果CMS',
        'app_version' => '1.0.0',
        'update_desc' => '修复已知问题，优化用户体验',
        'api_url' => '',
        'app_key' => '',
        'features' => [
            'push_notification' => true,
            'offline_download' => true,
            'screen_cast' => true,
            'gesture_control' => true
        ],
        'theme' => [
            'dark_mode' => true,
            'auto_theme' => false,
            'primary_color' => '#3b82f6'
        ],
        'player' => [
            'default_quality' => '720p',
            'auto_play' => true,
            'remember_position' => true,
            'gesture_control' => true
        ],
        'download' => [
            'max_tasks' => 3,
            'auto_download' => false,
            'wifi_only' => true
        ]
    ],
    'filter' => [
        'enabled' => true,
        'options' => ['type', 'area', 'year', 'lang', 'state'],
        'method' => 'ajax',
        'ajax_delay' => 300,
        'show_count' => true
    ],
    'sort' => [
        'default' => 'time',
        'options' => ['time', 'hits', 'score', 'rand'],
        'smart' => true,
        'smart_weight' => [
            'hits' => 0.4,
            'score' => 0.3,
            'time' => 0.2,
            'user_behavior' => 0.1
        ]
    ]
];
