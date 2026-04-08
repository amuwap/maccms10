<?php
// 批量添加测试数据脚本
header('Content-Type: text/html; charset=utf-8');

echo '开始批量添加测试数据...<br>';

// 直接连接数据库
$pdo = new PDO('mysql:host=127.0.0.1;dbname=maccms10;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 执行SQL语句的函数
function executeSQL($pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// 批量添加分类数据
function addCategories($pdo) {
    echo '添加分类数据...<br>';
    
    $categories = [
        ['type_name' => '电影', 'type_pid' => 0, 'type_sort' => 1, 'type_status' => 1, 'type_en' => 'movie'],
        ['type_name' => '电视剧', 'type_pid' => 0, 'type_sort' => 2, 'type_status' => 1, 'type_en' => 'tv'],
        ['type_name' => '综艺', 'type_pid' => 0, 'type_sort' => 3, 'type_status' => 1, 'type_en' => 'variety'],
        ['type_name' => '动漫', 'type_pid' => 0, 'type_sort' => 4, 'type_status' => 1, 'type_en' => 'anime'],
        ['type_name' => '动作片', 'type_pid' => 1, 'type_sort' => 1, 'type_status' => 1, 'type_en' => 'action'],
        ['type_name' => '喜剧片', 'type_pid' => 1, 'type_sort' => 2, 'type_status' => 1, 'type_en' => 'comedy'],
        ['type_name' => '科幻片', 'type_pid' => 1, 'type_sort' => 3, 'type_status' => 1, 'type_en' => 'sci-fi'],
        ['type_name' => '悬疑片', 'type_pid' => 1, 'type_sort' => 4, 'type_status' => 1, 'type_en' => 'mystery'],
        ['type_name' => '国产剧', 'type_pid' => 2, 'type_sort' => 1, 'type_status' => 1, 'type_en' => 'china'],
        ['type_name' => '美剧', 'type_pid' => 2, 'type_sort' => 2, 'type_status' => 1, 'type_en' => 'us'],
        ['type_name' => '韩剧', 'type_pid' => 2, 'type_sort' => 3, 'type_status' => 1, 'type_en' => 'korea'],
        ['type_name' => '日剧', 'type_pid' => 2, 'type_sort' => 4, 'type_status' => 1, 'type_en' => 'japan'],
    ];
    
    $sql = "INSERT INTO mac_type (type_name, type_pid, type_sort, type_status, type_en, type_mid, type_tpl, type_tpl_list, type_tpl_detail, type_tpl_play, type_tpl_down) VALUES (?, ?, ?, ?, ?, 1, '', '', '', '', '')";
    
    foreach ($categories as $category) {
        try {
            $result = executeSQL($pdo, $sql, [
                $category['type_name'],
                $category['type_pid'],
                $category['type_sort'],
                $category['type_status'],
                $category['type_en']
            ]);
            echo '添加分类：' . $category['type_name'] . ' 成功<br>';
        } catch (Exception $e) {
            echo '添加分类：' . $category['type_name'] . ' 失败 - ' . $e->getMessage() . '<br>';
        }
    }
}

// 批量添加视频数据
function addVodData($pdo) {
    echo '添加视频数据...<br>';
    
    $vods = [
        [
            'vod_name' => '复仇者联盟4：终局之战',
            'vod_sub' => 'Avengers: Endgame',
            'type_id' => 1,
            'vod_area' => '美国',
            'vod_year' => '2019',
            'vod_lang' => '英语',
            'vod_pic' => 'https://example.com/avengers4.jpg',
            'vod_actor' => '小罗伯特·唐尼,克里斯·埃文斯,克里斯·海姆斯沃斯',
            'vod_director' => '安东尼·罗素,乔·罗素',
            'vod_content' => '《复仇者联盟4：终局之战》是漫威电影宇宙的第22部电影，也是漫威电影宇宙第三阶段的收官之作。影片讲述了在《复仇者联盟3：无限战争》的毁灭性事件之后，复仇者联盟的剩余成员与他们的盟友一起，试图扭转灭霸的所作所为，恢复宇宙的秩序。',
            'vod_remarks' => 'HD',
            'vod_status' => 1,
            'vod_hits' => 123456,
            'vod_score' => 9.2,
            'vod_play_from' => '百度云',
            'vod_play_server' => '默认',
            'vod_play_url' => ' Avengers4.mp4',
        ],
        [
            'vod_name' => '权力的游戏 第八季',
            'vod_sub' => 'Game of Thrones Season 8',
            'type_id' => 2,
            'vod_area' => '美国',
            'vod_year' => '2019',
            'vod_lang' => '英语',
            'vod_pic' => 'https://example.com/got8.jpg',
            'vod_actor' => '艾米莉亚·克拉克,基特·哈灵顿,彼特·丁拉基',
            'vod_director' => '大卫·努特尔,戴维·贝尼奥夫,D·B·威斯',
            'vod_content' => '《权力的游戏》第八季是美国HBO电视网制作推出的中世纪史诗奇幻剧，是《权力的游戏》系列电视剧的第八季，也是该系列的最终季。本季讲述了丹妮莉丝·坦格利安和琼恩·雪诺联合对抗夜王和他的尸鬼大军，同时争夺铁王座的故事。',
            'vod_remarks' => 'HD',
            'vod_status' => 1,
            'vod_hits' => 98765,
            'vod_score' => 8.5,
            'vod_play_from' => '百度云',
            'vod_play_server' => '默认',
            'vod_play_url' => ' GameOfThronesS8E1.mp4$GameOfThronesS8E2.mp4$GameOfThronesS8E3.mp4$GameOfThronesS8E4.mp4$GameOfThronesS8E5.mp4$GameOfThronesS8E6.mp4',
        ],
        [
            'vod_name' => '奔跑吧兄弟 第五季',
            'vod_sub' => 'Running Man China Season 5',
            'type_id' => 3,
            'vod_area' => '中国',
            'vod_year' => '2023',
            'vod_lang' => '国语',
            'vod_pic' => 'https://example.com/runningman5.jpg',
            'vod_actor' => '邓超,李晨,陈赫,郑恺,王祖蓝,鹿晗,迪丽热巴',
            'vod_director' => '岑俊义',
            'vod_content' => '《奔跑吧兄弟》是浙江卫视推出的大型户外竞技真人秀节目，第五季继续由原班人马打造，通过各种游戏和挑战，展现明星们的真实一面，带给观众欢乐和正能量。',
            'vod_remarks' => 'HD',
            'vod_status' => 0,
            'vod_hits' => 76543,
            'vod_score' => 7.8,
            'vod_play_from' => '百度云',
            'vod_play_server' => '默认',
            'vod_play_url' => ' RunningManS5E1.mp4$RunningManS5E2.mp4$RunningManS5E3.mp4',
        ],
        [
            'vod_name' => '进击的巨人 最终季',
            'vod_sub' => 'Attack on Titan Final Season',
            'type_id' => 4,
            'vod_area' => '日本',
            'vod_year' => '2022',
            'vod_lang' => '日语',
            'vod_pic' => 'https://example.com/aotfinal.jpg',
            'vod_actor' => '梶裕贵,石川由依,井上麻里奈',
            'vod_director' => '林祐一郎',
            'vod_content' => '《进击的巨人》最终季是根据谏山创原作漫画改编的电视动画，讲述了艾伦·耶格尔与调查兵团的成员们为了自由而战的故事，揭开了巨人的真相和世界的秘密。',
            'vod_remarks' => 'HD',
            'vod_status' => 1,
            'vod_hits' => 87654,
            'vod_score' => 9.5,
            'vod_play_from' => '百度云',
            'vod_play_server' => '默认',
            'vod_play_url' => ' AttackOnTitanFinalS1.mp4$AttackOnTitanFinalS2.mp4',
        ],
    ];
    
    $sql = "INSERT INTO mac_vod (vod_name, vod_sub, type_id, vod_area, vod_year, vod_lang, vod_pic, vod_actor, vod_director, vod_content, vod_remarks, vod_status, vod_hits, vod_score, vod_play_from, vod_play_server, vod_play_url, vod_down_url, vod_plot_name, vod_plot_detail, vod_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    foreach ($vods as $vod) {
        try {
            $result = executeSQL($pdo, $sql, [
                $vod['vod_name'],
                $vod['vod_sub'],
                $vod['type_id'],
                $vod['vod_area'],
                $vod['vod_year'],
                $vod['vod_lang'],
                $vod['vod_pic'],
                $vod['vod_actor'],
                $vod['vod_director'],
                $vod['vod_content'],
                $vod['vod_remarks'],
                $vod['vod_status'],
                $vod['vod_hits'],
                $vod['vod_score'],
                $vod['vod_play_from'],
                $vod['vod_play_server'],
                $vod['vod_play_url'],
                '',
                '',
                '',
                time()
            ]);
            echo '添加视频：' . $vod['vod_name'] . ' 成功<br>';
        } catch (Exception $e) {
            echo '添加视频：' . $vod['vod_name'] . ' 失败 - ' . $e->getMessage() . '<br>';
        }
    }
}

// 批量添加直播数据
function addLiveData($pdo) {
    echo '添加直播数据...<br>';
    
    $lives = [
        [
            'live_name' => '无人直播测试',
            'live_anchor' => '测试主播',
            'live_cover' => 'https://example.com/live1.jpg',
            'live_video_url' => 'https://example.com/live1.mp4',
            'live_status' => 1,
            'live_hits' => 54321,
            'live_start_time' => time(),
            'live_end_time' => time() + 86400,
        ],
        [
            'live_name' => '游戏直播测试',
            'live_anchor' => '游戏主播',
            'live_cover' => 'https://example.com/live2.jpg',
            'live_video_url' => 'https://example.com/live2.mp4',
            'live_status' => 1,
            'live_hits' => 43210,
            'live_start_time' => time(),
            'live_end_time' => time() + 86400,
        ],
    ];
    
    $sql = "INSERT INTO mac_live (live_name, live_anchor, live_cover, live_video_url, live_status, live_hits, live_start_time, live_end_time, live_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    foreach ($lives as $live) {
        try {
            $result = executeSQL($pdo, $sql, [
                $live['live_name'],
                $live['live_anchor'],
                $live['live_cover'],
                $live['live_video_url'],
                $live['live_status'],
                $live['live_hits'],
                $live['live_start_time'],
                $live['live_end_time'],
                time()
            ]);
            echo '添加直播：' . $live['live_name'] . ' 成功<br>';
        } catch (Exception $e) {
            echo '添加直播：' . $live['live_name'] . ' 失败 - ' . $e->getMessage() . '<br>';
        }
    }
}

// 批量添加用户数据
function addUserData($pdo) {
    echo '添加用户数据...<br>';
    
    $users = [
        [
            'user_name' => 'test1',
            'user_pwd' => md5('123456'),
            'user_email' => 'test1@example.com',
            'user_reg_time' => time(),
            'user_last_login_time' => time(),
            'user_status' => 1,
        ],
        [
            'user_name' => 'test2',
            'user_pwd' => md5('123456'),
            'user_email' => 'test2@example.com',
            'user_reg_time' => time(),
            'user_last_login_time' => time(),
            'user_status' => 1,
        ],
    ];
    
    $sql = "INSERT INTO mac_user (user_name, user_pwd, user_email, user_reg_time, user_last_login_time, user_status) VALUES (?, ?, ?, ?, ?, ?)";
    
    foreach ($users as $user) {
        try {
            $result = executeSQL($pdo, $sql, [
                $user['user_name'],
                $user['user_pwd'],
                $user['user_email'],
                $user['user_reg_time'],
                $user['user_last_login_time'],
                $user['user_status']
            ]);
            echo '添加用户：' . $user['user_name'] . ' 成功<br>';
        } catch (Exception $e) {
            echo '添加用户：' . $user['user_name'] . ' 失败 - ' . $e->getMessage() . '<br>';
        }
    }
}

// 执行数据添加
addCategories($pdo);
echo '<br>';
addVodData($pdo);
echo '<br>';
addLiveData($pdo);
echo '<br>';
addUserData($pdo);
echo '<br>';
echo '批量添加数据完成！<br>';

// 关闭数据库连接
$pdo = null;
