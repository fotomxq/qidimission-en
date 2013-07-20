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
        //在本地搜索词库
        $search = $searchPNG = DIR_DATA . DS . 'word-images' . DS . $wordSrc['word'];
        $searchJPG = $search . '.jpg';
        $searchPNG = $search . '.png';
        $searchGIF = $search . '.gif';
        $src = '';
        if (corefile::is_file($searchJPG) == true) {
            $src = $searchJPG;
        } elseif (corefile::is_file($searchPNG) == true) {
            $src = $searchPNG;
        } elseif (corefile::is_file($searchGIF) == true) {
            $src = $searchGIF;
        } else {
            $src = $wordSrc['img'];
        }
        //输出图片
        $missionword->headerImg($src);
    }
}
?>
