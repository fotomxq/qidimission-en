<?php

/**
 * 所有脚本引用页
 * @author fotomxq <fotomxq.me>
 * @version 2
 * @package page
 */

/**
 * 定义全局路径
 * @since 1
 */
define('DS', '/');
define('DIR_LIB', 'includes');
define('DIR_DATA', 'content');

/**
 * 设定时区
 * @since 1
 */
date_default_timezone_set('PRC');

/**
 * 开启会话
 * @since 1
 */
@session_start();

/**
 * 网站测试开关
 * @since 1
 */
define('SYS_DEBUG', true);

/**
 * 缓冲设定模块
 * @since 2
 */
require(DIR_LIB . DS . 'plug-headernocache.php');
if (SYS_DEBUG == true) {
    plugheadernocache();
}

/**
 * 引用数据库配置文件
 * @since 1
 */
require(DIR_DATA . DS . 'configs' . DS . 'db.inc.php');

/**
 * 跳转模块
 * @since 1
 */
require(DIR_LIB . DS . 'plug-tourl.php');

/**
 * 引入错误处理模块
 * @since 1
 */
require(DIR_LIB . DS . 'core-error.php');
require(DIR_LIB . DS . 'plug-error.php');

/**
 * 引入并初始化数据库连接<br/>
 * 保留$db变量用于后面使用
 * @since 1
 */
require(DIR_LIB . DS . 'core-db.php');
$db = new coredb($db_dns, $db_username, $db_password, $db_persistent);
$db->set_encoding($db_encoding);

/**
 * 初始化配置操作句柄
 * @since 1
 */
require(DIR_LIB . DS . 'sys-configs.php');
$sysconfigs = new sysconfigs($db);

/**
 * 初始化IP地址
 * @since 1
 */
require(DIR_LIB . DS . 'core-ip.php');
$coreip = new coreip(DIR_DATA . DS . 'configs' . DS . 'qqwry.dat', $db);
$ip_arr = $coreip->get_ip();

/**
 * 初始化日志操作
 * @since 1
 */
require(DIR_LIB . DS . 'core-log.php');
$log = new corelog($ip_arr['addr'], $db, true);

/**
 * 获取网站URL
 * @since 1
 */
$web_url = 'http://localhost/qidimission';
?>
