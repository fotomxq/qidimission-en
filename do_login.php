<?php

/**
 * 登陆处理
 * @author fotomxq <fotomxq.me>
 * @version 4
 * @package do
 */
/**
 * 引入全局定义
 * @since 1
 */
require('glob.php');

/**
 * 引入用户操作封装
 * @since 1
 */
require(DIR_LIB . DS . 'sys-user.php');

/**
 * 检查变量存在并转移给user类
 * @since 3
 */
if (isset($_POST['user']) == true && isset($_POST['pass']) == true) {
    $remember = false;
    if (isset($_POST['remeber']) == true) {
        $remember = true;
    }
    $user = new sysuser($db);
    $login_bool = $user->login($_POST['user'], $_POST['pass'], $ip_arr['id'], $remember);
    if ($login_bool == true) {
        $log->add('do_login.php -> login success.');
        plugtourl('init.php');
    } else {
        $log->add('do_login.php -> login error : username or password is wrong.');
        plugtourl('index.php?e=1');
    }
} else {
    plugtourl('index.php');
}
?>
