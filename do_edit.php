<?php

/**
 * 编辑ajax操作
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package page
 */
/**
 * 引入全局
 */
require('glob_logged.php');
require('glob_mission.php');
require(DIR_LIB . DS . 'plug-feedback.php');

/**
 * 初始化
 */
plugfeedbackheaderjson();
$status = '2';
$error = '您提交的信息有误，请重新确认无误后再提交。';

/**
 * 处理提交
 */
if (isset($_GET['mode']) == true) {
    switch ($_GET['mode']) {
        case 'list':
            //获取列表
            if (isset($_GET['parent']) == true && isset($_GET['type']) == true && isset($_GET['page']) == true && isset($_GET['max']) == true) {
                $res = $missionview->view_list($_GET['parent'], $_GET['type'], $_GET['page'], $_GET['max']);
                $res_row = $missionview->view_list_row($_GET['parent'], $_GET['type']);
                $res_level = $missionview->view_level($_GET['parent']);
                $res_level = $res_level ? $res_level : '';
                if ($res && $_GET['type'] == '0') {
                    foreach ($res as $k => $v) {
                        $res[$k]['word'] = $missionword->getWordInfo($v['post_title']);
                    }
                }
                $status = array('res' => $res, 'row' => $res_row, 'level' => $res_level);
                $error = '';
            }
            break;
        case 'add':
            //添加操作
            if (isset($_POST['title']) == true && isset($_POST['parent']) == true && isset($_POST['type']) == true && isset($_POST['manually']) == true) {
                $word_id = 0;
                if ($_POST['type'] === '0') {
                    if ($_POST['manually']) {
                        $word_id = 1;
                    } else {
                        $word_id = $missionword->getWordInfo($_POST['title']);
                    }
                } else {
                    $word_id = 1;
                }
                if ($word_id > 0) {
                    $add_id = $missionview->add($_POST['title'], $_POST['type'], $_POST['parent']);
                    $status = $add_id > 0 ? '1' : '0';
                    $error = $status == '1' ? '添加成功。' : '无法添加，请稍候重试。';
                } else {
                    $add_id = $missionview->add($_POST['title'], $_POST['type'], $_POST['parent']);
                    $status = $add_id > 0 ? '1' : '0';
                    $error = $status == '1' ? '本地词库和互联网词库均无法找到该单词，系统已自动转入手动添加模式，请稍后手动填入相关信息。' : '找不到单词信息，且无法添加该单词，请稍候重试。';
                }
            }
            break;
        case 'edit':
            //编辑操作
            if (isset($_POST['type']) == true) {
                switch ($_POST['type']) {
                    case 1:
                        //从URL获取单词信息
                        if (isset($_POST['word']) == true) {
                            $status = $missionword->getURLInfo($_POST['word']) == true ? '1' : '0';
                            $error = $status == '1' ? '单词的词条已经更新。' : '无法更新词条信息，可能是URL阻塞，请稍候再试。';
                        }
                        break;
                    case 2:
                        //编辑单词信息
                        if (isset($_POST['word']) == true && isset($_POST['infos']) == true) {
                            $status = $missionword->saveWordInfo($_POST['word'],$_POST['infos']) == true ? '1' : '0';
                            $error = $status == '1' ? '修改单词成功。' : '无法修改单词信息，请稍候再试。';
                        }
                        break;
                    default:
                        //编辑名称
                        if (isset($_POST['id']) == true && isset($_POST['title']) == true) {
                            $status = $missionview->edit($_POST['id'], $_POST['title']) == true ? '1' : '0';
                            $error = $status == '1' ? '编辑成功。' : '无法编辑该记录，请稍候重试。';
                        }
                        break;
                }
            }
            break;
        case 'move':
            //移动操作
            if (isset($_POST['src']) == true && isset($_POST['dest']) == true) {
                $status = $syspost->edit_order($_POST['src'], $_POST['dest']) == true ? '1' : '0';
                $error = $status == '1' ? '修改成功。' : '无法修改位置，请稍候重试。';
            }
            break;
        case 'del':
            //删除操作
            if (isset($_POST['id']) == true) {
                $status = $missionview->del($_POST['id']) ? '1' : '0';
                $error = $status == '1' ? '删除成功。' : '无法删除该内容，请稍候重试。';
            }
            break;
        case 'set-mission':
            if (isset($_POST['id']) == true) {
                $status = $missionview->save_mission($_POST['id']) ? '1' : '0';
                $error = $status == '1' ? '设置成功，下次将以该小节进行课堂计划。' : '无法将该小节设定为课堂计划，请稍候重试。';
            }
            break;
        case 'clear-review':
            //清空复习数据
            $status = $missionreview->clear_configs() == true ? '1' : '0';
            $error = $status == '1' ? '成功清理了所有复习数据。' : '无法清空复习数据，请稍候再试。';
            break;
        case 'sys':
            //系统设置
            if (isset($_POST['a']) == true) {
                $status = $sysconfigs->save('USER_TIMEOUT', $_POST['a']) == true ? '1' : '0';
                $error = $status == '1' ? '修改了系统设置。' : '无法修改系统设置，请稍后重试。';
            }
            break;
    }
}
plugfeedbackjson($status, $error);
?>
