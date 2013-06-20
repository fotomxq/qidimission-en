<?php

/**
 * 复习表操作类
 * <p>由于时间过于紧张，配置处理部分BUG较多，所以改为手动add_config_a~d()添加配置值。create()方法并没有发现BUG，所以还会使用$configs配置信息。</p>
 * @author fotomxq <fotomxq.me>
 * @version 3
 * @package mission
 */
class missionreview {

    /**
     * 表名称
     * @since 1
     * @var string 
     */
    private $table_name;

    /**
     * 数据库操作句柄
     * @since 1
     * @var coredb 
     */
    private $db;

    /**
     * POST操作句柄
     * @since 1
     * @var syspost 
     */
    private $post;

    /**
     * Configs配置操作句柄
     * @var sysconfigs 
     */
    private $config;

    /**
     * 类型数组
     * @var array 
     */
    private $types;

    /**
     * post-status
     * @var string 
     */
    private $post_status;

    /**
     * 单词标识符
     * @var string 
     */
    private $post_type_word;

    /**
     * 小节标识符
     * @var string 
     */
    private $post_type_section;

    /**
     * 单元标识符
     * @var string 
     */
    private $post_type_unit;

    /**
     * 配置名前缀
     * @var string 
     */
    private $config_name_prefix = 'MISSION_REVIEW_';

    /**
     * 配置信息
     * <p>0 - 数据类型，该类型根据$types建立</p>
     * <p>1 - 保存的个数上限，必须大于0</p>
     * <p>2 - 达到上限时的清理个数 （正数数字N - 从第0~N个数据；倒数数字-N - 从第0～-N个数据；0 - 不清理），该参数也将影响插入数据方式，插入数据将和清理顺序正好相反</p>
     * <p>3 - 基数计算位移，上限复习时用到的0数据，可以为正负数，绝对值必须小于保存上限</p>
     * <p>4 - 复习上限，达到该上限则允许复习，必须大于0，且必须小于等于保存上限</p>
     * <p>5 - 复习其中多少个单词，必须大于0，且必须小于等于保存上限</p>
     * <p>6 - 复习时是否清理数据</p>
     * @var null|array 
     */
    private $configs = null;

    /**
     * 复习单词总量
     * @var int 
     */
    public $list_row;

    /**
     * 是否到达复习结尾
     * @var boolean 
     */
    public $end;

    /**
     * 初始化
     * @since 1
     * @param coredb $db 数据库句柄
     * @param syspost $post post句柄
     * @param sysconfigs $config config句柄
     * @param array $types 类型数组
     */
    public function __construct(&$db, &$post, &$config, &$types) {
        $this->db = $db;
        $this->table_name = $db->tables['review'];
        $this->post = $post;
        $this->config = $config;
        $this->types = $types;
        $this->post_status = 'public';
        $this->post_type_word = 'word';
        $this->post_type_section = 'section';
        $this->post_type_unit = 'unit';
        $this->end = false;
        $this->reg_config(1, 2, 1, 0, 1, 2, false);
        $this->reg_config(1, 3, 3, 0, 3, 3, true);
        $this->reg_config(2, 1, 1, 0, 1, 1, true);
        $this->reg_config(2, 3, 3, 0, 3, 3, true);
    }

    /**
     * 继续下一个复习
     * @param int $page 偏移值
     * @return boolean
     */
    public function next($page = 1) {
        $this->list_row = $this->view_list_row();
        if ($page >= $this->list_row) {
            $this->end = true;
        }
        $list = $this->view_list($page, 1);
        if ($list) {
            return $list[0];
        }
        return false;
    }

    /**
     * 添加一个复习
     * @param int $id post-id小节ID
     * @param int $type 复习类型，该类型根据$types提供
     * @return boolean
     */
    public function add($id, $type = null) {
        /* 原添加处理，但BUG较多，对课处理不友好，对单元处理存在生成多余BUG。
        if (in_array($type, $this->types) == true || $type == null) {
            if ($type == null) {
                foreach ($this->configs as $k => $v) {
                    if ($this->add_config_value($k, $id) == false) {
                        return false;
                    }
                }
                return true;
            } else {
                $k = array_keys($this->types, $type);
                return $this->add_config_value($k, $id);
            }
        }
        return false;
         */
        $this->add_config_a($id);
        $this->add_config_b($id);
        $this->add_config_c($id, 1, 2);
        $this->add_config_c($id, 3, 3);
        return true;
    }

    /**
     * 设定A条件
     * @param int $id 基准单元ID
     */
    private function add_config_a($id) {
        $res = $this->post->view($id);
        if ($res) {
            $config_value = $this->load_config(0);
            $config_arr = null;
            if ($config_value) {
                $config_arr = explode(',', $config_value);
            }
            if (is_array($config_arr) == true) {
                if (in_array($id, $config_arr) == false) {
                    array_unshift($config_arr, $id);
                }
            } else {
                $config_arr[] = $id;
            }
            if (count($config_arr) > 2) {
                array_pop($config_arr);
            }
            $new_value = implode(',', $config_arr);
            $this->save_config(0, $new_value);
        }
        return false;
    }

    /**
     * 设定B条件
     * @param int $id 基准单元ID
     */
    private function add_config_b($id) {
        $res = $this->post->view($id);
        if ($res) {
            $config_value = $this->load_config(1);
            $config_arr = null;
            if ($config_value) {
                $config_arr = explode(',', $config_value);
            }
            if (count($config_arr) >= 3) {
                $config_arr = null;
            }
            if(is_array($config_arr) == true){
                if(in_array($id, $config_arr) == false){
                    $config_arr[] = $id;
                }
            }else{
                $config_arr[] = $id;
            }
            $new_value = implode(',', $config_arr);
            return $this->save_config(1, $new_value);
        }
        return false;
    }

    /**
     * 设定C\D条件
     * @param int $id 基准单元ID
     */
    private function add_config_c($id, $max, $key) {
        $res = $this->post->view($id);
        if ($res) {
            if ($res['post_parent'] > 0) {
                //判断是否为最后一个单元
                $res_list = $this->get_view_list($res['post_parent'], $this->types[1]);
                $end_bool = false;
                if ($res_list) {
                    if ($res_list[count($res_list) - 1]['id'] == $res['id']) {
                        $end_bool = true;
                    }
                }
                if ($end_bool == true) {
                    $config_value = $this->load_config($key);
                    $config_arr = null;
                    if ($config_value) {
                        $config_arr = explode(',', $config_value);
                    }
                    if (count($config_arr) >= $max) {
                        $config_arr = null;
                    }
                    if (is_array($config_arr) == true) {
                        if (in_array($res['post_parent'], $config_arr) == false) {
                            $config_arr[] = $res['post_parent'];
                        }
                    } else {
                        $config_arr[] = $res['post_parent'];
                    }
                    $new_value = implode(',', $config_arr);
                    return $this->save_config($key, $new_value);
                }
            }
        }
        return false;
    }

    /**
     * 建立复习数据
     * @return boolean
     */
    public function create() {
        //清空数据表内容
        $this->clear();
        //复习单词序列
        $words = null;
        //遍历配置清单
        if (is_array($this->configs) == true) {
            foreach ($this->configs as $config_k => $config_v) {
                //小节ID序列
                $sections = null;
                //获取配置值
                $config_value = $this->load_config($config_k);
                //如果配置内容存在
                if ($config_value) {
                    //判断是否达到复习上限
                    $config_arr = explode(',', $config_value);
                    if ($config_arr) {
                        $config_count = count($config_arr);
                        if ($config_count > 0 && $config_count >= $config_v[4]) {
                            //添加到小节ID序列
                            for ($i = 0; $i <= $config_v[5]; $i++) {
                                //如果是单元则获取小节
                                if (isset($config_arr[$i]) == true) {
                                    if ($config_v[0] == 2) {
                                        $section_list = $this->get_list_id($config_arr[$i], $this->types[1]);
                                        if ($section_list) {
                                            foreach($section_list as $list_v){
                                                $sections[] = $list_v;
                                            }
                                        }
                                    } else {
                                        $sections[] = $config_arr[$i];
                                    }
                                }
                            }
                            //根据规则清理复习数据
                            if ($config_count >= $config_v[1] && $config_v[6] == true) {
                                $this->save_config($config_k, '');
                            }
                        }
                        //如果小节序列存在，则获取相关单词
                        if ($sections !== null) {
                            foreach ($sections as $section_v) {
                                $word_list = $this->get_view_list($section_v, $this->types[0]);
                                if ($word_list) {
                                    foreach ($word_list as $list_v) {
                                        $words[] = $list_v['post_title'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        //添加复习单词数据
        if ($words !== null) {
            foreach ($words as $v) {
                if ($this->add_review($v) == false) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * 清空复习数据
     * @param int $k 键值，等于null则全部清空
     * @return boolean
     */
    public function clear_configs($k = null) {
        if ($k == null) {
            foreach ($this->configs as $k => $v) {
                $config_name = $this->get_config_name($k);
                $this->config->save($config_name, '');
            }
        } else {
            $config_name = $this->get_config_name($k);
            $this->config->save($config_name, '');
        }
        return true;
    }

    /**
     * 获取分支序列
     * @param int $parent 上级ID
     * @param string $type 类型
     * @return boolean|array 无或序列数组
     */
    private function get_list_id($parent, $type) {
        $arr = null;
        $res_list = $this->get_view_list($parent, $type);
        if ($res_list) {
            foreach ($res_list as $v) {
                $arr[] = $v['id'];
            }
            return $arr;
        }
        return false;
    }

    /**
     * 获取下级所有ID
     * @param int $parent 上级ID
     * @param string $type 类型
     * @return null|array 无或数据
     */
    private function get_view_list($parent, $type) {
        return $this->post->view_list(null, null, null, $this->post_status, $type, 1, 9999, 6, false, $parent);
    }

    /**
     * 注册一个配置
     * @param string $type 配置类型键值，该配置根据$types建立，如，2 - 单词；1 - 小节；0 - 单元
     * @param int $limit_max 保存上限，必须大于0，绝对值必须小于保存上限
     * @param string $clear 清理个数 （正数数字N - 从第0~N个数据；倒数数字-N - 从第0～-N个数据；0 - 不清理），该参数也将影响插入数据方式，插入数据将和清理顺序正好相反
     * @param int $offset 基数计算位移，上限清理时用到的0数据，可以为正负数，绝对值必须小于保存上限
     * @param int $review_limit 复习上限，达到该上限则允许复习，必须大于0，必须小于等于保存上限
     * @param int $review_num 复习其中多少个单词，必须大于0，且小于等于保存上限
     * @param boolean $review_clear 复习时是否清理数据
     * @return boolean 添加是否成功
     */
    private function reg_config($type, $limit_max, $clear, $offset, $review_limit, $review_num, $review_clear) {
        if (isset($this->types[$type]) == true && is_int($limit_max) == true && $limit_max > 0 && is_int($offset) == true && abs($offset) < $limit_max && is_int($review_limit) == true && $review_limit > 0 && $review_limit <= $limit_max && is_int($review_num) && $review_num > 0 && $review_num <= $limit_max && is_bool($review_clear) == true) {
            $num = 1;
            if ($this->configs != null) {
                $num = count($this->configs) + 1;
            }
            $config_name = $this->config_name_prefix . $num;
            if ($this->config->reg($config_name, '') == true) {
                $this->configs[] = array($type, $limit_max, $clear, $offset, $review_limit, $review_num, $review_clear);
                return true;
            }
        }
        return false;
    }

    /**
     * 获取配置数据
     * @param int $k 键值
     * @return string 配置值
     */
    private function load_config($k) {
        $config_name = $this->get_config_name($k);
        $config_value = $this->config->load($config_name);
        return $config_value;
    }

    /**
     * 保存配置值
     * @param int $k 键值
     * @param string $value 保存值
     * @return boolean 是否成功
     */
    private function save_config($k, $value) {
        $config_name = $this->get_config_name($k);
        return $this->config->save($config_name, $value);
    }

    /**
     * 获取配置名
     * @param int $k 键值
     * @return string 配置名
     */
    private function get_config_name($k) {
        $config_name = $this->config_name_prefix . ($k + 1);
        return $config_name;
    }

    /**
     * 添加数据
     * @param string $word
     * @return boolean
     */
    private function add_review($word) {
        $sql = 'INSERT INTO `' . $this->table_name . '`(`id`,`review_word`) VALUES(NULL,:word)';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':word', $word, PDO::PARAM_STR);
        return $sth->execute();
    }

    /**
     * 获取单词列表
     * @param int $parent 上一级ID
     * @param int $page 页数
     * @param int $max 页长
     * @return boolean|array
     */
    private function get_word_list($parent = null, $page = 1, $max = 9999) {
        return $this->post->view_list(null, null, null, $this->post_status, $this->types[2], $page, $max, 6, false, $parent);
    }

    /**
     * 为一个配置添加值
     * @param int $k 键值
     * @param int $id 添加的值
     * @return boolean
     */
    private function add_config_value($k, $id) {
        $config_value = $this->load_config($k);
        $arr = null;
        $new_arr = null;
        if ($config_value) {
            $arr = explode(',', $config_value);
            $cut_len = count($arr) - $this->configs[$k][1];
            if ($cut_len > 0) {
                $t = 0;
                for ($i = $this->configs[$k][3]; $i <= $this->configs[$k][2]; $i++) {
                    $new_arr[$t] = $arr[$i];
                    $t++;
                }
            }
        }
        if ($this->configs[$k][2] < 0) {
            array_unshift($arr, $id);
        } else {
            $arr[] = $id;
        }
        $config_save_value = implode(',', $arr);
        return $this->save_config($k, $config_save_value);
    }

    /**
     * 获取列表
     * @param int $page 页数
     * @param int $max 页长
     * @return boolean|array
     */
    private function view_list($page = 1, $max = 1) {
        $sql = 'SELECT `id`,`review_word` FROM `' . $this->table_name . '` ORDER BY `id` ASC LIMIT ' . ($page - 1) * $max . ',' . $max;
        $sth = $this->db->prepare($sql);
        if ($sth->execute() == true) {
            $return = $sth->fetchAll(PDO::FETCH_ASSOC);
            return $return;
        }
        return false;
    }

    /**
     * 获取记录数
     * @since 1
     * @return int
     */
    private function view_list_row() {
        $return = 0;
        $sql = 'SELECT count(id) FROM `' . $this->table_name . '`';
        $sth = $this->db->prepare($sql);
        if ($sth->execute() == true) {
            $return = $sth->fetchColumn();
        }
        return $return;
    }

    /**
     * 清空数据
     * @since 1
     */
    private function clear() {
        $sql = 'TRUNCATE `' . $this->table_name . '`';
        return $this->db->exec($sql);
    }

}

?>
