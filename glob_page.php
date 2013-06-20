<?php

/**
 * 所有显示页面引用页
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package SYS
 */
/**
 * 引入全局处理页
 * @since 1
 */
require('glob.php');

/**
 * 获取相关配置
 * @since 1
 */
$web_title = $sysconfigs->load('WEB_TITLE');
$page_meta_description = $sysconfigs->load('PAGE_META_DESCRIPTION');
$page_meta_author = $sysconfigs->load('PAGE_META_AUTHOR');
?>
