<?php

/**
 * 进行课堂模式
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package mission
 */
class missionview {

    /**
     * syspost实例引用句柄
     * @var syspost 
     */
    public $post;

    /**
     * sysconfigs实例引用句柄
     * @var sysconfigs 
     */
    private $config;

    /**
     * 用户ID
     * @var int 
     */
    private $user_id;

    /**
     * 状态
     * @var strng 
     */
    public $status;

    /**
     * 是否为小节最后一个单词
     * @var boolean 
     */
    public $section_end;

    /**
     * 当前小节下单词量
     * @var int 
     */
    public $word_row;

    /**
     * 获取当前小节数据
     * @var array 
     */
    public $section_res;

    /**
     * 获取当前单元数据
     * @var array 
     */
    public $unit_res;

    /**
     * 类型组
     * @var array 
     */
    public $types;

    /**
     * 初始化
     * @param syspost $post post实例
     * @param sysconfigs $config config实例
     * @param int $user_id 用户ID
     */
    public function __construct(&$post, &$config, &$user_id) {
        $this->post = $post;
        $this->config = $config;
        $this->user_id = $user_id;
        $this->types = array('word', 'unit', 'class');
        $this->status = 'public';
        $this->section_end = false;
    }

    /**
     * 获取当前小节单词数据
     * @param int $offset 单词列表偏移值
     * @return boolean|array
     */
    public function get($offset = 1) {
        $section_res = $this->get_last_section();
        if ($section_res) {
            $this->section_res = $section_res;
            $this->unit_res = $this->post->view($section_res['post_parent']);
            $word_list_row = $this->view_list_row($section_res['id'], 0);
            if ($word_list_row > 0) {
                $this->word_row = $word_list_row;
                //如果偏移值大于等于总量，则向后进行一个小节
                if ($offset >= $word_list_row) {
                    $this->section_end = true;
                }
                //返回单词数据
                $word_res = $this->view_list($section_res['id'], 0, $offset, 1);
                if ($word_res) {
                    return $word_res[0];
                }
            }
        }
        return false;
    }

    /**
     * 获取数据
     * @param int $id
     * @return type
     */
    public function view($id) {
        return $this->post->view($id);
    }

    /**
     * 获取列表
     * @param int $parent 上一级ID
     * @param int $type 类型键值
     * @param int $page 页数
     * @param int $max 页长
     * @return array|boolean 数据|false
     */
    public function view_list($parent = 0, $type = null, $page = 1, $max = 9999) {
        $post_type = $type === null ? $this->types[2] : $this->types[$type];
        return $this->post->view_list(null, null, null, $this->status, $post_type, $page, $max, 6, false, $parent);
    }

    /**
     * 获取下级所有单词
     * @param int $parent 上一级ID
     * @return null|array 单词数据 eg:array('word','test',...)
     */
    public function view_all_word($parent = 0) {
        $res_list = $this->view_list($parent);
        $arr = null;
        if ($res_list) {
            if ($res_list[0]['post_parent'] > 0 && array_keys($this->types, $res_list[0]['post_type']) == 0) {
                $arr = $res_list;
            } else {
                foreach ($res_list as $list_v) {
                    $v_list_res = $this->view_all_word($list_v['post_id']);
                    if ($v_list_res) {
                        $arr = array_merge($arr, $v_list_res);
                    }
                }
            }
        }
        return $arr;
    }

    /**
     * 获取下级单词总数
     * @param int $parent 上一级ID
     * @return int 总数
     */
    public function view_all_word_row($parent = 0) {
        $num = 0;
        $res = $this->view_list($parent);
        if ($res) {
            $res_row = 0;
            if ($res[0]['post_parent'] > 0 && $res[0]['post_type'] == $this->types[0]) {
                $res_row = (int) $this->view_list_row($parent);
            } else {
                foreach ($res as $v) {
                    $res_row += (int) $this->view_all_word_row($res[0]['id']);
                }
            }
            $num += $res_row;
        }
        return $num;
    }

    /**
     * 获取列表记录数
     * @param int $parent 上一级ID
     * @param int|null $type 类型键值
     * @return int 记录数
     */
    public function view_list_row($parent = 0, $type = null) {
        $post_type = $type === null ? $this->types[0] : $this->types[$type];
        return $this->post->view_list_row(null, null, null, $this->status, $post_type, $parent);
    }

    /**
     * 获取层级关系
     * @param int $id 获取ID
     * @return null|array 空或数据数组 eg:array(array('id'=>'1','title'=>'...'))
     */
    public function view_level($id) {
        $res = $this->view($id);
        $arr = null;
        while ($res) {
            $arr_v['id'] = $res['id'];
            $arr_v['title'] = $res['post_title'];
            $arr_v['type'] = array_keys($this->types, $res['post_type']);
            $arr[] = $arr_v;
            $res = $this->view($res['post_parent']);
        }
        return $arr;
    }

    /**
     * 添加数据
     * @param string $title 标题
     * @param int $type 类型键值
     * @param int $parent 上一级ID
     * @return int 添加后ID
     */
    public function add($title, $type, $parent = 0) {
        return $this->post->add($title, '', $this->types[$type], $parent, $this->user_id, null, '', '', 'public', '');
    }

    /**
     * 编辑ID
     * @param int $id ID
     * @param string $title 标题
     * @return boolean
     */
    public function edit($id, $title) {
        $res = $this->view($id);
        if ($res) {
            return $this->post->edit($res['id'], $title, $res['post_content'], $res['post_type'], $res['post_parent'], $res['post_user'], $res['post_password'], $res['post_name'], $res['post_url'], $res['post_status'], $res['post_meta']);
        }
        return false;
    }

    /**
     * 删除ID
     * <p>递归删除所有分支内容</p>
     * @param int $id ID
     * @return boolean 是否删除
     */
    public function del($id) {
        return $this->post->del_parent($id);
    }

    /**
     * 设置课堂单元
     * <p>如果不是单元，则自动向下或向上获取单元。</p>
     * @param int $id 单元ID
     * @return boolean 是否成功
     */
    public function save_mission($id) {
        $res = $this->view($id);
        if ($res) {
            $save_id = $this->get_last_section_id();
            if ($res['post_type'] == $this->types[1]) {
                $save_id = $res['id'];
            } else {
                if ($res['post_type'] == $this->types[2]) {
                    $res_list = $this->view_list($res['id'], 1, 1, 1);
                    if ($res_list) {
                        $save_id = $res_list[0]['id'];
                    }
                } else {
                    $save_id = $res['post_parent'];
                }
            }
            return $this->save_last_section($save_id);
        }
        return false;
    }

    /**
     * 根据小节ID向后移动一个小节
     * @param array $section_res 小节数据
     * @return boolean
     */
    public function next_section($section_res = null) {
        //获取该小节的单元下所有小节
        if ($section_res == null) {
            $section_res = $this->section_res;
        }
        $section_list = $this->view_list($section_res['post_parent'], 1);
        if ($section_list) {
            //如果下一小节存在，则返回
            foreach ($section_list as $k => $v) {
                if ($v['id'] == $section_res['id'] && isset($section_list[$k + 1]) == true) {
                    return $this->save_last_section($section_list[$k + 1]['id']);
                }
            }
            //如果下一小节不存在，则获取下一单元
            $unit_list = $this->view_list();
            if ($unit_list) {
                $unit_id = 0;
                foreach ($unit_list as $k => $v) {
                    if ($v['id'] == $section_res['post_parent']) {
                        if (isset($unit_list[$k + 1]) == true) {
                            $unit_id = $unit_list[$k + 1]['id'];
                        }
                    }
                }
                if ($unit_id > 0) {
                    $section_list = $this->view_list($unit_id, 1, 1, 1);
                    if ($section_list) {
                        return $this->save_last_section($section_list[0]['id']);
                    }
                }
            }
        }
        return false;
    }

    /**
     * 保存当前进行的小节ID
     * @param int $id 小节ID
     * @return boolean
     */
    private function save_last_section($id) {
        return $this->config->save('LAST_MISSION_ID', $id);
    }

    /**
     * 读取当前进行的小节ID
     * @return int
     */
    private function get_last_section_id() {
        return $this->config->load('LAST_MISSION_ID');
    }

    /**
     * 获取当前小节数据
     * <p>如果不存在，则从数据库第一个小节获取；</p>
     * <p>如果还是不存在，则通过数据库第一个单词的小节获取。</p>
     * @return boolean|array
     */
    private function get_last_section() {
        $last_id = $this->get_last_section_id();
        $res = null;
        if ($last_id) {
            $res = $this->post->view($last_id);
            //判断是否为小节
            if ($res) {
                if ($res['post_type'] == $this->types[1]) {
                    $word_row = $this->view_list_row($res['id']);
                    if ($word_row > 0) {
                        return $res;
                    }
                }
            }
            //如果不存在，则查找数据库第一个小节返回
            $res = $this->view_list(null, 1, 1, 1);
            if ($res) {
                if ($res[0]['post_type'] == $this->types[1]) {
                    $word_row = $this->view_list_row($res[0]['id']);
                    if ($word_row > 0) {
                        $this->save_last_section($res[0]['id']);
                        return $res[0];
                    }
                }
            }
            //如果不存在，则查看最近一个单词的上一级小节返回
            $word_res = $this->view_list(null, 0, 1, 1);
            if ($word_res) {
                $res = $this->post->view($word_res[0]['post_parent']);
                if ($res) {
                    if ($res['post_type'] == $this->types[1]) {
                        return $res;
                    }
                }
            }
        }
        return false;
    }

}

?>
