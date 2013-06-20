<?php

/**
 * 已登陆检测
 * <p>如果发现尚未登陆，则直接中断页面</p>
 * @author fotomxq <fotomxq.me>
 * @version 5
 * @package sys
 */
/**
 * 引入全局
 * @since 1
 */
require('glob_page.php');

/**
 * 引入用户类
 * @since 4
 */
require(DIR_LIB . DS . 'sys-user.php');
$user = new sysuser($db);

/**
 * 当前用户ID
 * @since 5
 */
$user_id = $user->get_session_login();

/**
 * 进行登陆检测
 * @since 5
 */
//读取用户超时配置
$config_user_timeout = (int) $sysconfigs->load('USER_TIMEOUT');
$logged_admin = false;
if ($user->status($ip_arr['id'], $config_user_timeout) == true) {
    $logged_user = $user->view_user($user_id);
    if ($logged_user) {
        $logged_group = $user->view_group($logged_user['user_group']);
        if ($logged_group) {
            if ($logged_group['group_power'] == 'admin') {
                $logged_admin = true;
            }
        }
    }
} else {
    //如果尚未登陆处理
    plugerror('logged');
}
unset($config_user_timeout);

/**
 * 判断网站开关且是否为管理员
 * <p>如果是管理员，则可以在网站关闭情况下进入；反之无法登陆。</p>
 * @since 4
 */
$website_on = $sysconfigs->load('WEB_ON');
if (!$website_on && !$logged_admin) {
    plugerror('webclose');
}
?>
