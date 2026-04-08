-- ----------------------------
-- 添加直播表新字段
-- ----------------------------
ALTER TABLE `mac_live` ADD COLUMN `live_barrage_text` text NOT NULL COMMENT '弹幕文字，一行一个' AFTER `live_remark`;
ALTER TABLE `mac_live` ADD COLUMN `live_play_mode` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '播放模式：1=普通播放, 2=循环播放, 3=随机播放' AFTER `live_barrage_text`;
ALTER TABLE `mac_live` ADD COLUMN `live_charge_mode` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '收费模式：1=按时间收费, 2=按次数收费, 3=免费' AFTER `live_play_mode`;
ALTER TABLE `mac_live` ADD COLUMN `live_charge_seconds` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收费开始时间（秒）' AFTER `live_charge_mode`;
ALTER TABLE `mac_live` ADD COLUMN `live_charge_times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收费次数' AFTER `live_charge_seconds`;
ALTER TABLE `mac_live` ADD COLUMN `live_mp4_url` varchar(500) NOT NULL DEFAULT '' COMMENT 'MP4播放地址' AFTER `live_charge_times`;
ALTER TABLE `mac_live` ADD COLUMN `live_optimize` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '播放优化：1=开启, 0=关闭' AFTER `live_mp4_url`;
ALTER TABLE `mac_live` ADD COLUMN `live_hls_url` varchar(500) NOT NULL DEFAULT '' COMMENT 'HLS播放地址' AFTER `live_optimize`;
ALTER TABLE `mac_live` ADD COLUMN `live_flv_url` varchar(500) NOT NULL DEFAULT '' COMMENT 'FLV播放地址' AFTER `live_hls_url`;