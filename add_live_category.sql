-- ----------------------------
-- 添加直播分类字段
-- ----------------------------
ALTER TABLE `mac_live` ADD COLUMN `live_category` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '直播分类：1=普通直播, 2=无人直播' AFTER `live_remark`;

-- ----------------------------
-- 创建分类索引
-- ----------------------------
CREATE INDEX `live_category` ON `mac_live`(`live_category`);