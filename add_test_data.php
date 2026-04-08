<?php
// 完善的测试数据添加脚本
header('Content-Type: text/html; charset=utf-8');

echo '<h1>开始添加测试数据...</h1>';

// 引入ThinkPHP核心文件
define('APP_PATH', __DIR__ . '/application/');
define('RUNTIME_PATH', __DIR__ . '/runtime/');
require __DIR__ . '/thinkphp/base.php';

// 获取数据库配置
$db_config = include APP_PATH . 'database.php';

try {
    // 连接数据库
    $pdo = new PDO(
        'mysql:host=' . $db_config['hostname'] . ';dbname=' . $db_config['database'] . ';charset=' . $db_config['charset'],
        $db_config['username'],
        $db_config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo '<p style="color:green;">数据库连接成功！</p>';
} catch (Exception $e) {
    die('<p style="color:red;">数据库连接失败：' . $e->getMessage() . '</p>');
}

// 执行SQL语句的函数
function executeSQL($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (Exception $e) {
        echo '<p style="color:red;">SQL执行失败：' . $e->getMessage() . '</p>';
        return false;
    }
}

// 清空表数据（可选）
function truncateTable($pdo, $table) {
    try {
        $pdo->exec("DELETE FROM $table");
        $pdo->exec("ALTER TABLE $table AUTO_INCREMENT = 1");
        echo "<p>清空表 $table 成功</p>";
    } catch (Exception $e) {
        echo "<p style=\"color:orange;\">清空表 $table 失败：" . $e->getMessage() . "</p>";
    }
}

// 添加分类数据
function addCategories($pdo) {
    echo '<h2>添加分类数据...</h2>';
    
    $categories = [
        ['type_name' => '电影', 'type_pid' => 0, 'type_sort' => 1, 'type_status' => 1, 'type_en' => 'movie', 'type_mid' => 1],
        ['type_name' => '电视剧', 'type_pid' => 0, 'type_sort' => 2, 'type_status' => 1, 'type_en' => 'tv', 'type_mid' => 1],
        ['type_name' => '综艺', 'type_pid' => 0, 'type_sort' => 3, 'type_status' => 1, 'type_en' => 'variety', 'type_mid' => 1],
        ['type_name' => '动漫', 'type_pid' => 0, 'type_sort' => 4, 'type_status' => 1, 'type_en' => 'anime', 'type_mid' => 1],
        ['type_name' => '动作片', 'type_pid' => 1, 'type_sort' => 1, 'type_status' => 1, 'type_en' => 'action', 'type_mid' => 1],
        ['type_name' => '喜剧片', 'type_pid' => 1, 'type_sort' => 2, 'type_status' => 1, 'type_en' => 'comedy', 'type_mid' => 1],
        ['type_name' => '科幻片', 'type_pid' => 1, 'type_sort' => 3, 'type_status' => 1, 'type_en' => 'scifi', 'type_mid' => 1],
        ['type_name' => '爱情片', 'type_pid' => 1, 'type_sort' => 4, 'type_status' => 1, 'type_en' => 'romance', 'type_mid' => 1],
        ['type_name' => '悬疑片', 'type_pid' => 1, 'type_sort' => 5, 'type_status' => 1, 'type_en' => 'mystery', 'type_mid' => 1],
        ['type_name' => '国产剧', 'type_pid' => 2, 'type_sort' => 1, 'type_status' => 1, 'type_en' => 'china', 'type_mid' => 1],
        ['type_name' => '美剧', 'type_pid' => 2, 'type_sort' => 2, 'type_status' => 1, 'type_en' => 'us', 'type_mid' => 1],
        ['type_name' => '韩剧', 'type_pid' => 2, 'type_sort' => 3, 'type_status' => 1, 'type_en' => 'korea', 'type_mid' => 1],
        ['type_name' => '日剧', 'type_pid' => 2, 'type_sort' => 4, 'type_status' => 1, 'type_en' => 'japan', 'type_mid' => 1],
    ];
    
    $sql = "INSERT INTO mac_type (type_name, type_pid, type_sort, type_status, type_en, type_mid, type_tpl, type_tpl_list, type_tpl_detail, type_tpl_play, type_tpl_down, type_addtime) VALUES (?, ?, ?, ?, ?, ?, '', '', '', '', '', ?)";
    
    foreach ($categories as $category) {
        $result = executeSQL($pdo, $sql, [
            $category['type_name'],
            $category['type_pid'],
            $category['type_sort'],
            $category['type_status'],
            $category['type_en'],
            $category['type_mid'],
            time()
        ]);
        if ($result) {
            echo '<p style="color:green;">添加分类：' . $category['type_name'] . ' 成功</p>';
        }
    }
}

// 添加视频数据
function addVodData($pdo) {
    echo '<h2>添加视频数据...</h2>';
    
    $vods = [
        [
            'vod_name' => '流浪地球2',
            'vod_sub' => 'The Wandering Earth II',
            'type_id' => 1,
            'type_id_1' => 7,
            'vod_area' => '中国大陆',
            'vod_year' => '2023',
            'vod_lang' => '国语',
            'vod_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=sci-fi%20movie%20poster%20wandering%20earth%20space%20station&image_size=portrait_4_3',
            'vod_actor' => '吴京,刘德华,李雪健,沙溢,宁理,王智,朱颜曼滋',
            'vod_director' => '郭帆',
            'vod_content' => '太阳即将毁灭，人类在地球表面建造出巨大的推进器，寻找新的家园。然而宇宙之路危机四伏，为了拯救地球，为了人类能在漫长的2500年后抵达新的家园，流浪地球时代的年轻人再次挺身而出。',
            'vod_remarks' => 'HD1080P',
            'vod_status' => 1,
            'vod_hits' => 156789,
            'vod_hits_day' => 5678,
            'vod_hits_week' => 34567,
            'vod_hits_month' => 98765,
            'vod_score' => 9.3,
            'vod_score_all' => 9300,
            'vod_score_num' => 1000,
            'vod_play_from' => '百度云$优酷',
            'vod_play_server' => '默认$默认',
            'vod_play_url' => '正片$https://example.com/liulangdiqiu2.mp4',
            'vod_down_url' => '',
            'vod_plot_name' => '第一集$第二集$第三集',
            'vod_plot_detail' => '太阳危机初现$月球危机爆发$地球开始流浪',
            'vod_time_add' => time(),
            'vod_time' => time(),
        ],
        [
            'vod_name' => '满江红',
            'vod_sub' => 'Full River Red',
            'type_id' => 1,
            'type_id_1' => 6,
            'vod_area' => '中国大陆',
            'vod_year' => '2023',
            'vod_lang' => '国语',
            'vod_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=chinese%20historical%20movie%20poster%20red%20theme%20ancient%20china&image_size=portrait_4_3',
            'vod_actor' => '沈腾,易烊千玺,张译,雷佳音,岳云鹏,王佳怡,潘斌龙',
            'vod_director' => '张艺谋',
            'vod_content' => '南宋绍兴年间，岳飞死后四年，秦桧率兵与金国会谈。会谈前夜，金国使者死在宰相驻地，所携密信不翼而飞。小兵张大与亲兵营副统领孙均机缘巧合被裹挟进这巨大阴谋之中。',
            'vod_remarks' => 'HD1080P',
            'vod_status' => 1,
            'vod_hits' => 145678,
            'vod_hits_day' => 4567,
            'vod_hits_week' => 23456,
            'vod_hits_month' => 87654,
            'vod_score' => 8.8,
            'vod_score_all' => 8800,
            'vod_score_num' => 1000,
            'vod_play_from' => '百度云',
            'vod_play_server' => '默认',
            'vod_play_url' => '正片$https://example.com/manjianghong.mp4',
            'vod_down_url' => '',
            'vod_plot_name' => '',
            'vod_plot_detail' => '',
            'vod_time_add' => time(),
            'vod_time' => time(),
        ],
        [
            'vod_name' => '狂飙',
            'vod_sub' => 'The Knockout',
            'type_id' => 2,
            'type_id_1' => 10,
            'vod_area' => '中国大陆',
            'vod_year' => '2023',
            'vod_lang' => '国语',
            'vod_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=chinese%20tv%20drama%20poster%20crime%20thriller%20dark%20theme&image_size=portrait_4_3',
            'vod_actor' => '张译,张颂文,李一桐,张志坚,吴刚,倪大红,韩童生',
            'vod_director' => '徐纪周',
            'vod_content' => '2000年，意气风发的刑警安欣与倍受欺负的鱼贩子高启强相识，而后随着高启强逐渐偏离正途，安欣意识到在京海市社会发展的背后正是以高家兄弟为首的黑恶势力暗流汹涌，两人分道扬镳并展开了长达20年的正邪较量。',
            'vod_remarks' => '全39集',
            'vod_status' => 1,
            'vod_hits' => 234567,
            'vod_hits_day' => 8901,
            'vod_hits_week' => 56789,
            'vod_hits_month' => 198765,
            'vod_score' => 9.6,
            'vod_score_all' => 9600,
            'vod_score_num' => 1000,
            'vod_play_from' => '百度云$腾讯视频',
            'vod_play_server' => '默认$默认',
            'vod_play_url' => '第1集$https://example.com/kuangbiao01.mp4$第2集$https://example.com/kuangbiao02.mp4$第3集$https://example.com/kuangbiao03.mp4$第4集$https://example.com/kuangbiao04.mp4$第5集$https://example.com/kuangbiao05.mp4',
            'vod_down_url' => '',
            'vod_plot_name' => '第1集$第2集$第3集',
            'vod_plot_detail' => '除夕夜的相遇$高启强的改变$旧厂街风云',
            'vod_time_add' => time(),
            'vod_time' => time(),
        ],
        [
            'vod_name' => '三体',
            'vod_sub' => 'Three-Body',
            'type_id' => 2,
            'type_id_1' => 10,
            'vod_area' => '中国大陆',
            'vod_year' => '2023',
            'vod_lang' => '国语',
            'vod_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=scifi%20tv%20series%20poster%20three%20body%20problem%20space%20dark&image_size=portrait_4_3',
            'vod_actor' => '张鲁一,于和伟,陈瑾,王子文,林永健,李小冉,王传君',
            'vod_director' => '杨磊',
            'vod_content' => '2007年，地球基础科学出现了异常的扰动，一时间科学界风雨飘飘，人心惶惶。离奇自杀的科学家，近乎神迹的倒计时，行事隐秘的科学边界，神秘莫测的《三体》游戏……纳米科学家汪淼被警官史强带到联合作战中心，在那里他们发现了一个震惊世界的秘密。',
            'vod_remarks' => '全30集',
            'vod_status' => 1,
            'vod_hits' => 187654,
            'vod_hits_day' => 6543,
            'vod_hits_week' => 45678,
            'vod_hits_month' => 156789,
            'vod_score' => 9.5,
            'vod_score_all' => 9500,
            'vod_score_num' => 1000,
            'vod_play_from' => '百度云',
            'vod_play_server' => '默认',
            'vod_play_url' => '第1集$https://example.com/santi01.mp4$第2集$https://example.com/santi02.mp4$第3集$https://example.com/santi03.mp4',
            'vod_down_url' => '',
            'vod_plot_name' => '第1集$第2集$第3集',
            'vod_plot_detail' => '科学边界$倒计时$三体游戏',
            'vod_time_add' => time(),
            'vod_time' => time(),
        ],
        [
            'vod_name' => '孤注一掷',
            'vod_sub' => 'No More Bets',
            'type_id' => 1,
            'type_id_1' => 9,
            'vod_area' => '中国大陆',
            'vod_year' => '2023',
            'vod_lang' => '国语',
            'vod_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=crime%20thriller%20movie%20poster%20gambling%20fraud%20dark%20tension&image_size=portrait_4_3',
            'vod_actor' => '张艺兴,金晨,咏梅,王传君,王大陆,周也,孙阳',
            'vod_director' => '申奥',
            'vod_content' => '电影取材自上万起真实诈骗案例，境外网络诈骗全产业链骇人内幕将在大银幕上首度被揭秘。程序员潘生、模特安娜被海外高薪招聘吸引，出国淘金，却意外落入境外诈骗工厂的陷阱。',
            'vod_remarks' => 'HD1080P',
            'vod_status' => 1,
            'vod_hits' => 176543,
            'vod_hits_day' => 5432,
            'vod_hits_week' => 34567,
            'vod_hits_month' => 145678,
            'vod_score' => 9.0,
            'vod_score_all' => 9000,
            'vod_score_num' => 1000,
            'vod_play_from' => '百度云',
            'vod_play_server' => '默认',
            'vod_play_url' => '正片$https://example.com/guzhuyizhi.mp4',
            'vod_down_url' => '',
            'vod_plot_name' => '',
            'vod_plot_detail' => '',
            'vod_time_add' => time(),
            'vod_time' => time(),
        ],
        [
            'vod_name' => '消失的她',
            'vod_sub' => 'Lost in the Stars',
            'type_id' => 1,
            'type_id_1' => 9,
            'vod_area' => '中国大陆',
            'vod_year' => '2023',
            'vod_lang' => '国语',
            'vod_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=mystery%20thriller%20movie%20poster%20missing%20woman%20suspense%20dark&image_size=portrait_4_3',
            'vod_actor' => '朱一龙,倪妮,文咏珊,杜江,黄子琪',
            'vod_director' => '崔睿,刘翔',
            'vod_content' => '丈夫何非带着妻子李木子去东南亚旅行，结果结婚周年旅行中，妻子离奇消失。当何非苦苦寻找未果之时，妻子却再次出现，可何非坚称眼前的神秘女人并非自己的妻子。随着金牌律师陈麦介入到这起离奇案件中，更多的谜团慢慢浮现。',
            'vod_remarks' => 'HD1080P',
            'vod_status' => 1,
            'vod_hits' => 198765,
            'vod_hits_day' => 7654,
            'vod_hits_week' => 45678,
            'vod_hits_month' => 167890,
            'vod_score' => 9.2,
            'vod_score_all' => 9200,
            'vod_score_num' => 1000,
            'vod_play_from' => '百度云',
            'vod_play_server' => '默认',
            'vod_play_url' => '正片$https://example.com/xiaoshideta.mp4',
            'vod_down_url' => '',
            'vod_plot_name' => '',
            'vod_plot_detail' => '',
            'vod_time_add' => time(),
            'vod_time' => time(),
        ],
        [
            'vod_name' => '热烈',
            'vod_sub' => 'One and Only',
            'type_id' => 1,
            'type_id_1' => 6,
            'vod_area' => '中国大陆',
            'vod_year' => '2023',
            'vod_lang' => '国语',
            'vod_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=dance%20movie%20poster%20street%20dance%20youth%20inspirational%20colorful&image_size=portrait_4_3',
            'vod_actor' => '黄渤,王一博,刘敏涛,岳云鹏,小沈阳,张子贤',
            'vod_director' => '大鹏',
            'vod_content' => '街舞老炮儿丁雷，偶遇卖艺少年陈烁，丁雷忽悠陈烁加入自己经营的舞团。舞团内高手如云，性格各异，与陈烁碰撞出不同的火花，笑料不断。陈烁热烈追梦，期待着上场的机会，却发现丁雷邀请他其实另有目的。',
            'vod_remarks' => 'HD1080P',
            'vod_status' => 1,
            'vod_hits' => 123456,
            'vod_hits_day' => 3456,
            'vod_hits_week' => 23456,
            'vod_hits_month' => 98765,
            'vod_score' => 8.5,
            'vod_score_all' => 8500,
            'vod_score_num' => 1000,
            'vod_play_from' => '百度云',
            'vod_play_server' => '默认',
            'vod_play_url' => '正片$https://example.com/relied.mp4',
            'vod_down_url' => '',
            'vod_plot_name' => '',
            'vod_plot_detail' => '',
            'vod_time_add' => time(),
            'vod_time' => time(),
        ],
        [
            'vod_name' => '去有风的地方',
            'vod_sub' => 'Meet Yourself',
            'type_id' => 2,
            'type_id_1' => 10,
            'vod_area' => '中国大陆',
            'vod_year' => '2023',
            'vod_lang' => '国语',
            'vod_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=romantic%20tv%20drama%20poster%20yunnan%20countryside%20peaceful%20warm%20scenery&image_size=portrait_4_3',
            'vod_actor' => '刘亦菲,李现,胡冰卿,牛骏峰,吴彦姝,董晴,范帅琦',
            'vod_director' => '丁梓光',
            'vod_content' => '许红豆因为闺蜜去世，生活和工作陷入低谷，她独自前往大理云苗村的"有风小院"休息调整。在那里，她认识了辞去高薪工作回乡创业的本地人谢之遥，还有一群从大城市过去的同龄人。在日常相处中，谢之遥感受到了许红豆的善良和认真，便邀请许红豆用她多年的酒店从业经验，帮助当地提升员工服务意识，为发展云苗村的文化旅游事业助力。',
            'vod_remarks' => '全40集',
            'vod_status' => 1,
            'vod_hits' => 167890,
            'vod_hits_day' => 4567,
            'vod_hits_week' => 34567,
            'vod_hits_month' => 134567,
            'vod_score' => 9.1,
            'vod_score_all' => 9100,
            'vod_score_num' => 1000,
            'vod_play_from' => '百度云',
            'vod_play_server' => '默认',
            'vod_play_url' => '第1集$https://example.com/youfengde01.mp4$第2集$https://example.com/youfengde02.mp4$第3集$https://example.com/youfengde03.mp4',
            'vod_down_url' => '',
            'vod_plot_name' => '第1集$第2集$第3集',
            'vod_plot_detail' => '来到大理$有风小院$相遇谢之遥',
            'vod_time_add' => time(),
            'vod_time' => time(),
        ],
    ];
    
    $sql = "INSERT INTO mac_vod (vod_name, vod_sub, type_id, type_id_1, vod_area, vod_year, vod_lang, vod_pic, vod_actor, vod_director, vod_content, vod_remarks, vod_status, vod_hits, vod_hits_day, vod_hits_week, vod_hits_month, vod_score, vod_score_all, vod_score_num, vod_play_from, vod_play_server, vod_play_url, vod_down_url, vod_plot_name, vod_plot_detail, vod_time_add, vod_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    foreach ($vods as $vod) {
        $result = executeSQL($pdo, $sql, [
            $vod['vod_name'],
            $vod['vod_sub'],
            $vod['type_id'],
            $vod['type_id_1'],
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
            $vod['vod_hits_day'],
            $vod['vod_hits_week'],
            $vod['vod_hits_month'],
            $vod['vod_score'],
            $vod['vod_score_all'],
            $vod['vod_score_num'],
            $vod['vod_play_from'],
            $vod['vod_play_server'],
            $vod['vod_play_url'],
            $vod['vod_down_url'],
            $vod['vod_plot_name'],
            $vod['vod_plot_detail'],
            $vod['vod_time_add'],
            $vod['vod_time']
        ]);
        if ($result) {
            echo '<p style="color:green;">添加视频：' . $vod['vod_name'] . ' 成功</p>';
        }
    }
}

// 添加文章分类
function addArtCategories($pdo) {
    echo '<h2>添加文章分类...</h2>';
    
    $categories = [
        ['type_name' => '资讯', 'type_pid' => 0, 'type_sort' => 1, 'type_status' => 1, 'type_en' => 'news', 'type_mid' => 2],
        ['type_name' => '影评', 'type_pid' => 0, 'type_sort' => 2, 'type_status' => 1, 'type_en' => 'review', 'type_mid' => 2],
        ['type_name' => '八卦', 'type_pid' => 0, 'type_sort' => 3, 'type_status' => 1, 'type_en' => 'gossip', 'type_mid' => 2],
    ];
    
    $sql = "INSERT INTO mac_type (type_name, type_pid, type_sort, type_status, type_en, type_mid, type_tpl, type_tpl_list, type_tpl_detail, type_tpl_play, type_tpl_down, type_addtime) VALUES (?, ?, ?, ?, ?, ?, '', '', '', '', '', ?)";
    
    foreach ($categories as $category) {
        $result = executeSQL($pdo, $sql, [
            $category['type_name'],
            $category['type_pid'],
            $category['type_sort'],
            $category['type_status'],
            $category['type_en'],
            $category['type_mid'],
            time()
        ]);
        if ($result) {
            echo '<p style="color:green;">添加文章分类：' . $category['type_name'] . ' 成功</p>';
        }
    }
}

// 添加文章数据
function addArtData($pdo) {
    echo '<h2>添加文章数据...</h2>';
    
    $arts = [
        [
            'art_title' => '2023年度电影盘点：国产电影的崛起之年',
            'art_sub' => '2023国产电影回顾',
            'type_id' => 14,
            'art_author' => '影视小编',
            'art_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=chinese%20movie%20industry%202023%20review%20banner%20cinema&image_size=landscape_16_9',
            'art_content' => '<p>2023年对于中国电影来说是不平凡的一年，多部高质量国产电影相继上映，取得了票房和口碑的双丰收。从科幻巨制《流浪地球2》到悬疑佳作《满江红》，从现实题材《孤注一掷》到温暖治愈的《去有风的地方》，国产电影在各个类型上都取得了突破。</p><p>这一年，观众重新回到电影院，中国电影市场展现出强大的复苏势头。多部影片票房突破10亿，证明了好内容始终是吸引观众的核心动力。</p>',
            'art_remarks' => '精选',
            'art_status' => 1,
            'art_hits' => 56789,
            'art_hits_day' => 2345,
            'art_hits_week' => 12345,
            'art_hits_month' => 45678,
            'art_time' => time(),
        ],
        [
            'art_title' => '《流浪地球2》深度解析：中国科幻电影的里程碑',
            'art_sub' => '流浪地球2影评',
            'type_id' => 15,
            'art_author' => '科幻迷',
            'art_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=wandering%20earth%202%20movie%20analysis%20sci-fi%20china&image_size=landscape_16_9',
            'art_content' => '<p>《流浪地球2》不仅仅是一部科幻电影，更是中国电影工业化的里程碑。影片通过宏大的视觉特效和深刻的人文思考，展现了中国科幻的独特魅力。</p><p>与好莱坞科幻片不同，《流浪地球2》强调集体主义和人类命运共同体的理念，展现了中国人对家园的深厚情感。影片中的每一个角色都有血有肉，他们的选择和牺牲构成了这部史诗般的作品。</p>',
            'art_remarks' => '推荐',
            'art_status' => 1,
            'art_hits' => 45678,
            'art_hits_day' => 1234,
            'art_hits_week' => 9876,
            'art_hits_month' => 34567,
            'art_time' => time(),
        ],
        [
            'art_title' => '张颂文因《狂飙》爆红：实力派演员的春天来了',
            'art_sub' => '张颂文走红',
            'type_id' => 16,
            'art_author' => '八卦君',
            'art_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=actor%20zhang%20songwen%20famous%20tv%20drama%20success&image_size=landscape_16_9',
            'art_content' => '<p>随着电视剧《狂飙》的热播，演员张颂文凭借高启强一角一夜爆红。这位入行二十多年的实力派演员，终于迎来了属于自己的高光时刻。</p><p>张颂文的走红不是偶然，而是多年积累的必然结果。他对表演的热爱和对角色的认真钻研，让他塑造的每一个角色都深入人心。高启强这个角色的复杂性和层次感，被张颂文演绎得淋漓尽致。</p>',
            'art_remarks' => '热门',
            'art_status' => 1,
            'art_hits' => 78901,
            'art_hits_day' => 3456,
            'art_hits_week' => 23456,
            'art_hits_month' => 67890,
            'art_time' => time(),
        ],
    ];
    
    $sql = "INSERT INTO mac_art (art_title, art_sub, type_id, art_author, art_pic, art_content, art_remarks, art_status, art_hits, art_hits_day, art_hits_week, art_hits_month, art_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    foreach ($arts as $art) {
        $result = executeSQL($pdo, $sql, [
            $art['art_title'],
            $art['art_sub'],
            $art['type_id'],
            $art['art_author'],
            $art['art_pic'],
            $art['art_content'],
            $art['art_remarks'],
            $art['art_status'],
            $art['art_hits'],
            $art['art_hits_day'],
            $art['art_hits_week'],
            $art['art_hits_month'],
            $art['art_time']
        ]);
        if ($result) {
            echo '<p style="color:green;">添加文章：' . $art['art_title'] . ' 成功</p>';
        }
    }
}

// 添加明星数据
function addActorData($pdo) {
    echo '<h2>添加明星数据...</h2>';
    
    $actors = [
        [
            'actor_name' => '吴京',
            'actor_en' => 'Wu Jing',
            'actor_sex' => 1,
            'actor_birthday' => '1974-04-03',
            'actor_height' => '175',
            'actor_weight' => '70',
            'actor_area' => '中国大陆',
            'actor_breed' => '白羊座',
            'actor_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=chinese%20actor%20wu%20jing%20portrait%20professional&image_size=portrait_4_3',
            'actor_content' => '吴京，1974年4月3日出生于北京，中国内地影视男演员、电影导演、编剧、出品人、国家一级演员。1989年进入北京市武术队。1994年获得全国武术比赛精英赛枪术、对练冠军。1995年出演个人首部电影《功夫小子闯情关》，从而进入演艺圈。',
            'actor_status' => 1,
            'actor_hits' => 89012,
            'actor_time' => time(),
        ],
        [
            'actor_name' => '刘亦菲',
            'actor_en' => 'Liu Yifei',
            'actor_sex' => 0,
            'actor_birthday' => '1987-08-25',
            'actor_height' => '170',
            'actor_weight' => '53',
            'actor_area' => '中国大陆',
            'actor_breed' => '处女座',
            'actor_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=chinese%20actress%20liu%20yifei%20portrait%20beautiful%20elegant&image_size=portrait_4_3',
            'actor_content' => '刘亦菲，1987年8月25日出生于湖北省武汉市，华语影视女演员、歌手，毕业于北京电影学院2002级表演系本科。2002年，因出演电视剧《金粉世家》中白秀珠一角踏入演艺圈。2003年，因主演武侠剧《天龙八部》崭露头角。',
            'actor_status' => 1,
            'actor_hits' => 98765,
            'actor_time' => time(),
        ],
        [
            'actor_name' => '张译',
            'actor_en' => 'Zhang Yi',
            'actor_sex' => 1,
            'actor_birthday' => '1978-02-17',
            'actor_height' => '178',
            'actor_weight' => '65',
            'actor_area' => '中国大陆',
            'actor_breed' => '水瓶座',
            'actor_pic' => 'https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=chinese%20actor%20zhang%20yi%20portrait%20professional&image_size=portrait_4_3',
            'actor_content' => '张译，1978年2月17日出生于黑龙江省哈尔滨市，中国内地男演员。1997年至2006年服役于北京军区政治部战友话剧团。2006年，主演军事励志题材电视剧《士兵突击》。2009年，主演抗战剧《我的团长我的团》。',
            'actor_status' => 1,
            'actor_hits' => 76543,
            'actor_time' => time(),
        ],
    ];
    
    $sql = "INSERT INTO mac_actor (actor_name, actor_en, actor_sex, actor_birthday, actor_height, actor_weight, actor_area, actor_breed, actor_pic, actor_content, actor_status, actor_hits, actor_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    foreach ($actors as $actor) {
        $result = executeSQL($pdo, $sql, [
            $actor['actor_name'],
            $actor['actor_en'],
            $actor['actor_sex'],
            $actor['actor_birthday'],
            $actor['actor_height'],
            $actor['actor_weight'],
            $actor['actor_area'],
            $actor['actor_breed'],
            $actor['actor_pic'],
            $actor['actor_content'],
            $actor['actor_status'],
            $actor['actor_hits'],
            $actor['actor_time']
        ]);
        if ($result) {
            echo '<p style="color:green;">添加明星：' . $actor['actor_name'] . ' 成功</p>';
        }
    }
}

// 添加用户数据
function addUserData($pdo) {
    echo '<h2>添加用户数据...</h2>';
    
    $users = [
        [
            'user_name' => 'testuser1',
            'user_pwd' => md5('123456'),
            'user_email' => 'test1@example.com',
            'user_nickname' => '影视爱好者',
            'user_points' => 1000,
            'user_status' => 1,
            'user_reg_time' => time() - 86400 * 30,
        ],
        [
            'user_name' => 'testuser2',
            'user_pwd' => md5('123456'),
            'user_email' => 'test2@example.com',
            'user_nickname' => '电影达人',
            'user_points' => 2500,
            'user_status' => 1,
            'user_reg_time' => time() - 86400 * 60,
        ],
        [
            'user_name' => 'testuser3',
            'user_pwd' => md5('123456'),
            'user_email' => 'test3@example.com',
            'user_nickname' => '追剧狂人',
            'user_points' => 500,
            'user_status' => 1,
            'user_reg_time' => time() - 86400 * 10,
        ],
    ];
    
    $sql = "INSERT INTO mac_user (user_name, user_pwd, user_email, user_nickname, user_points, user_status, user_reg_time, user_last_login_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    foreach ($users as $user) {
        $result = executeSQL($pdo, $sql, [
            $user['user_name'],
            $user['user_pwd'],
            $user['user_email'],
            $user['user_nickname'],
            $user['user_points'],
            $user['user_status'],
            $user['user_reg_time'],
            time()
        ]);
        if ($result) {
            echo '<p style="color:green;">添加用户：' . $user['user_name'] . ' 成功</p>';
        }
    }
}

// 添加评论数据
function addCommentData($pdo) {
    echo '<h2>添加评论数据...</h2>';
    
    $comments = [
        [
            'comment_type' => 1,
            'comment_mid' => 1,
            'comment_rid' => 1,
            'comment_pid' => 0,
            'user_id' => 1,
            'user_name' => 'testuser1',
            'comment_content' => '这部电影太震撼了！特效一流，剧情也很感人。强烈推荐！',
            'comment_up' => 128,
            'comment_down' => 5,
            'comment_status' => 1,
            'comment_time' => time() - 3600,
        ],
        [
            'comment_type' => 1,
            'comment_mid' => 1,
            'comment_rid' => 1,
            'comment_pid' => 0,
            'user_id' => 2,
            'user_name' => 'testuser2',
            'comment_content' => '中国科幻电影的里程碑！期待第三部！',
            'comment_up' => 256,
            'comment_down' => 3,
            'comment_status' => 1,
            'comment_time' => time() - 7200,
        ],
        [
            'comment_type' => 1,
            'comment_mid' => 1,
            'comment_rid' => 3,
            'comment_pid' => 0,
            'user_id' => 3,
            'user_name' => 'testuser3',
            'comment_content' => '高启强这个角色演得太好了！张颂文老师演技炸裂！',
            'comment_up' => 512,
            'comment_down' => 8,
            'comment_status' => 1,
            'comment_time' => time() - 10800,
        ],
    ];
    
    $sql = "INSERT INTO mac_comment (comment_type, comment_mid, comment_rid, comment_pid, user_id, user_name, comment_content, comment_up, comment_down, comment_status, comment_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    foreach ($comments as $comment) {
        $result = executeSQL($pdo, $sql, [
            $comment['comment_type'],
            $comment['comment_mid'],
            $comment['comment_rid'],
            $comment['comment_pid'],
            $comment['user_id'],
            $comment['user_name'],
            $comment['comment_content'],
            $comment['comment_up'],
            $comment['comment_down'],
            $comment['comment_status'],
            $comment['comment_time']
        ]);
        if ($result) {
            echo '<p style="color:green;">添加评论：' . mb_substr($comment['comment_content'], 0, 20) . '... 成功</p>';
        }
    }
}

// 添加友情链接
function addLinkData($pdo) {
    echo '<h2>添加友情链接...</h2>';
    
    $links = [
        ['link_name' => '豆瓣电影', 'link_url' => 'https://movie.douban.com', 'link_sort' => 1, 'link_status' => 1],
        ['link_name' => '时光网', 'link_url' => 'https://www.mtime.com', 'link_sort' => 2, 'link_status' => 1],
        ['link_name' => '猫眼电影', 'link_url' => 'https://maoyan.com', 'link_sort' => 3, 'link_status' => 1],
        ['link_name' => '腾讯视频', 'link_url' => 'https://v.qq.com', 'link_sort' => 4, 'link_status' => 1],
        ['link_name' => '爱奇艺', 'link_url' => 'https://www.iqiyi.com', 'link_sort' => 5, 'link_status' => 1],
    ];
    
    $sql = "INSERT INTO mac_link (link_name, link_url, link_sort, link_status, link_addtime) VALUES (?, ?, ?, ?, ?)";
    
    foreach ($links as $link) {
        $result = executeSQL($pdo, $sql, [
            $link['link_name'],
            $link['link_url'],
            $link['link_sort'],
            $link['link_status'],
            time()
        ]);
        if ($result) {
            echo '<p style="color:green;">添加友情链接：' . $link['link_name'] . ' 成功</p>';
        }
    }
}

// 执行所有数据添加
echo '<h1>========================================</h1>';
addCategories($pdo);
addArtCategories($pdo);
addVodData($pdo);
addArtData($pdo);
addActorData($pdo);
addUserData($pdo);
addCommentData($pdo);
addLinkData($pdo);

echo '<h1 style="color:green;text-align:center;">所有测试数据添加完成！</h1>';
echo '<p style="text-align:center;"><a href="index.php">点击访问首页</a></p>';

$pdo = null;
