<?php

/**
 * POST类
 * @author fotomxq <fotomxq.me>
 * @version 10
 * @package SYS
 */
class syspost {

    /**
     * Type标识组
     * @since 5
     * @var array 
     */
    private $type_values = array('file' => 'file', 'unit' => 'unit', 'class' => 'class', 'word' => 'word');

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
     * 操作IP ID
     * @since 1
     * @var int
     */
    private $ip_id;

    /**
     * 字段列表
     * @since 1
     * @var array 
     */
    private $fields;

    /**
     * 初始化
     * @since 1
     * @param coredb $db 数据库操作句柄
     * @param int $ip_id IP ID
     */
    public function __construct(&$db, $ip_id) {
        $this->db = $db;
        $this->table_name = $db->tables['posts'];
        $this->ip_id = $ip_id;
        $this->fields = array('id', 'post_title', 'post_content', 'post_date', 'post_modified', 'post_type', 'post_order', 'post_parent', 'post_user', 'post_password', 'post_name', 'post_url', 'post_status', 'post_meta');
    }

    /**
     * 查询列表
     * @since 10
     * @param string $user 用户ID
     * @param string $title 搜索标题
     * @param string $content 搜索内容
     * @param string $status 状态 public|private|trush|null-删除该条件
     * @param string $type 识别类型 message|text|addressbook|messageboard|file
     * @param int $page 页数
     * @param int $max 页长
     * @param int $sort 排序字段键值
     * @param boolean $desc 是否倒序
     * @param int $parent 上一级ID null-无条件|''-为非0|int-为某个值
     * @param string $name 名称 null-等于空值|''-如果非空且空字符串则删除该条件|string-等于字符串
     * @param string $pw 搜索密码或SHA1识别码
     * @param int $sort_w 筛选排序值
     * @param string $sort_q 筛选排序等式符号 <|=|>|>=|<=
     * @return boolean
     */
    public function view_list($user = null, $title = null, $content = null, $status = 'public', $type = 'text', $page = 1, $max = 10, $sort = 6, $desc = true, $parent = null, $name = '', $pw = null, $sort_w = null, $sort_q = null) {
        $return = false;
        $sql_where = '';
        if ($title) {
            $title = '%' . $title . '%';
            $sql_where .= ' OR `post_title`=:title';
        }
        if ($content) {
            $content = '%' . $content . '%';
            $sql_where .= ' OR `post_content`=:content';
        }
        if ($sql_where) {
            $sql_where = '(' . substr($sql_where, 4) . ') AND';
        }
        if ($user) {
            $sql_where = $sql_where . ' `post_user`=:user AND';
        }
        if ($parent !== null) {
            if ($parent === '') {
                $sql_where = $sql_where . ' `post_parent`!=0 AND';
            } else {
                $sql_where = $sql_where . ' `post_parent`=:parent AND';
            }
        }
        if ($name !== null) {
            //如果$name非null且非空
            //可以提交空字符串以废除该条件
            if ($name) {
                $sql_where = $sql_where . ' `post_name`=:name AND';
            }
        } else {
            $sql_where = $sql_where . ' `post_name` is NULL AND';
        }
        if ($pw !== null) {
            $sql_where = $sql_where . ' `post_password`=:password AND';
        }
        if ($status !== null) {
            $sql_where = $sql_where . ' `post_status` = :status AND';
        }
        if ($sort_w !== null && $sort_q !== null) {
            $sql_where = $sql_where . ' `post_order` ' . $sort_q . ' :sort_w AND';
        }
        $sql_desc = $desc ? 'DESC' : 'ASC';
        $sql = 'SELECT `id`,`post_title`,`post_date`,`post_modified`,`post_type`,`post_order`,`post_parent`,`post_user`,`post_password`,`post_name`,`post_url`,`post_status` FROM `' . $this->table_name . '` WHERE ' . $sql_where . ' `post_type`=:type ORDER BY ' . $this->fields[$sort] . ' ' . $sql_desc . ' LIMIT ' . ($page - 1) * $max . ',' . $max;
        $sth = $this->db->prepare($sql);
        if ($title) {
            $sth->bindParam(':title', $title, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($content) {
            $sth->bindParam(':content', $content, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($user) {
            $sth->bindParam(':user', $user, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($parent !== null && $parent !== '') {
            $sth->bindParam(':parent', $parent, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($name) {
            $sth->bindParam(':name', $name, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($pw !== null) {
            $sth->bindParam(':password', $pw, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($status !== null) {
            $sth->bindParam(':status', $status, PDO::PARAM_STR);
        }
        if ($sort_w !== null && $sort_q !== null) {
            $sth->bindParam(':sort_w', $sort_w, PDO::PARAM_INT);
        }
        $type = $this->get_type($type);
        $sth->bindParam(':type', $type, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            $return = $sth->fetchAll(PDO::FETCH_ASSOC);
        }
        return $return;
    }

    /**
     * 获取条件下的记录数
     * @since 7
     * @param string $user 用户ID
     * @param string $title 搜索标题
     * @param string $content 搜索内容
     * @param string $status 状态 public|private|trush|null-删除该条件
     * @param string $type 识别类型 message|text|addressbook|messageboard|file
     * @param int $parent 上一级ID null-无条件|''-为非0|int-为某个值
     * @param string $name 名称 null-等于空值|''-如果非空且空字符串则删除该条件|string-等于字符串
     * @param string $pw 搜索密码或SHA1识别码
     * @return boolean
     */
    public function view_list_row($user = null, $title = null, $content = null, $status = 'public', $type = 'text', $parent = null, $name = '', $pw = null) {
        $return = false;
        $sql_where = '';
        if ($title) {
            $title = '%' . $title . '%';
            $sql_where .= ' OR `post_title`=:title';
        }
        if ($content) {
            $content = '%' . $content . '%';
            $sql_where .= ' OR `post_content`=:content';
        }
        if ($sql_where) {
            $sql_where = '(' . substr($sql_where, 4) . ') AND';
        }
        if ($user) {
            $sql_where = $sql_where . ' `post_user`=:user AND';
        }
        if ($parent !== null) {
            if ($parent === '') {
                $sql_where = $sql_where . ' `post_parent`!=0 AND';
            } else {
                $sql_where = $sql_where . ' `post_parent`=:parent AND';
            }
        }
        if ($name !== null) {
            //如果$name非null且非空
            //可以提交空字符串以废除该条件
            if ($name) {
                $sql_where = $sql_where . ' `post_name`=:name AND';
            }
        } else {
            $sql_where = $sql_where . ' `post_name` is NULL AND';
        }
        if ($pw !== null) {
            $sql_where = $sql_where . ' `post_password` = :password AND';
        }
        if ($status !== null) {
            $sql_where = $sql_where . ' `post_status` = :status AND';
        }
        $sql = 'SELECT COUNT(id) FROM `' . $this->table_name . '` WHERE ' . $sql_where . ' `post_type`=:type';
        $sth = $this->db->prepare($sql);
        if ($title) {
            $sth->bindParam(':title', $title, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($content) {
            $sth->bindParam(':content', $content, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($user) {
            $sth->bindParam(':user', $user, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($parent !== null && $parent !== '') {
            $sth->bindParam(':parent', $parent, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($name) {
            $sth->bindParam(':name', $name, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($pw) {
            $sth->bindParam(':password', $pw, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($status !== null) {
            $sth->bindParam(':status', $status, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        $type = $this->get_type($type);
        $sth->bindParam(':type', $type, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            $return = $sth->fetchColumn();
        }
        return $return;
    }

    /**
     * 计算某字段数据
     * @since 8
     * @param string $type 类型标识
     * @param int $user 用户ID
     * @param string $field 计算字段
     * @param string $date_start 开始时间
     * @param string $date_end 结束时间
     * @return int 结果值
     */
    public function sum_fields($type = 'performance', $user = null, $field = 'post_url', $date_start = null, $date_end = null) {
        $return = 0;
        $sql_where = '';
        if ($user) {
            $sql_where = $sql_where . ' `post_user`=:user AND';
        }
        if ($date_start) {
            $sql_where .= ' `post_date` > :start AND';
        }
        if ($date_end) {
            $sql_where .= ' `post_date` < :end AND';
        }
        $sql = 'SELECT SUM(`' . $field . '`) FROM `' . $this->table_name . '` WHERE ' . $sql_where . ' `post_type` = :type';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':type', $type, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        if ($user) {
            $sth->bindParam(':user', $user, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($date_start) {
            $sth->bindParam(':start', $date_start, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($date_end) {
            $sth->bindParam(':end', $date_end, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($sth->execute() == true) {
            $return = $sth->fetchColumn();
        }
        return $return;
    }

    /**
     * 查询ID
     * @since 1
     * @param int $id 主键
     * @return boolean|array
     */
    public function view($id) {
        $return = false;
        if ($this->check_int($id) == false) {
            return $return;
        }
        $sql = 'SELECT `' . implode('`,`', $this->fields) . '` FROM `' . $this->table_name . '` WHERE `id` = :id';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':id', $id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            $return = $sth->fetch(PDO::FETCH_ASSOC);
        }
        return $return;
    }

    /**
     * 添加新的记录
     * @since 7
     * @param string $title 标题
     * @param string $content 内容
     * @param string $type 类型
     * @param int $parent 上一级ID
     * @param int $user 用户ID
     * @param string $pw 密码SHA1或匹配值
     * @param string $name 媒体文件原名称
     * @param string $url 媒体路径或内容访问路径
     * @param string $status 状态 public|private|trash
     * @param string $meta 媒体文件访问头信息
     * @return int 0或记录ID
     */
    public function add($title, $content, $type, $parent, $user, $pw, $name, $url, $status, $meta) {
        $return = 0;
        $sql = 'INSERT INTO `' . $this->table_name . '`(`post_title`,`post_content`,`post_date`,`post_ip`,`post_type`,`post_order`,`post_parent`,`post_user`,`post_password`,`post_name`,`post_url`,`post_status`,`post_meta`) VALUES(:title,:content,NOW(),:ip,:type,0,:parent,:user,:pw,:name,:url,:status,:meta)';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':title', $title, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':content', $content, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':ip', $this->ip_id, PDO::PARAM_INT);
        $type = $this->get_type($type);
        $sth->bindParam(':type', $type, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':parent', $parent, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':user', $user, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':pw', $pw, PDO::PARAM_STR);
        $sth->bindParam(':name', $name, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':url', $url, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':status', $status, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':meta', $meta, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            $return = $this->db->lastInsertId();
            $this->check_order();
        }
        return $return;
    }

    /**
     * 编辑记录
     * @since 7
     * @param int $id 主键
     * @param string $title 标题
     * @param string $content 内容
     * @param string $type 类型
     * @param int $parent 上一级ID
     * @param int $user 用户ID
     * @param string $pw 密码SHA1或匹配值
     * @param string $name 媒体文件原名称
     * @param string $url 媒体路径或内容访问路径
     * @param string $status 状态 public|private|trash
     * @param string $meta 媒体文件访问头信息
     * @return boolean
     */
    public function edit($id, $title, $content, $type, $parent, $user, $pw, $name, $url, $status, $meta) {
        $return = false;
        $sql = 'UPDATE `' . $this->table_name . '` SET `post_title`=:title,`post_content`=:content,`post_type`=:type,`post_parent`=:parent,`post_user`=:user,`post_password`=:pw,`post_name`=:name,`post_url`=:url,`post_status`=:status,`post_meta`=:meta WHERE `id`=:id';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':title', $title, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':content', $content, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $type = $this->get_type($type);
        $sth->bindParam(':type', $type, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':parent', $parent, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':user', $user, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':pw', $pw, PDO::PARAM_STR);
        $sth->bindParam(':name', $name, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':url', $url, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':status', $status, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':meta', $meta, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':id', $id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            $return = true;
        }
        return $return;
    }

    /**
     * 将ID和目标ID位置对调
     * @since 10
     * @param int $id
     * @param int $dest_id
     * @return boolean
     */
    public function edit_order($id, $dest_id) {
        $return = false;
        $src_res = $this->view($id);
        $dest_res = $this->view($dest_id);
        if ($src_res && $dest_res) {
            $sql = 'UPDATE `' . $this->table_name . '` SET `post_order` = :order WHERE `id` = :id';
            $sth = $this->db->prepare($sql);
            $sth->bindParam(':id', $id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
            $sth->bindParam(':order', $dest_res['post_order'], PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
            if ($sth->execute() == true) {
                $sth->bindParam(':id', $dest_id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
                $sth->bindParam(':order', $src_res['post_order'], PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
                if ($sth->execute() == true) {
                    $return = true;
                }
            }
        }
        return $return;
    }

    /**
     * 删除post
     * @since 2
     * @param int $id 主键
     * @return boolean
     */
    public function del($id) {
        if ($this->check_int($id) == false) {
            return false;
        }
        $sql = 'DELETE FROM `' . $this->table_name . '` WHERE `id` = :id';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':id', $id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        return $sth->execute();
    }

    /**
     * 删除ID以及其子ID
     * <p>递归处理所有层级</p>
     * @since 3
     * @param int $id ID
     * @return boolean
     */
    public function del_parent($id) {
        $sql = 'SELECT `id` FROM `' . $this->table_name . '` WHERE `post_parent` = :id';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':id', $id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);
            if ($res) {
                foreach ($res as $v) {
                    if ($this->del_parent($v['id']) == false) {
                        return false;
                    }
                }
            }
        }
        $sql_delete = 'DELETE FROM `' . $this->table_name . '` WHERE `id` = :id';
        $sth_delete = $this->db->prepare($sql_delete);
        $sth_delete->bindParam(':id', $id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        return $sth_delete->execute();
    }

    /**
     * 整理全局order
     * <p>将所有为post_order=0的记录设置为post_order=id</p>
     * @since 10
     */
    private function check_order() {
        $return = false;
        if ($this->db->exec('UPDATE `' . $this->table_name . '` SET `post_order` = `id` WHERE `post_order` = \'0\'') !== false) {
            $return = true;
        }
        return $return;
    }

    /**
     * 过滤数字
     * @since 1
     * @param int $int
     * @return int|boolean
     */
    private function check_int($int) {
        return filter_var($int, FILTER_VALIDATE_INT);
    }

    /**
     * 获取类型标识
     * @since 2
     * @param string $type
     * @return string
     */
    private function get_type($type) {
        return $this->type_values[$type];
    }

}

?>
