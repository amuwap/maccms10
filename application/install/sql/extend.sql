-- ----------------------------
-- Apple CMS 10 二次开发扩展表
-- ----------------------------

-- ----------------------------
-- Table structure for mac_ai_config - AI配置表
-- ----------------------------
DROP TABLE IF EXISTS `mac_ai_config`;
CREATE TABLE `mac_ai_config` (
  `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `config_name` varchar(100) NOT NULL DEFAULT '',
  `config_provider` varchar(50) NOT NULL DEFAULT 'openai',
  `config_api_key` varchar(255) NOT NULL DEFAULT '',
  `config_api_url` varchar(255) NOT NULL DEFAULT '',
  `config_model` varchar(100) NOT NULL DEFAULT 'gpt-3.5-turbo',
  `config_temperature` decimal(3,2) unsigned NOT NULL DEFAULT '0.70',
  `config_max_tokens` int(10) unsigned NOT NULL DEFAULT '2000',
  `config_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `config_sort` smallint(6) unsigned NOT NULL DEFAULT '0',
  `config_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`config_id`),
  KEY `config_provider` (`config_provider`),
  KEY `config_status` (`config_status`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mac_ai_task - AI任务表
-- ----------------------------
DROP TABLE IF EXISTS `mac_ai_task`;
CREATE TABLE `mac_ai_task` (
  `task_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `task_mid` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `task_rid` int(10) unsigned NOT NULL DEFAULT '0',
  `task_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `task_prompt` text NOT NULL,
  `task_result` mediumtext NOT NULL,
  `task_error` varchar(500) NOT NULL DEFAULT '',
  `task_time` int(10) unsigned NOT NULL DEFAULT '0',
  `task_time_start` int(10) unsigned NOT NULL DEFAULT '0',
  `task_time_end` int(10) unsigned NOT NULL DEFAULT '0',
  `task_config_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_id`),
  KEY `task_type` (`task_type`),
  KEY `task_mid` (`task_mid`),
  KEY `task_rid` (`task_rid`),
  KEY `task_status` (`task_status`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mac_live - 直播表
-- ----------------------------
DROP TABLE IF EXISTS `mac_live`;
CREATE TABLE `mac_live` (
  `live_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `live_name` varchar(255) NOT NULL DEFAULT '',
  `live_anchor` varchar(100) NOT NULL DEFAULT '',
  `live_cover` varchar(255) NOT NULL DEFAULT '',
  `live_video_url` varchar(500) NOT NULL DEFAULT '',
  `live_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `live_is_auto` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `live_start_time` int(10) unsigned NOT NULL DEFAULT '0',
  `live_end_time` int(10) unsigned NOT NULL DEFAULT '0',
  `live_price` smallint(6) unsigned NOT NULL DEFAULT '0',
  `live_free_seconds` int(10) unsigned NOT NULL DEFAULT '0',
  `live_hits` int(10) unsigned NOT NULL DEFAULT '0',
  `live_hits_day` int(10) unsigned NOT NULL DEFAULT '0',
  `live_hits_week` int(10) unsigned NOT NULL DEFAULT '0',
  `live_hits_month` int(10) unsigned NOT NULL DEFAULT '0',
  `live_online` int(10) unsigned NOT NULL DEFAULT '0',
  `live_sort` smallint(6) unsigned NOT NULL DEFAULT '0',
  `live_remark` varchar(255) NOT NULL DEFAULT '',
  `live_time` int(10) unsigned NOT NULL DEFAULT '0',
  `live_time_add` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`live_id`),
  KEY `live_status` (`live_status`),
  KEY `live_sort` (`live_sort`),
  KEY `live_time` (`live_time`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mac_live_gift - 直播礼物表
-- ----------------------------
DROP TABLE IF EXISTS `mac_live_gift`;
CREATE TABLE `mac_live_gift` (
  `gift_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gift_name` varchar(100) NOT NULL DEFAULT '',
  `gift_pic` varchar(255) NOT NULL DEFAULT '',
  `gift_price` int(10) unsigned NOT NULL DEFAULT '0',
  `gift_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `gift_animation` varchar(255) NOT NULL DEFAULT '',
  `gift_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `gift_sort` smallint(6) unsigned NOT NULL DEFAULT '0',
  `gift_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`gift_id`),
  KEY `gift_status` (`gift_status`),
  KEY `gift_sort` (`gift_sort`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mac_live_record - 直播观看记录和打赏记录
-- ----------------------------
DROP TABLE IF EXISTS `mac_live_record`;
CREATE TABLE `mac_live_record` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `record_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `live_id` int(10) unsigned NOT NULL DEFAULT '0',
  `gift_id` int(10) unsigned NOT NULL DEFAULT '0',
  `gift_num` int(10) unsigned NOT NULL DEFAULT '1',
  `record_points` int(10) unsigned NOT NULL DEFAULT '0',
  `record_time` int(10) unsigned NOT NULL DEFAULT '0',
  `record_duration` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`record_id`),
  KEY `record_type` (`record_type`),
  KEY `user_id` (`user_id`),
  KEY `live_id` (`live_id`),
  KEY `record_time` (`record_time`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mac_payment - 支付配置表
-- ----------------------------
DROP TABLE IF EXISTS `mac_payment`;
CREATE TABLE `mac_payment` (
  `pay_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pay_code` varchar(50) NOT NULL DEFAULT '',
  `pay_name` varchar(100) NOT NULL DEFAULT '',
  `pay_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `pay_config` text NOT NULL,
  `pay_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pay_sort` smallint(6) unsigned NOT NULL DEFAULT '0',
  `pay_logo` varchar(255) NOT NULL DEFAULT '',
  `pay_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pay_id`),
  KEY `pay_code` (`pay_code`),
  KEY `pay_status` (`pay_status`),
  KEY `pay_sort` (`pay_sort`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mac_hot_keywords - 热门关键词表
-- ----------------------------
DROP TABLE IF EXISTS `mac_hot_keywords`;
CREATE TABLE `mac_hot_keywords` (
  `kw_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kw_name` varchar(255) NOT NULL DEFAULT '',
  `kw_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `kw_hits` int(10) unsigned NOT NULL DEFAULT '0',
  `kw_sort` smallint(6) unsigned NOT NULL DEFAULT '0',
  `kw_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `kw_time` int(10) unsigned NOT NULL DEFAULT '0',
  `kw_source` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`kw_id`),
  KEY `kw_type` (`kw_type`),
  KEY `kw_status` (`kw_status`),
  KEY `kw_sort` (`kw_sort`),
  KEY `kw_hits` (`kw_hits`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mac_user_behavior - 用户行为表
-- ----------------------------
DROP TABLE IF EXISTS `mac_user_behavior`;
CREATE TABLE `mac_user_behavior` (
  `bh_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `bh_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `bh_mid` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `bh_rid` int(10) unsigned NOT NULL DEFAULT '0',
  `bh_sid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `bh_nid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `bh_progress` int(10) unsigned NOT NULL DEFAULT '0',
  `bh_time` int(10) unsigned NOT NULL DEFAULT '0',
  `bh_ip` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`bh_id`),
  KEY `user_id` (`user_id`),
  KEY `bh_type` (`bh_type`),
  KEY `bh_mid` (`bh_mid`),
  KEY `bh_rid` (`bh_rid`),
  KEY `bh_time` (`bh_time`),
  UNIQUE KEY `uk_user_behavior` (`user_id`, `bh_type`, `bh_mid`, `bh_rid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mac_play_error - 播放报错表
-- ----------------------------
DROP TABLE IF EXISTS `mac_play_error`;
CREATE TABLE `mac_play_error` (
  `error_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `vod_id` int(10) unsigned NOT NULL DEFAULT '0',
  `error_sid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `error_nid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `error_content` varchar(500) NOT NULL DEFAULT '',
  `error_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `error_reply` varchar(500) NOT NULL DEFAULT '',
  `error_reply_time` int(10) unsigned NOT NULL DEFAULT '0',
  `error_time` int(10) unsigned NOT NULL DEFAULT '0',
  `error_contact` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`error_id`),
  KEY `user_id` (`user_id`),
  KEY `vod_id` (`vod_id`),
  KEY `error_status` (`error_status`),
  KEY `error_time` (`error_time`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mac_request - 求片表
-- ----------------------------
DROP TABLE IF EXISTS `mac_request`;
CREATE TABLE `mac_request` (
  `req_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `req_name` varchar(255) NOT NULL DEFAULT '',
  `req_content` text NOT NULL,
  `req_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `req_reply` text NOT NULL,
  `req_reply_time` int(10) unsigned NOT NULL DEFAULT '0',
  `req_time` int(10) unsigned NOT NULL DEFAULT '0',
  `req_contact` varchar(100) NOT NULL DEFAULT '',
  `req_hits` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`req_id`),
  KEY `user_id` (`user_id`),
  KEY `req_status` (`req_status`),
  KEY `req_time` (`req_time`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mac_reward_log - 分享奖励日志
-- ----------------------------
DROP TABLE IF EXISTS `mac_reward_log`;
CREATE TABLE `mac_reward_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `log_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `log_points` int(10) unsigned NOT NULL DEFAULT '0',
  `log_related_id` int(10) unsigned NOT NULL DEFAULT '0',
  `log_from_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `log_time` int(10) unsigned NOT NULL DEFAULT '0',
  `log_remark` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `log_type` (`log_type`),
  KEY `log_time` (`log_time`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mac_template_config - 模板配置表
-- ----------------------------
DROP TABLE IF EXISTS `mac_template_config`;
CREATE TABLE `mac_template_config` (
  `tc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tc_template` varchar(50) NOT NULL DEFAULT '',
  `tc_name` varchar(100) NOT NULL DEFAULT '',
  `tc_key` varchar(100) NOT NULL DEFAULT '',
  `tc_value` text NOT NULL,
  `tc_type` varchar(20) NOT NULL DEFAULT 'text',
  `tc_options` text NOT NULL,
  `tc_group` varchar(50) NOT NULL DEFAULT '',
  `tc_sort` smallint(6) unsigned NOT NULL DEFAULT '0',
  `tc_desc` varchar(255) NOT NULL DEFAULT '',
  `tc_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tc_id`),
  KEY `tc_template` (`tc_template`),
  KEY `tc_key` (`tc_key`),
  KEY `tc_group` (`tc_group`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- 插入默认礼物数据
-- ----------------------------
INSERT INTO `mac_live_gift` VALUES 
('1', '鲜花', '', 1, 1, '', 1, 1, UNIX_TIMESTAMP()),
('2', '掌声', '', 2, 1, '', 1, 2, UNIX_TIMESTAMP()),
('3', '爱心', '', 5, 1, '', 1, 3, UNIX_TIMESTAMP()),
('4', '钻石', '', 10, 1, '', 1, 4, UNIX_TIMESTAMP()),
('5', '跑车', '', 50, 2, '', 1, 5, UNIX_TIMESTAMP()),
('6', '飞机', '', 100, 2, '', 1, 6, UNIX_TIMESTAMP()),
('7', '火箭', '', 500, 3, '', 1, 7, UNIX_TIMESTAMP()),
('8', '城堡', '', 1000, 3, '', 1, 8, UNIX_TIMESTAMP());
