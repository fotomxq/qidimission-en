<?php
/**
 * 中心页面
 * @author fotomxq <fotomxq.me>
 * @version 2
 * @package page
 */
/**
 * 引入全局处理
 * @since 1
 */
require('glob_logged.php');
require('glob_mission.php');

/**
 * 组合页面重要参数
 */
$page_mode = isset($_GET['mode']) == true ? $_GET['mode'] : 'mission';
$css_arr = null;
$js_arr = null;
$require_arr = null;
$foot_link_arr = null;
switch ($page_mode) {
    case 'edit':
        $css_arr[] = 'page_edit.css';
        $css_arr[] = 'uploadify.css';
        $js_arr[] = 'page_edit.js';
        $js_arr[] = 'jquery.uploadify.min.js';
        $require_arr[] = 'page_edit.php';
        $foot_link_arr['mission'] = array('title' => '继续课堂', 'url' => 'init.php?mode=mission');
        $foot_link_arr['system'] = array('title' => '高级设置', 'url' => '#system-operate', 'attr' => 'data-toggle="modal"');
        break;
    default:
        $css_arr[] = 'page_mission.css';
        $js_arr[] = 'page_mission.js';
        $require_arr[] = 'page_mission.php';
        $foot_link_arr['edit'] = array('title' => '编辑模式', 'url' => 'init.php?mode=edit');
        break;
}
$foot_link_arr['logout'] = array('title' => '退出登录', 'url' => 'do_logout.php');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php echo $web_title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- CSS -->
    <link href="includes/css/bootstrap.css" rel="stylesheet">
    <link href="includes/css/init.css" rel="stylesheet">
        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="includes/js/html5shiv.js"></script>
    <![endif]-->
    <?php foreach($css_arr as $v){ ?>
    <link href="includes/css/<?php echo $v; ?>" rel="stylesheet">
    <?php } ?>
    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="includes/img/logo-144.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="includes/img/logo-114.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="includes/img/logo-72.png">
    <link rel="apple-touch-icon-precomposed" href="includes/img/logo-57.png">
    <link rel="shortcut icon" href="includes/img/favicon.ico">
  </head>
  <body>
      <?php foreach($require_arr as $v){ require($v); } ?>
      <script src="includes/js/jquery.js"></script>
      <script src="includes/js/bootstrap.js"></script>
      <?php foreach($js_arr as $v){ ?>
      <script src="includes/js/<?php echo $v; ?>"></script>
      <?php } ?>
      <?php require('page_footer.php'); ?>
  </body>
</html>