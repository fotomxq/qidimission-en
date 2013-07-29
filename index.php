<?php
/**
 * 登录页面
 * @author fotomxq <fotomxq.me>
 * @version 2
 * @package page
 */
require('glob_page.php');

/**
 * 引入用户操作封装
 * @since 1
 */
require(DIR_LIB . DS . 'sys-user.php');

/**
 * 判断用户是否记住登陆，或已经登陆
 */
$user = new sysuser($db);
$status = $user->status($ip_arr['id'], (int) $sysconfigs->load('USER_TIMEOUT'));
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>登录 - <?php echo $web_title; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="<?php echo $page_meta_description; ?>">
        <meta name="author" content="<?php echo $page_meta_author; ?>">

        <!-- Le styles -->
        <link href="includes/css/bootstrap.css" rel="stylesheet">
        <style type="text/css">
            body {
                padding-top: 40px;
                padding-bottom: 40px;
                background-color: #f5f5f5;
            }

            .form-signin {
                max-width: 300px;
                padding: 30px 50px 40px;
                margin: 0 auto 20px;
                background-color: #fff;
                border: 1px solid #e5e5e5;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 20px;
                -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
            }
            .form-signin .form-signin-heading,
            .form-signin .checkbox {
                margin-bottom: 30px;
            }
            .form-signin input[type="text"],
            .form-signin input[type="password"] {
                font-size: 16px;
                height: auto;
                margin-bottom: 15px;
                padding: 7px 9px;
            }
            .login-container{
                background-size: 1000px;
                background-position-x: 65px;
            }

        </style>
        <link href="includes/css/bootstrap-responsive.css" rel="stylesheet">
        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="includes/js/html5shiv.js"></script>
        <![endif]-->
        <!-- Fav and touch icons -->
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="includes/img/logo-144.png">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="includes/img/logo-114.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="includes/img/logo-72.png">
        <link rel="apple-touch-icon-precomposed" href="includes/img/logo-57.png">
        <link rel="shortcut icon" href="includes/img/favicon.ico">
    </head>

    <body>

        <div class="container login-container">
            <?php if($status == false){ ?>
            <form class="form-signin" action="do_login.php" method="post">
                <h2 class="form-signin-heading"><?php echo $web_title; ?></h2>
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-user"></i> 用户</span>
                    <input class="input-large" type="text" name="user" class="input-block-level" placeholder="用户名" value="<?php if(SYS_DEBUG == true){ echo 'admin'; } ?>">
                </div>
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-lock"></i> 密码</span>
                    <input class="input-large" type="password" name="pass" class="input-block-level" placeholder="密码" value="<?php if(SYS_DEBUG == true){ echo 'adminadmin'; } ?>">
                </div>
                <label class="checkbox">
                    <input type="checkbox" value="remember-me"> 记住我
                </label>
                <button class="btn btn-large btn-primary" type="submit">登录</button>
            </form>
            <?php }else{ ?>
            <h3><a href="init.php" target="_self">您已经登陆，正在跳转中...</a></h3>
            <?php } ?>
        </div> <!-- /container -->

        <!-- javascript -->
        <script src="includes/js/jquery.js"></script>
        <script src="includes/js/bootstrap.js"></script>
        <?php if($status == true){ ?>
        <script type="text/javascript">
                function tourl_init(){
                    window.location.href = "init.php";
                }
                jQuery("div[class='container login-container']").attr("class","container");
                var t = setTimeout("tourl_init();",1000);
        </script>
        <?php } ?>
    </body>
</html>