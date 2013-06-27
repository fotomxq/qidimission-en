<?php

/**
 * 显示单词图片
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package page
 */
require('glob_mission.php');
require(DIR_LIB . DS . 'plug-feedback.php');

if (isset($_GET['word'])) {
    $wordSrc = $missionword->getWordInfo($_GET['word']);
    if ($wordSrc) {
        $missionword->headerImg($wordSrc['img']);
    }
}
?>
