<?php

/**
 * 退出登陆操作
 * @author fotomxq <fotomxq.me>
 * @version 3
 * @package SYS
 */
/**
 * 引入全局
 * @since 1
 */
require('glob.php');

/**
 * 引入用户类
 * @since 1
 */
require(DIR_LIB . DS . 'sys-user.php');

/**
 * 进行退出登陆操作
 * @since 3
 */
$user = new sysuser($db);
$user->logout($ip_arr['id']);
plugtourl('index.php');
?>
