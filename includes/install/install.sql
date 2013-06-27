-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1
-- 生成日期: 2013 年 06 月 27 日 03:07
-- 服务器版本: 5.5.27
-- PHP 版本: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- 数据库: `qidimission`
--

-- --------------------------------------------------------

--
-- 表的结构 `core_ip`
--

CREATE TABLE IF NOT EXISTS `core_ip` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ip_addr` varchar(39) COLLATE utf8_bin NOT NULL COMMENT '地址',
  `ip_ban` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否拉黑',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `core_ip`
--

INSERT INTO `core_ip` (`id`, `ip_addr`, `ip_ban`) VALUES
(1, '::1', 0);

-- --------------------------------------------------------

--
-- 表的结构 `core_log`
--

CREATE TABLE IF NOT EXISTS `core_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `log_ip` varchar(39) COLLATE utf8_bin NOT NULL COMMENT 'Ip地址',
  `log_message` text COLLATE utf8_bin NOT NULL COMMENT '消息',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `core_log`
--

INSERT INTO `core_log` (`id`, `log_date`, `log_ip`, `log_message`) VALUES
(1, '2013-06-27 01:02:21', '::1', 0x646f5f6c6f67696e2e706870202d3e206c6f67696e20737563636573732e);

-- --------------------------------------------------------

--
-- 表的结构 `mission_review`
--

CREATE TABLE IF NOT EXISTS `mission_review` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `review_word` text COLLATE utf8_bin NOT NULL COMMENT '单词',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `sys_configs`
--

CREATE TABLE IF NOT EXISTS `sys_configs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `config_name` varchar(300) COLLATE utf8_bin NOT NULL COMMENT '配置名称',
  `config_value` text COLLATE utf8_bin COMMENT '配置值',
  `config_default` text COLLATE utf8_bin COMMENT '默认值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=12 ;

--
-- 转存表中的数据 `sys_configs`
--

INSERT INTO `sys_configs` (`id`, `config_name`, `config_value`, `config_default`) VALUES
(1, 'WEB_TITLE', 0xe590afe8bfaae88bb1e8afade69599e882b2e8afbee5a082, 0xe590afe8bfaae88bb1e8afade69599e882b2e8afbee5a082),
(2, 'PAGE_META_DESCRIPTION', 0x666f746f6d78712ce4b8aae4baba2ce4b8ade5bf83, ''),
(3, 'PAGE_META_AUTHOR', 0x666f746f6d7871, 0x666f746f6d7871),
(4, 'USER_TIMEOUT', 0x33363031, 0x33363030),
(5, 'WEB_ON', 0x31, 0x31),
(6, 'WEB_URL', 0x687474703a2f2f6c6f63616c686f73742f716964696d697373696f6e, 0x687474703a2f2f6c6f63616c686f73742f716964696d697373696f6e),
(7, 'LAST_MISSION_ID', 0x3536, 0x30),
(8, 'MISSION_REVIEW_1', 0x35362c3530, ''),
(9, 'MISSION_REVIEW_2', '', ''),
(10, 'MISSION_REVIEW_3', '', ''),
(11, 'MISSION_REVIEW_4', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `sys_posts`
--

CREATE TABLE IF NOT EXISTS `sys_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `post_title` varchar(300) COLLATE utf8_bin DEFAULT NULL COMMENT '标题',
  `post_content` longtext COLLATE utf8_bin COMMENT '内容',
  `post_date` datetime NOT NULL COMMENT '创建时间',
  `post_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '修改时间',
  `post_ip` bigint(20) unsigned DEFAULT NULL COMMENT 'IP ID',
  `post_type` varchar(300) COLLATE utf8_bin NOT NULL COMMENT '内容类型标识',
  `post_order` bigint(20) NOT NULL DEFAULT '0' COMMENT '排序',
  `post_parent` bigint(20) unsigned NOT NULL COMMENT '上一级ID',
  `post_user` bigint(20) unsigned DEFAULT NULL COMMENT '用户ID',
  `post_password` char(41) COLLATE utf8_bin DEFAULT NULL COMMENT '访问密码',
  `post_name` varchar(300) COLLATE utf8_bin DEFAULT NULL COMMENT '媒体文件名称',
  `post_url` varchar(500) COLLATE utf8_bin DEFAULT NULL COMMENT '多媒体文件路径',
  `post_status` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT 'public' COMMENT '发布状态',
  `post_meta` varchar(300) COLLATE utf8_bin DEFAULT NULL COMMENT '头信息',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_2` (`id`),
  KEY `post_ip` (`post_ip`),
  KEY `post_parent` (`post_parent`),
  KEY `post_user` (`post_user`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=3 ;

--
-- 转存表中的数据 `sys_posts`
--

INSERT INTO `sys_posts` (`id`, `post_title`, `post_content`, `post_date`, `post_modified`, `post_ip`, `post_type`, `post_order`, `post_parent`, `post_user`, `post_password`, `post_name`, `post_url`, `post_status`, `post_meta`) VALUES
(1, '第1课', '', '2013-06-27 09:02:32', '2013-06-27 01:02:32', 1, 'class', 1, 0, 1, NULL, '', '', 'public', ''),
(2, '第1单元', '', '2013-06-27 09:02:34', '2013-06-27 01:02:34', 1, 'unit', 2, 1, 1, NULL, '', '', 'public', '');

-- --------------------------------------------------------

--
-- 表的结构 `sys_user`
--

CREATE TABLE IF NOT EXISTS `sys_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_username` varchar(20) COLLATE utf8_bin NOT NULL COMMENT '用户名',
  `user_password` char(41) COLLATE utf8_bin NOT NULL COMMENT '密码',
  `user_email` varchar(255) COLLATE utf8_bin NOT NULL COMMENT '邮箱',
  `user_name` varchar(60) COLLATE utf8_bin NOT NULL COMMENT '昵称',
  `user_group` bigint(20) unsigned NOT NULL COMMENT '用户组',
  `user_date` datetime NOT NULL COMMENT '创建时间',
  `user_login_date` datetime NOT NULL COMMENT '上一次登录时间',
  `user_ip` bigint(20) unsigned NOT NULL COMMENT '登录IP ID',
  `user_session` char(32) COLLATE utf8_bin NOT NULL COMMENT '登录会话值',
  `user_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `user_remember` tinyint(1) NOT NULL DEFAULT '0' COMMENT '记住我',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `user_username` (`user_username`),
  KEY `user_group` (`user_group`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `sys_user`
--

INSERT INTO `sys_user` (`id`, `user_username`, `user_password`, `user_email`, `user_name`, `user_group`, `user_date`, `user_login_date`, `user_ip`, `user_session`, `user_status`, `user_remember`) VALUES
(1, 'admin', 'dd94709528bb1c83d08f3088d4043f4742891f4f', 'admin@admin.com', '管理员', 1, '2013-03-20 11:15:57', '2013-06-27 09:02:21', 0, '7nlk26kjv3fmqv9jgseo7nn292', 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `sys_user_group`
--

CREATE TABLE IF NOT EXISTS `sys_user_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `group_name` varchar(30) COLLATE utf8_bin NOT NULL COMMENT '名称',
  `group_power` text COLLATE utf8_bin NOT NULL COMMENT '权限',
  `group_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `sys_user_group`
--

INSERT INTO `sys_user_group` (`id`, `group_name`, `group_power`, `group_status`) VALUES
(1, '管理员组', 0x61646d696e, 1);

-- --------------------------------------------------------

--
-- 表的结构 `word_info`
--

CREATE TABLE IF NOT EXISTS `word_info` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `info_word` varchar(100) COLLATE utf8_bin NOT NULL COMMENT '单词',
  `info_pho` varchar(100) COLLATE utf8_bin NOT NULL COMMENT '音标',
  `info_img` varchar(300) COLLATE utf8_bin NOT NULL COMMENT '图片文件地址',
  `info_voice` varchar(300) COLLATE utf8_bin NOT NULL COMMENT '音频文件地址',
  `info_des` text COLLATE utf8_bin NOT NULL COMMENT '解释',
  `info_note` varchar(300) COLLATE utf8_bin NOT NULL COMMENT '注记',
  `info_dict` text COLLATE utf8_bin NOT NULL COMMENT '例句',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;
