<?php
/**
 * 页面底部引用信息
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package page
 */
require_once('glob_logged.php');
?>
<div id="footer" class="navbar navbar-inverse navbar-fixed-bottom">
    <div class="container">
        <p class="muted credit text-center">
            <?php if(isset($foot_link_arr) == true){ foreach($foot_link_arr as $v){ ?>
            <a href="<?php echo $v['url']; ?>" target="_self"<?php if(isset( $v['attr']) == true){ echo $v['attr']; } ?>><?php echo $v['title']; ?></a> | 
            <?php } } ?>Code licensed under Apache License v2.0,By <a href="http://fotomxq.me/">Fotomxq</a></p>
    </div>
</div>