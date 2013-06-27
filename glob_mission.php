<?php

/**
 * 课堂应用文件引用
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package page
 */
/**
 * 引入全局处理
 * @since 1
 */
require_once('glob_logged.php');

/**
 * 引入Post类
 */
require(DIR_LIB . DS . 'sys-post.php');
$syspost = new syspost($db, $ip_arr['id']);

/**
 * 引入xmltoarray模块
 */
require(DIR_LIB . DS . 'plug-xmltoarray.php');

/**
 * 引入文件操作
 */
require(DIR_LIB . DS . 'core-file.php');

/**
 * 引入mission-word类
 */
require(DIR_LIB . DS . 'mission-word.php');
$missionword = new MissionWord($db);

/**
 * 引入mission-view类
 */
require(DIR_LIB . DS . 'mission-view.php');
$missionview = new missionview($syspost, $sysconfigs, $user_id);

/**
 * post-type数组
 */
$types = $missionview->types;

/**
 * 引入mission-review类
 */
require(DIR_LIB . DS . 'mission-review.php');
$missionreview = new missionreview($db, $syspost, $sysconfigs, $types);
?>
