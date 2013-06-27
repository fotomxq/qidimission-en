<?php

/**
 * 获取单词相近列表
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package page
 */
require('glob_logged.php');
require('glob_mission.php');
require(DIR_LIB . DS . 'plug-feedback.php');

//初始化
plugfeedbackheaderjson();
$status = '';
$error = '';

if (isset($_GET['word']) == true) {
    $wordList = $missionword->getLikeWord($_GET['word']);
    if ($wordList) {
        $newList = null;
        foreach($wordList as $v){
            $newList[] = $v['word'];
        }
        $status = $newList;
        $error = '';
    }
}

plugfeedbackjson($status, $error);
?>
