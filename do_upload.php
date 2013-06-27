<?php

/**
 * 上传文件
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package page
 */
/*
  Uploadify
  Copyright (c) 2012 Reactive Apps, Ronnie Garcia
  Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */
// Define a destination
//$targetFolder = DIR_DATA . DS . '/files';
//$verifyToken = md5('unique_salt' . $_POST['timestamp']);

require_once('glob_logged.php');
require('glob_mission.php');
require(DIR_LIB . DS . 'plug-feedback.php');
plugfeedbackheaderjson();

if (!empty($_FILES) && isset($_GET['word']) == true && isset($_GET['type']) == true) {
    if ($_GET['word'] != '' && $_GET['type'] != '') {

        $tempFile = $_FILES['Filedata']['tmp_name'];

        // Validate the file type
        $fileTypes = array('jpg', 'jpeg', 'gif', 'png', 'mp3'); // File extensions
        $fileParts = pathinfo($_FILES['Filedata']['name']);

        if (in_array($fileParts['extension'], $fileTypes)) {
            if ($_GET['type'] == 'voice') {
                if ($fileParts['extension'] != 'mp3') {
                    die('voice must mp3.');
                }
            }
            //生成路径
            $dir_name = DIR_DATA . DS . 'files' . DS . date('Ym') . DS . date('d');
            if (corefile::new_dir($dir_name) == true) {
                $file_name = $dir_name . DS . time() . '_' . rand(1, 9999) . '.' . $fileParts['extension'];
                move_uploaded_file($tempFile, $file_name);
                $wordInfos = $missionword->getWordInfo($_GET['word']);
                if ($wordInfos) {
                    $wordInfos['img'] = $file_name;
                    if ($missionword->saveWordInfo($wordInfos['word'], $wordInfos) == true) {
                        echo 'ok.';
                    } else {
                        echo 'cannot create data.';
                    }
                }
            } else {
                echo 'cannot read type.';
            }
        }
    }
}
?>
