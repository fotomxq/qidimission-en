<?php
/**
 * 课堂模式页面
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package page
 */
/**
 * 引入全局
 */
require_once('glob_logged.php');

/**
 * 初始化变量
 */
//学习偏移位置
$offset = isset($_GET['offset']) == true ? (int)$_GET['offset'] : 1;
//是否复习 normal-学习模式 ; review-复习模式
$review_bool = isset($_GET['review']) == true ? true : false;
//层级信息
$word_level = null;

/**
 * 单词信息
 */
//单词
$word_word = '';
//单词信息
$word_infos = null;
//跳过一个单元
if (isset($_GET['next']) == true) {
    $word_res = $missionview->get(1);
    $missionview->next_section();
}
//获取单词信息
if ($review_bool == true) {
    //如果需要建立复习数据
    if (isset($_GET['create']) == true) {
        $missionreview->create();
    }
    //复习模式下获取
    $word_res = $missionreview->next($offset);
    if ($word_res) {
        $word_word = $word_res['review_word'];
    }
} else {
    //学习模式下获取
    $word_res = $missionview->get($offset);
    if ($word_res) {
        $word_id = $word_res['id'];
        $word_word = $word_res['post_title'];
        $word_level = $missionview->view_level($word_id);
    }
}
//获取单词相关信息
if ($word_word) {
    $word_infos = $missionword->getWordInfo($word_word);
    //如果在学习模式末尾
    if ($missionview->section_end == true && $review_bool == false) {
        $missionreview->add($missionview->section_res['id']);
    }
}

/**
 * 显示设定
 */
//单词显示形式类型序列
$show_mode_arr = array('en', 'zh', 'all');
//单词显示形式 all-全部显示 ; en-仅显示英文单词 ; zh-隐藏英文单词，注意音标总是显示
$show_mode = isset($_GET['show']) == true ? $_GET['show'] : $show_mode_arr[2];
//单词显示形式下一个选择键值
$show_mode_next = array_keys($show_mode_arr, $show_mode);
$show_mode_next = $show_mode_next[0];
if ($show_mode == $show_mode_arr[2]) {
    $show_mode_next = 0;
} else {
    $show_mode_next += 1;
}

/**
 * 设定例句和无图片序列
 */
//例句显示个数
$dict_num = isset($_GET['dict-num']) == true ? Math . abs($_GET['dict-num']) : 1;
//无图提示图像序列
$noimg_arr = array('includes/img/noimg_01.png', 'includes/img/noimg_02.png', 'includes/img/noimg_03.png', 'includes/img/noimg_04.png');

/**
 * URL
 */
//当前的核心URL
$url = 'init.php?mode=mission&show=' . $show_mode;
$url_show = 'init.php?mode=mission&offset=' . $offset;
if ($review_bool == true) {
    $url_show .= '&review=1';
}
//下一个单词URL
$next_offset = $offset;
$next_review = '';
if ($missionview->section_end == true) {
    $next_offset = 1;
    $next_review = '&review=1';
}
if ($next_review == true) {
    $next_review = '&review=1';
}
$url_next = $url . '&offset=' . $next_offset . $next_review . '&show=' . $show_mode;
?>
<div class="container">
    <!-- 顶部位置提示 -->
    <div class="row">
        <div class="span12">
            <ul class="breadcrumb" id="data-tip">
                <?php if ($word_level !== null) { ?>
                <li><?php echo $word_level[2]['title']; ?> <span class="divider">-</span></li>
                <li><?php echo $word_level[1]['title']; ?> <span class="divider">-</span></li>
                <li class="active"><?php echo $word_level[0]['title']; ?></li>
                <?php }else{ ?>
                <li>复习阶段</li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <!-- 顶部位置提示 结束 -->

    <!-- 单词 -->
    <div class="row">
        <div class="span12 info-word text-center" id="info-word">
            <?php if(($show_mode == $show_mode_arr[2] || $show_mode == $show_mode_arr[0]) && $word_infos != null){ echo $word_infos['word']; } ?>
        </div>
    </div>
    <!-- 单词 结束 -->
    
    <!-- 音标和隐藏发音 -->
    <div class="row">
        <div class="span12 info-pho text-center" id="info-pho">
            <?php if($word_infos !== null){ echo '[&nbsp;'.$word_infos['pho'].'&nbsp;]'; }else{ if($review_bool == true){ $missionview->next_section(); ?>
            <a href="<?php echo $url; ?>&offset=1&next=1">复习结束，是否继续学习？</a>
            <?php }else{ ?>
            <a href="<?php echo $url; ?>&offset=1&next=1">本单元结束，是否继续学习？</a>
            <?php } } ?>
        </div>
        <?php if($word_infos !== null){ if($word_infos['voice']){ ?>
        <audio src="<?php echo $word_infos['voice']; ?>" preload="auto" autoplay="autoplay"></audio>
        <?php } } ?>
    </div>
    <!-- 音标和隐藏发音 结束 -->
    
    <hr>

    <!-- 单词信息 -->
    <div class="row">
        <?php if(($show_mode == $show_mode_arr[2] || $show_mode == $show_mode_arr[1]) && $word_infos !== null){ ?>
        <div class="span4">
            <?php if($word_infos['img']){ ?>
            <img src="<?php echo $word_infos['img']; ?>" class="img-polaroid info-img">
            <?php }else{ $img_src_key = array_rand($noimg_arr); ?>
            <img src="<?php echo $noimg_arr[$img_src_key]; ?>" class="img-polaroid info-img">
            <?php } ?>
        </div>
        <div class="span8 text-left">
            <?php if($word_infos['des']){ foreach($word_infos['des'] as $v){ ?>
            <p class="info-des"><?php echo $v['p'].'&nbsp;&nbsp;'.$v['d']; ?></p>
            <?php } } ?>
            <p>&nbsp;</p>
            <?php if($word_infos['dict']){ $dict_keys = array_rand($noimg_arr,$dict_num); if(is_array($dict_keys) == true){ foreach($dict_keys as $v){ ?>
            <p class="info-dict"><?php if(isset($word_infos['dict'][$v]['en']) == true) echo $word_infos['dict'][$v]['en']; ?></p>
            <p class="info-dict"><?php if(isset($word_infos['dict'][$v]['zh']) == true) echo '&nbsp;&nbsp;'.$word_infos['dict'][$v]['zh']; ?></p>
            <?php } }else{ ?>
            <p class="info-dict"><?php if(isset($word_infos['dict'][$dict_keys]['en']) == true) echo $word_infos['dict'][$dict_keys]['en']; ?></p>
            <p class="info-dict"><?php if(isset($word_infos['dict'][$dict_keys]['zh']) == true) echo '&nbsp;&nbsp;'.$word_infos['dict'][$dict_keys]['zh']; ?></p>
            <?php } } ?>
        </div>
        <?php } ?>
    </div>
    <!-- 单词信息 结束 -->
</div>

<!-- 浮动按钮 -->
<div id="buttons">
    <div class="row" id="buttons_next">
        <div class="span5 text-right">
            <?php if($missionview->section_end == true){ ?>
            <a class="hide" href="<?php echo $url; ?>&offset=1&next=1"><img src="includes/img/button_review_next.png" class="img-circle"></a>
            <?php } ?>
        </div>
        <div class="span2">
            <?php if($missionview->section_end == true){ ?>
            <a href="<?php echo $url; ?>&offset=1&review=1&create=1"><img src="includes/img/button_review.png" class="img-circle"></a>
            <?php }else{ ?>
            <a href="<?php echo $url; ?><?php if($review_bool){ echo '&review=1'; } if($word_infos == null){ echo '&offset=1'; }else{ echo '&offset='.($offset+1); } ?>"><img src="includes/img/button_next.png" class="img-circle"></a>
            <?php } ?>
        </div>
    </div>
    <div class="row" id="buttons_play">
        <div class="span5"></div>
        <div class="span2">
            <a href="#"><img src="includes/img/button_play.png" class="img-circle"></a>
        </div>
    </div>
    <div class="row" id="buttons_note">
        <div class="span5"></div>
        <div class="span2">
            <a href="#"><img src="includes/img/button_note.png" class="img-circle"></a>
        </div>
    </div>
    <div class="row" id="buttons_show">
        <div class="span5 text-right">
            <a class="hide" href="<?php echo $url_show; ?>&show=en"><img src="includes/img/button_show_en.png" class="img-circle"></a>
            <a class="hide" href="<?php echo $url_show; ?>&show=zh"><img src="includes/img/button_show_zh.png" class="img-circle"></a>
            <a class="hide" href="<?php echo $url_show; ?>&show=all"><img src="includes/img/button_show_all.png" class="img-circle"></a>
        </div>
        <div class="span2">
            <a href="<?php echo $url_show . '&show=' . $show_mode_arr[$show_mode_next]; ?>"><img src="includes/img/button_show.png" class="img-circle"></a>
        </div>
    </div>
</div>
<!-- 浮动按钮 结束 -->

<!-- 传递js变量 -->
<script>
    var offset = <?php echo $offset; ?>;
    var url_next = "<?php echo $url_next; ?>";
    var info_note_en = "<?php if($word_infos !== null){ if(isset($word_infos['note']) == true){ echo $word_infos['note'][0]['en']; } } ?>";
    var info_note_zh = "<?php if($word_infos !== null){ if(isset($word_infos['note']) == true){ echo $word_infos['note'][0]['zh']; } } ?>";
</script>
<!-- 传递js变量 结束 -->