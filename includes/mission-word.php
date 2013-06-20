<?php

/**
 * 单词库操作类
 * <p>需要 : plug-xmltoarray、corefile、DIR_DATA、DS</p>
 * @author fotomxq <fotomxq.me>
 * @version 2
 * @package mission
 */
class missionword {

    /**
     * 表名
     * @var string 
     */
    private $table_name;

    /**
     * 数据库句柄
     * @var coredb 
     */
    private $db;

    /**
     * 数据保存目录
     * @var string 
     */
    private $data_dir;

    /**
     * 初始化
     * @param coredb $db
     */
    public function __construct(&$db) {
        $this->db = $db;
        $this->table_name = $db->tables['word'];
        $this->data_dir = DIR_DATA . DS . 'files';
    }

    /**
     * 创建词汇
     * @param string $word 单词名称
     * @param array $word_arr 单词数据
     * @return int 单词ID
     */
    public function add($word, $word_arr = null) {
        $return = 0;
        $sql = 'SELECT `id` FROM `' . $this->table_name . '` WHERE `word_name` = \'word\' and `word_src` = :word';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':word', $word, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            //如果存在则返回单词ID
            $res_id = $sth->fetchColumn();
            if ($res_id) {
                $return = $res_id;
            } else {
                //如果不存在则创建数据
                if ($word_arr == null) {
                    $word_arr = $this->get_url_word($word);
                }
                if ($word_arr) {
                    $this->db->beginTransaction();
                    try {
                        //创建单词ID
                        $res_id = $this->add_query(0, 'word', $word);
                        if ($res_id > 0) {
                            //添加单词信息
                            //注记英文
                            $this->add_query($res_id, 'note-en', $word_arr['note']['en']);
                            //注记中文
                            $this->add_query($res_id, 'note-zh', $word_arr['note']['zh']);
                            //音标
                            $this->add_query($res_id, 'pho', $word_arr['pho']);
                            //解释
                            foreach ($word_arr['des'] as $v) {
                                $des_id = $this->add_query($res_id, 'des', '');
                                $this->add_query($des_id, 'p', $v['p']);
                                $this->add_query($des_id, 'd', $v['d']);
                            }
                            //例句
                            foreach ($word_arr['dict'] as $v) {
                                $dict_id = $this->add_query($res_id, 'dict', '');
                                $this->add_query($dict_id, 'en', $v['en']);
                                $this->add_query($dict_id, 'zh', $v['zh']);
                            }
                            //图片
                            $this->add_query($res_id, 'img', $word_arr['img']);
                            //发音
                            foreach ($word_arr['voice'] as $v) {
                                $this->add_query($res_id, 'voice', $v);
                            }
                            $return = $res_id;
                        }
                        $this->db->commit();
                    } catch (PDOException $e) {
                        $this->db->rollBack();
                        $return = 0;
                    }
                } else {
                    //无法从URL获取相关数据信息
                }
            }
        }
        return $return;
    }

    /**
     * 添加执行SQL封装
     * <p>可能会抛出异常，请注意使用捕捉</p>
     * @param int $parent 上一级
     * @param string $name 标识
     * @param string $src 值
     * @return int 添加ID
     */
    private function add_query($parent, $name, $src) {
        $return = 0;
        $sql = 'INSERT INTO `' . $this->table_name . '`(`id`,`word_parent`,`word_name`,`word_src`) VALUES(NULL,:parent,:name,:src)';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':parent', $parent, PDO::PARAM_INT);
        $sth->bindParam(':name', $name, PDO::PARAM_STR);
        $sth->bindParam(':src', $src, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->execute();
        $return = $this->db->lastInsertId();
        if ($return < 1) {
            throw new PDOException;
        }
        return $return;
    }

    /**
     * 外部获取单词数据
     * @param string $word 单词名称
     * @param boolean $bool_id 是否需要信息ID
     * @return boolean
     */
    public function get($word, $bool_id = false) {
        $return = false;
        $sth = null;
        if (is_string($word) == true) {
            $sql = 'SELECT `id`,`word_parent`,`word_name`,`word_src` FROM `' . $this->table_name . '` WHERE `word_name` = \'word\' and `word_src` = :word';
            $sth = $this->db->prepare($sql);
            $sth->bindParam(':word', $word, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        } else {
            $sql = 'SELECT `id`,`word_parent`,`word_name`,`word_src` FROM `' . $this->table_name . '` WHERE `word_name` = \'word\' and `id` = :word';
            $word = (int) $word;
            $sth = $this->db->prepare($sql);
            $sth->bindParam(':word', $word, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($sth->execute() == true) {
            $res_word = $sth->fetch(PDO::FETCH_ASSOC);
            if ($res_word) {
                $sql_info = 'SELECT `id`,`word_parent`,`word_name`,`word_src` FROM `' . $this->table_name . '` WHERE `word_parent` = :parent';
                $sth_info = $this->db->prepare($sql_info);
                $sth_info->bindParam(':parent', $res_word['id'], PDO::PARAM_INT);
                if ($sth_info->execute() == true) {
                    $res_info = $sth_info->fetchAll(PDO::FETCH_ASSOC);
                    if ($res_info) {
                        $return['word'] = $res_word['word_src'];
                        if ($bool_id == true) {
                            $return['word-id'] = $res_word['id'];
                        }
                        foreach ($res_info as $v) {
                            switch ($v['word_name']) {
                                case 'note-en':
                                    //注记英文部分
                                    if ($bool_id == true) {
                                        $return['note-en'] = array('id' => $v['id'], 'src' => $v['word_src']);
                                    } else {
                                        $return['note-en'] = $v['word_src'];
                                    }
                                    break;
                                case 'note-zh':
                                    //注记中文部分
                                    if ($bool_id == true) {
                                        $return['note-zh'] = array('id' => $v['id'], 'src' => $v['word_src']);
                                    } else {
                                        $return['note-zh'] = $v['word_src'];
                                    }
                                    break;
                                case 'pho':
                                    //音标
                                    if ($bool_id == true) {
                                        $return['pho'] = array('id' => $v['id'], 'src' => $v['word_src']);
                                    } else {
                                        $return['pho'] = $v['word_src'];
                                    }
                                    break;
                                case 'des':
                                    //解释
                                    $res_info_v = $this->get_info($v['id']);
                                    if ($res_info_v) {
                                        $arr;
                                        foreach ($res_info_v as $v2) {
                                            if ($bool_id == true) {
                                                $arr['parent'] = $v['id'];
                                                $arr['id'] = $v2['id'];
                                            }
                                            if ($v2['word_name'] == 'p') {
                                                $arr['p'] = $v2['word_src'];
                                            } else {
                                                $arr['d'] = $v2['word_src'];
                                            }
                                        }
                                        $return['des'][] = $arr;
                                    }
                                    break;
                                case 'dict':
                                    //例句
                                    $res_info_v = $this->get_info($v['id']);
                                    if ($res_info_v) {
                                        $arr;
                                        foreach ($res_info_v as $v2) {
                                            if ($bool_id == true) {
                                                $arr['parent'] = $v['id'];
                                                $arr['id'] = $v2['id'];
                                            }
                                            if ($v2['word_name'] == 'en') {
                                                $arr['en'] = $v2['word_src'];
                                            } else {
                                                $arr['zh'] = $v2['word_src'];
                                            }
                                        }
                                        $return['dict'][] = $arr;
                                    }
                                    break;
                                case 'img':
                                    //图片
                                    if ($bool_id == true) {
                                        $return['img'] = array('id' => $v['word_src'], 'src' => $v['word_src']);
                                    } else {
                                        $return['img'] = $v['word_src'];
                                    }
                                    break;
                                case 'voice':
                                    //发音
                                    if ($bool_id == true) {
                                        $return['voice'][] = array('id' => 'id', 'src' => $v['word_src']);
                                    } else {
                                        $return['voice'][] = $v['word_src'];
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }
        return $return;
    }

    /**
     * 编辑信息
     * @param array $infos 新的信息数组
     * @return boolean
     */
    public function edit($infos) {
        $words = $this->view($infos['word']);
        $word = '';
        if ($words) {
            $word = $words['word_src'];
            if ($this->del($words['id']) == false) {
                return false;
            }
        } else {
            $word = $infos['word'];
        }
        return $this->add($word, $infos);
    }

    /**
     * 添加一个文件信息
     * @param int|string $word 单词ID或单词名称
     * @param string $src 路径
     * @param string $type 文件信息类型 img-图片 ; voice-发音
     * @return boolean
     */
    public function upload_file($word, $src, $type) {
        $res_word = $this->view($word);
        if ($res_word) {
            if ($type == 'img') {
                $sql_select = 'SELECT `id` FROM `' . $this->table_name . '` WHERE `word_parent` = :parent AND `word_name` = \'img\'';
                $sth_select = $this->db->prepare($sql_select);
                $sth_select->bindParam(':parent', $res_word['id'], PDO::PARAM_INT);
                if ($sth_select->execute() == true) {
                    $res_select = $sth_select->fetchColumn();
                    if ($res_select) {
                        $sql_update = 'UPDATE `' . $this->table_name . '` SET `word_src` = :src WHERE `id` = :id';
                        $sth_update = $this->db->prepare($sql_update);
                        $sth_update->bindParam(':src', $src, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
                        $sth_update->bindParam(':id', $res_select, PDO::PARAM_INT);
                        return $sth_update->execute();
                    }
                }
                return $this->add_query($res_word['id'], 'img', $src);
            } else {
                return $this->add_query($res_word['id'], 'voice', $src);
            }
        }
        return false;
    }

    /**
     * 从互联网刷新单词信息
     * <p>不删除原有数据，如果能得到数据，则尝试填补新的数据和覆盖旧数据。</p>
     * @param string $word 单词名称
     * @return boolean
     */
    public function refresh($word) {
        $res_word = $this->get($word, true);
        if ($res_word) {
            $url_info = $this->get_url_word($word);
            if ($url_info) {
                //重组数据
                $new_infos = null;
                //注记英文和中文
                $new_infos['note']['en'] = $res_word['note-en'];
                $new_infos['note']['zh'] = $res_word['note-zh'];
                //单词
                $new_infos['word'] = $word;
                //音标
                $new_infos['pho'] = $url_info['pho'];
                //解释
                $new_infos['des'] = $res_word['des'];
                foreach ($url_info['des'] as $v) {
                    $add_bool = true;
                    foreach ($res_word['des'] as $v2) {
                        if ($v['d'] == $v2['d'] && $v['p'] == $v2['p']) {
                            $add_bool = false;
                        }
                    }
                    if ($add_bool == true) {
                        $new_infos['des'][] = array('p' => $v['p'], 'd' => $v['d']);
                    }
                }
                //例句
                $new_infos['dict'] = $res_word['dict'];
                foreach ($url_info['dict'] as $v) {
                    $add_bool = true;
                    foreach ($res_word['dict'] as $v2) {
                        if ($v['en'] == $v2['en'] && $v['zh'] == $v2['zh']) {
                            $add_bool = false;
                        }
                    }
                    if ($add_bool == true) {
                        $new_infos['dict'][] = array('en' => $v['en'], 'zh' => $v['zh']);
                    }
                }
                //图片
                $new_infos['img'] = $url_info['img'];
                //发音
                $new_infos['voice'] = $url_info['voice'];
                return $this->edit($new_infos);
            }
        } else {
            if ($this->add($word) > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * 递归删除单词
     * @param int $id 单词ID
     * @return boolean
     */
    private function del($id) {
        $sql = 'SELECT `id`,`word_parent` FROM `' . $this->table_name . '` WHERE `word_parent` = :id';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        if ($sth->execute() == true) {
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);
            if ($res) {
                foreach ($res as $v) {
                    if ($this->del($v['id']) == false) {
                        return false;
                    }
                }
            }
        }
        $sql_del = 'DELETE FROM `' . $this->table_name . '` WHERE `id` = :id';
        $sth_del = $this->db->prepare($sql_del);
        $sth_del->bindParam(':id', $id, PDO::PARAM_INT);
        return $sth_del->execute();
    }

    /**
     * 获取单元信息
     * @param int $id 上一级ID
     * @return boolean|array
     */
    private function get_info($id) {
        $return = false;
        $sql = 'SELECT `id`,`word_parent`,`word_name`,`word_src` FROM `' . $this->table_name . '` WHERE `word_parent` = :id';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        if ($sth->execute() == true) {
            $return = $sth->fetchAll(PDO::FETCH_ASSOC);
        }
        return $return;
    }

    /**
     * 获取单词ID信息
     * @param int|string $id 单词ID或单词名称
     * @return null|array 数据
     */
    private function view($id) {
        if (is_int($id) == true) {
            $sql = 'SELECT `id`,`word_parent`,`word_name`,`word_src` FROM `' . $this->table_name . '` WHERE `id` = :id';
        } else {
            $sql = 'SELECT `id`,`word_parent`,`word_name`,`word_src` FROM `' . $this->table_name . '` WHERE `word_name` = \'word\' and  `word_src` = :id';
        }
        $sth = $this->db->prepare($sql);
        if (is_int($id) == true) {
            $sth->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            $sth->bindParam(':id', $id, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($sth->execute() == true) {
            return $sth->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    /**
     * 获取单词数据
     * <p>确保数据的完整性</p>
     * @param string $word
     * @return array
     */
    private function get_url_word($word) {
        $return = null;
        $src1 = $this->get_url_qq($word);
        $src2 = $this->get_url_aiciba($word);
        if ($src1 && $src2) {
            //注记
            $return['note']['en'] = '';
            $return['note']['zh'] = '';
            //音标
            $return['pho'] = isset($src1['pho']) ? $src1['pho'] : '';
            if ($return['pho'] == '') {
                $return['pho'] = isset($src2['pho']) == true ? $src2['pho'] : '';
            }
            //解释
            $return['des'] = isset($src1['des']) ? $src1['des'] : '';
            if ($return['des'] == '') {
                $return['des'] = isset($src2['des']) == true ? $src2['des'] : array(array('p' => '', 'd' => ''));
            }
            //例句
            $return['dict'] = isset($src1['dict']) ? $src1['dict'] : '';
            if ($return['dict'] == '') {
                $return['dict'] = isset($src2['dict']) == true ? $src2['dict'] : array(array('en' => '', 'zh' => ''));
            }
            //图片
            $return['img'] = isset($src1['img']) ? $src1['img'] : '';
            if ($return['img'] == '') {
                $return['img'] = isset($src2['img']) == true ? $src2['img'] : '';
            }
            if ($return['img']) {
                $return['img'] = $this->save_url_file($return['img']);
            }
            //单词发音
            $return['voice'] = isset($src1['voice']) ? $src1['voice'] : '';
            if ($return['voice'] == '') {
                $return['voice'] = isset($src2['voice']) == true ? $src2['voice'] : '';
            }
            if ($return['voice']) {
                foreach ($return['voice'] as $k => $v) {
                    $return['voice'][$k] = $this->save_url_file($v);
                }
            }
            if ($return['voice'] == '') {
                $return['voice'] = array('');
            }
        }
        return $return;
    }

    /**
     * 从URL保存文件
     * @param string $url
     * @return boolean|string
     */
    private function save_url_file($url) {
        $return = false;
        $c = $this->get_url($url);
        if ($c) {
            $dir_name = $this->data_dir . DS . date('Ym') . DS . date('d');
            if (corefile::new_dir($dir_name) == true) {
                $file_type_arr = explode('.', $url);
                $file_name = $dir_name . DS . time() . '_' . rand(1, 9999) . '.' . $file_type_arr[count($file_type_arr) - 1];
                if (corefile::edit_file($file_name, $c) == true) {
                    $return = $file_name;
                }
            }
        }
        return $return;
    }

    /**
     * 从QQ获取数据
     * @param string $word
     * @return boolean|array
     */
    private function get_url_qq($word) {
        $return = false;
        $url = 'http://dict.qq.com/dict?q=' . $word;
        $content = $this->get_url($url);
        if ($content) {
            $arr = json_decode($content, true);
            //音标
            if (isset($arr['local'][0]['pho'][0]) == true) {
                $return['pho'] = $arr['local'][0]['pho'][0];
            }
            //解释
            if (isset($arr['local'][0]['des']) == true) {
                foreach ($arr['local'][0]['des'] as $k => $v) {
                    $return['des'][$k]['p'] = isset($v['p']) == true ? $v['p'] : '';
                    $return['des'][$k]['d'] = isset($v['d']) == true ? $v['d'] : '';
                }
            }
            //例句
            if (isset($arr['netsen']) == true) {
                foreach ($arr['netsen'] as $k => $v) {
                    $return['dict'][$k]['en'] = isset($v['es']) == true ? $v['es'] : '';
                    $return['dict'][$k]['zh'] = isset($v['cs']) == true ? $v['cs'] : '';
                }
            }
            //图片
            if (isset($arr['baike'][0]['pic']) == true) {
                $return['img'] = $arr['baike'][0]['pic'];
            }
        }
        return $return;
    }

    /**
     * 从爱词霸获取数据
     * @param string $word
     * @return boolean|array
     */
    private function get_url_aiciba($word) {
        $return = false;
        $url = 'http://dict-co.iciba.com/api/dictionary.php?w=' . $word;
        $content = $this->get_url($url);
        if ($content) {
            $xmltoarray = new plugxmltoarray($content);
            $arr = $xmltoarray->array;
            //音标
            if (isset($arr['dict']['ps'][0]) == true) {
                $return['pho'] = $arr['dict']['ps'][0];
            }
            //解释
            if (isset($arr['dict']['pos']) == true && isset($arr['dict']['acceptation']) == true) {
                if (is_array($arr['dict']['pos']) == true) {
                    foreach ($arr['dict']['pos'] as $k => $v) {
                        $return['des'][$k]['p'] = isset($v) == true ? $v : '';
                        $return['des'][$k]['d'] = isset($arr['dict']['acceptation'][$k]) == true ? $arr['dict']['acceptation'][$k] : '';
                    }
                } else {
                    $return['des'][0]['p'] = $arr['dict']['pos'];
                    $return['des'][0]['d'] = $arr['dict']['acceptation'];
                }
            }
            //例句
            if (isset($arr['dict']['sent']) == true) {
                if (is_array($arr['dict']['sent']) == true) {
                    foreach ($arr['dict']['sent'] as $k => $v) {
                        $return['dict'][$k]['en'] = isset($v['orig']) == true ? $v['trans'] : '';
                        $return['dict'][$k]['zh'] = isset($v['trans']) == true ? $v['trans'] : '';
                    }
                } else {
                    $return['dict'][0]['en'] = $arr['dict']['sent']['orig'];
                    $return['dict'][0]['zh'] = $arr['dict']['sent']['trans'];
                }
            }
            //发音文件
            if (isset($arr['dict']['pron']) == true) {
                if (is_array($arr['dict']['pron']) == true) {
                    foreach ($arr['dict']['pron'] as $k => $v) {
                        $return['voice'][$k] = $v;
                    }
                } else {
                    $return['voice'][0] = $arr['dict']['pron'];
                }
            }
        }
        return $return;
    }

    /**
     * 获取URL数据
     * @param string $url
     * @return string
     */
    private function get_url($url) {
        $this->set_time(30);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 5000);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    /**
     * 设定脚本时长(秒)
     * @param int $time
     */
    private function set_time($time = 30) {
        set_time_limit($time);
    }

}

?>
