-- ----------------------------
-- 新分类体系 - 参考优酷、爱奇艺、腾讯等大型影视网站分类
-- ----------------------------

-- 清空原有分类
TRUNCATE TABLE `mac_type`;

-- ----------------------------
-- 视频分类数据
-- ----------------------------

-- 视频大分类
INSERT INTO `mac_type` VALUES 
('1', '电影', 'dianying', '1', '1', '0', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '电影,电影大全,最新电影,好看的电影', '为您提供最新电影、好看的电影排行榜，免费在线观看各种类型电影', '电影', '', '{\"class\":\"剧情,喜剧,动作,爱情,科幻,恐怖,悬疑,犯罪,战争,动画,纪录片,短片,奇幻,冒险,武侠,古装,历史,战争,家庭,儿童,青春,励志,文艺,运动,歌舞,传记,古装,网络电影\",\"area\":\"中国大陆,中国香港,中国台湾,美国,韩国,日本,英国,法国,德国,泰国,印度,意大利,西班牙,俄罗斯,加拿大,澳大利亚,其他\",\"lang\":\"国语,粤语,英语,韩语,日语,法语,德语,泰语,其他\",\"year\":\"2025,2024,2023,2022,2021,2020,2019,2018,2017,更早\",\"star\":\"\",\"director\":\"\",\"state\":\"正片,预告片,花絮\",\"version\":\"高清版,蓝光版,720P,1080P,4K,其他\"}');

INSERT INTO `mac_type` VALUES 
('2', '电视剧', 'dianshiju', '2', '1', '0', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '电视剧,最新电视剧,好看的电视剧', '为您提供最新电视剧排行榜，韩国电视剧、香港TVB电视剧、好看的电视剧', '电视剧', '', '{\"class\":\"言情,古装,武侠,仙侠,玄幻,悬疑,犯罪,都市,家庭,偶像,青春,励志,历史,战争,军旅,农村,情景,喜剧,科幻,动画,儿童,其他\",\"area\":\"中国大陆,中国香港,中国台湾,韩国,日本,美国,英国,泰国,新加坡,其他\",\"lang\":\"国语,粤语,英语,韩语,日语,其他\",\"year\":\"2025,2024,2023,2022,2021,2020,2019,2018,更早\",\"star\":\"\",\"director\":\"\",\"state\":\"更新中,已完结,预告片\",\"version\":\"TV版,网络版,蓝光版,其他\"}');

INSERT INTO `mac_type` VALUES 
('3', '综艺', 'zongyi', '3', '1', '0', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '综艺,综艺节目,最新综艺,综艺大全', '为您提供最新综艺节目、好看的综艺，免费在线观看', '综艺', '', '{\"class\":\"真人秀,选秀,访谈,情感,音乐,舞蹈,美食,旅游,游戏,脱口秀,竞技,美食,其他\",\"area\":\"中国大陆,中国香港,中国台湾,韩国,日本,欧美,其他\",\"lang\":\"国语,粤语,英语,韩语,日语,其他\",\"year\":\"2025,2024,2023,2022,更早\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('4', '动漫', 'dongman', '4', '1', '0', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '动漫,动漫大全,最新动漫', '为您提供最新动漫、好看的动漫', '动漫', '', '{\"class\":\"热血,搞笑,恋爱,校园,科幻,冒险,悬疑,推理,动作,机战,运动,战争,历史,社会,原创,亲子,益智,其他\",\"area\":\"国产,日本,欧美,其他\",\"lang\":\"国语,日语,英语,其他\",\"year\":\"2025,2024,2023,更早\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"TV版,剧场版,OVA版,真人版\"}');

-- 电影子分类
INSERT INTO `mac_type` VALUES 
('5', '剧情片', 'juqingpian', '1', '1', '1', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '剧情片,最新剧情片', '最新剧情片', '剧情片', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('6', '喜剧片', 'xijupian', '2', '1', '1', '1', 'type.html', 'show.html', 'play.html', 'down.html', '喜剧片,最新喜剧片', '最新喜剧片', '喜剧片', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('7', '动作片', 'dongzuopian', '3', '1', '1', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '动作片,最新动作片', '最新动作片', '动作片', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('8', '爱情片', 'aiqingpian', '4', '1', '1', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '爱情片,最新爱情片', '最新爱情片', '爱情片', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('9', '科幻片', 'kehuanpian', '5', '1', '1', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '科幻片,最新科幻片', '最新科幻片', '科幻片', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('10', '恐怖片', 'kongbupian', '6', '1', '1', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '恐怖片,最新恐怖片', '最新恐怖片', '恐怖片', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('11', '悬疑片', 'xuanyipian', '7', '1', '1', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '悬疑片,最新悬疑片', '最新悬疑片', '悬疑片', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('12', '犯罪片', 'fanzuipian', '8', '1', '1', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '犯罪片,最新犯罪片', '最新犯罪片', '犯罪片', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('13', '战争片', 'zhanzhengpian', '9', '1', '1', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '战争片,最新战争片', '最新战争片', '战争片', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('14', '动画片', 'donghuapian', '10', '1', '1', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '动画片,最新动画片', '最新动画片', '动画片', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('15', '纪录片', 'jilupian', '11', '1', '1', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '纪录片,最新纪录片', '最新纪录片', '纪录片', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

-- 电视剧子分类
INSERT INTO `mac_type` VALUES 
('16', '国产剧', 'guochanju', '1', '1', '2', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '国产剧,最新国产剧', '最新国产剧', '国产剧', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('17', '港剧', 'gangtaiju', '2', '1', '2', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '港剧,最新港剧', '最新港剧', '港剧', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('18', '台剧', 'taiwanju', '3', '1', '2', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '台剧,最新台剧', '最新台剧', '台剧', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('19', '韩剧', 'hanguoju', '4', '1', '2', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '韩剧,最新韩剧', '最新韩剧', '韩剧', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('20', '日剧', 'rihanju', '5', '1', '2', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '日剧,最新日剧', '最新日剧', '日剧', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('21', '美剧', 'oumeiju', '6', '1', '2', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '美剧,最新美剧', '最新美剧', '美剧', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('22', '泰剧', 'taiguoju', '7', '1', '2', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '泰剧,最新泰剧', '最新泰剧', '泰剧', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('23', '新加坡剧', 'xinjiapoju', '8', '1', '2', '1', 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '新加坡剧,最新新加坡剧', '最新新加坡剧', '新加坡剧', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

-- ----------------------------
-- 文章分类
-- ----------------------------
INSERT INTO `mac_type` VALUES 
('24', '影评', 'yingping', '1', '2', '0', '1', 'type.html', 'show.html', 'detail.html', '', '', '影评,电影评论,电视剧评论', '影视评论文章', '影评', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('25', '电影观后感', 'dianyingguanhougan', '1', '2', '24', '1', 'type.html', 'show.html', 'detail.html', '', '', '电影观后感,最新电影观后感', '电影观后感', '电影观后感', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('26', '电视剧观后感', 'dianshijuquanhougan', '2', '2', '24', '1', 'type.html', 'show.html', 'detail.html', '', '', '电视剧观后感,最新电视剧观后感', '电视剧观后感', '电视剧观后感', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

-- ----------------------------
-- 其他分类
-- ----------------------------
INSERT INTO `mac_type` VALUES 
('27', '公告', 'gonggao', '1', '2', '0', '1', 'type.html', 'show.html', 'detail.html', '', '', '网站公告', '网站公告', '公告', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');

INSERT INTO `mac_type` VALUES 
('28', '资讯', 'zixun', '2', '2', '0', '1', 'type.html', 'show.html', 'detail.html', '', '', '影视资讯,娱乐资讯', '影视资讯', '资讯', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}');
