<?php

/**
 * 获取单词列表封装
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package plug
 */
class PlugWordList {

    /**
     * 课堂句柄
     * @var missionview 
     */
    private $missionView;

    /**
     * 单词句柄
     * @var MissionWord 
     */
    private $missionWord;

    /**
     * 初始化
     * @param missionview $missionView 课堂句柄
     * @param MissionWord $missionWord 单词句柄
     */
    public function __construct(&$missionView, &$missionWord) {
        $this->missionView = $missionView;
        $this->missionWord = $missionWord;
    }

    /**
     * 获取列表
     * @param int $parent 上一级ID
     * @param int $type 类型键值
     * @param int $page 页数
     * @param int $max 页长
     * @return array|boolean 数据|false
     */
    public function getList($parent = 0, $type = null, $page = 1, $max = 9999) {
        $postType = $type === null ? $this->missionView->types[2] : $this->missionView->types[$type];
        if ($postType == 'word') {
            $sql = 'SELECT sys_posts.id,sys_posts.post_title,sys_posts.post_parent,sys_posts.post_type,word_info.info_word as word,word_info.info_pho as pho,word_info.info_img as img,word_info.info_voice as voice,word_info.info_des as des,word_info.info_note as note,word_info.info_dict as dict FROM ' . $this->missionView->post->table_name . ' as sys_posts,' . $this->missionWord->tableName . ' as word_info WHERE sys_posts.post_title = word_info.info_word and sys_posts.post_parent = :parent and sys_posts.post_type = :type ORDER BY sys_posts.post_order ASC LIMIT ' . ($page - 1) * $max . ',' . $max;
            $sth = $this->missionWord->db->prepare($sql);
            $sth->bindParam(':parent', $parent, PDO::PARAM_INT);
            $sth->bindParam(':type', $postType, PDO::PARAM_STR);
            if ($sth->execute() == true) {
                $res = $sth->fetchAll(PDO::FETCH_ASSOC);
                if ($res) {
                    foreach ($res as $k => $v) {
                        $res[$k]['des'] = $this->missionWord->getGroupArr($v['des']);
                        $res[$k]['note'] = $this->missionWord->getGroupArr($v['note']);
                        $res[$k]['dict'] = $this->missionWord->getGroupArr($v['dict']);
                    }
                }
                return $res;
            }
            return false;
        } else {
            return $this->missionView->view_list($parent, $type, $page, $max);
        }
    }

}

?>
