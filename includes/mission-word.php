<?php

/**
 * 获取单词信息
 * @author fotomxq <fotomxq.me>
 * @version 3
 * @package mission
 */
class MissionWord {

    /**
     * 单词表名称
     * @var string 
     */
    public $tableName;

    /**
     * 数据库操作句柄
     * @var coredb 
     */
    public $db;

    /**
     * 组键值字符串
     * @var array 
     */
    private $keyData = array('dict' => 'en|zh', 'des' => 'p|d', 'note' => 'en|zh');

    /**
     * 从互联网获取数据超时时间(毫秒)
     * @var int 
     */
    private $urlLimitTime = 3000;

    /**
     * 初始化
     * @param coredb $db 数据库操作句柄
     */
    public function __construct(&$db) {
        $this->tableName = $db->tables['wordinfo'];
        $this->db = $db;
    }

    /**
     * 获取相似单词列表
     * @param string $word 单词
     * @param int $max 页长
     * @return null|array 单词列表数据
     */
    public function getLikeWord($word, $max = 5) {
        $sql = 'SELECT `info_word` as `word` FROM `' . $this->tableName . '` WHERE `info_word` LIKE \'' . $word . '%\' LIMIT 0,:max';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':max', $max, PDO::PARAM_INT);
        if ($sth->execute() == true) {
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        }
        return null;
    }

    /**
     * 获取单词信息
     * @param string $word 单词
     * @return null|array 信息数据
     */
    public function getWordInfo($word) {
        $sql = 'SELECT `info_word` as `word`,`info_pho` as `pho`,`info_img` as `img`,`info_voice` as `voice`,`info_des` as `des`,`info_note` as `note`,`info_dict` as `dict` FROM `' . $this->tableName . '` WHERE `info_word` = :word';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':word', $word, PDO::PARAM_INT);
        if ($sth->execute() == true) {
            $res = $sth->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                $res['des'] = $this->getGroupArr($res['des']);
                $res['note'] = $this->getGroupArr($res['note']);
                $res['dict'] = $this->getGroupArr($res['dict']);
                return $res;
            }
        }
        return null;
    }

    /**
     * 保存单词数据
     * <p>如果单词不存在，则尝试联网获取。</p>
     * @param string $word 单词
     * @param array $infos 单词数据
     * @param boolean $autoURL 是否为首次添加数据（防止成为死循环）
     * @return boolean
     */
    public function saveWordInfo($word, $infos, $autoURL = true) {
        $wordInfos = $this->getWordInfo($word);
        if ($wordInfos) {
            $sql = 'UPDATE `' . $this->tableName . '` SET `info_word` = :word,`info_pho` = :pho,`info_img` = :img,`info_voice` = :voice,`info_des` = :des,`info_note` = :note,`info_dict` = :dict WHERE `info_word` = :setword';
            $sth = $this->db->prepare($sql);
            $sth->bindParam(':word', $word, PDO::PARAM_STR);
            $sth->bindParam(':pho', $infos['pho'], PDO::PARAM_STR);
            $sth->bindParam(':img', $infos['img'], PDO::PARAM_STR);
            $sth->bindParam(':voice', $infos['voice'], PDO::PARAM_STR);
            $infos['des'] = $this->getGroupStr('des', $infos['des']);
            $sth->bindParam(':des', $infos['des'], PDO::PARAM_STR);
            $infos['note'] = $this->getGroupStr('note', $infos['note']);
            $sth->bindParam(':note', $infos['note'], PDO::PARAM_STR);
            $infos['dict'] = $this->getGroupStr('dict', $infos['dict']);
            $sth->bindParam(':dict', $infos['dict'], PDO::PARAM_STR);
            $sth->bindParam(':setword', $infos['word'], PDO::PARAM_STR);
            if ($sth->execute() == true) {
                return true;
            }
        } else {
            $wordId = $this->addQueryInfo($word, $infos['pho'], '', '', '', '', '');
            if ($wordId > 0) {
                if ($autoURL == true) {
                    return $this->getURLInfo($word);
                }
            }
        }
        return false;
    }

    /**
     * 从互联网刷新数据
     * <p>如果发现单词项目为空，且网上有相关数据，则尝试替换。</p>
     * <p>例句，先拆解现有数据并逐个比对是否存在，如果不存在则添加。</p>
     * @param string $word 单词
     * @return boolean
     */
    public function getURLInfo($word) {
        $wordInfos = $this->getWordInfo($word);
        if ($wordInfos) {
            //从QQ获取数据
            $dataQQ = $this->getURLQQ($word);
            if ($wordInfos['pho'] == '' && isset($dataQQ['pho']) == true) {
                $wordInfos['pho'] = $dataQQ['pho'];
            }
            if ($wordInfos['des'] == '' && isset($dataQQ['des']) == true) {
                $wordInfos['des'] = $dataQQ['des'];
            }
            if (isset($dataQQ['dict']) == true) {
                if ($wordInfos['dict'] == '') {
                    $wordInfos['dict'] = $dataQQ['dict'];
                } else {
                    $wordInfos['dict'] = $this->margerGroup($wordInfos['dict'], $dataQQ['dict']);
                }
            }
        }
        return $this->saveWordInfo($word, $wordInfos, false);
    }

    /**
     * 输出图片
     * @param string $src 图片文件路径
     */
    public function headerImg($src) {
        try {
            if ($src == '') {
                $src = DIR_LIB . DS . 'img' . DS . 'noimg_0' . rand(1, 4) . '.png';
                header("Content-type: image/png;charset=utf-8");
                echo file_get_contents($src);
            } else {
                $srcSize = getimagesize($src);
                $img = null;
                switch ($srcSize[2]) {
                    case 1:
                        $img = imagecreatefromgif($src);
                        break;
                    case 2:
                        $img = imagecreatefromjpeg($src);
                        break;
                    case 3:
                        $img = imagecreatefrompng($src);
                        break;
                    case 6:
                        $img = imagecreatefromwbmp($src);
                        break;
                }
                if ($img !== null) {
                    //如果是本地图库
                    if (substr($src, 8, 9) == 'word-imgs') {
                        $newW = 209;
                        $newH = $srcSize[1] - 355;
                        $newImg = imagecreatetruecolor($newW, $newH);
                        imagecopy($newImg, $img, 0, 0, 31, 31, $newW, $newH);
                        $img = $newImg;
                    }
                    //输出图片
                    header("Content-type: image/png;charset=utf-8");
                    imagepng($img);
                    imagedestroy($img);
                }
            }
        } catch (Exception $e) {
            
        }
    }

    /**
     * 合并两个二维数组
     * <p>确保不会出现重复的值。</p>
     * @param array $infos 原数组
     * @param array $news 新数组
     * @return null|array 新的数组
     */
    private function margerGroup($infos, $news) {
        if ($infos) {
            if ($news) {
                $arr = $infos;
                foreach ($news as $newV) {
                    $addOn = false;
                    foreach ($infos as $infoV) {
                        foreach ($newV as $newV2K => $newV2V) {
                            if (isset($infoV[$newV2K]) == true && $newV2V) {
                                if ($infoV[$newV2K] != $newV2V) {
                                    $addOn = true;
                                    break;
                                }
                            }
                        }
                    }
                    if ($addOn == true) {
                        $arr[] = $newV;
                    }
                }
                return $arr;
            } else {
                return $infos;
            }
        } else {
            return $news;
        }
        return null;
    }

    /**
     * 从QQ词典获取数据
     * @param string $word 单词
     * @return null|array 数据数组
     */
    private function getURLQQ($word) {
        $return = false;
        $url = 'http://dict.qq.com/dict?q=' . $word;
        $content = $this->getURL($url);
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
     * 获取互联网的数据
     * @param string $url 地址
     * @return string 反馈的数据
     */
    private function getURL($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->urlLimitTime);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    /**
     * 执行添加单词信息
     * @param string $word 单词
     * @param string $pho 音标
     * @param string $img 图片文件路径
     * @param string $voice 发音文件路径
     * @param array $des 解释
     * @param array $note 注记
     * @param array $dict 例句
     * @return int 单词信息ID
     */
    private function addQueryInfo($word, $pho, $img, $voice, $des, $note, $dict) {
        $sql = 'INSERT INTO `' . $this->tableName . '`(`id`,`info_word`,`info_pho`,`info_img`,`info_voice`,`info_des`,`info_note`,`info_dict`) VALUES(NULL,:word,:pho,:img,:voice,:des,:note,:dict)';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':word', $word, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':pho', $pho, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':img', $img, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':voice', $voice, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $des = $this->getGroupStr('des', $des);
        $sth->bindParam(':des', $des, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $note = $this->getGroupStr('note', $note);
        $sth->bindParam(':note', $note, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $dict = $this->getGroupStr('dict', $dict);
        $sth->bindParam(':dict', $dict, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            return $this->db->lastInsertId();
        }
        return 0;
    }

    /**
     * 获取组修改字符串
     * <p>eg: array(array('en'=>'...','zh'=>'...'),...)</p>
     * @param string $key 组键值
     * @param array $valueArr 组信息
     * @return string
     */
    private function getGroupStr($key, $valueArr) {
        if ($valueArr) {
            $str = $this->keyData[$key];
            $len = count(explode('|', $str));
            foreach ($valueArr as $vArr) {
                if ($vArr && count($vArr) == $len) {
                    $vStr = '';
                    foreach ($vArr as $valV) {
                        $valV = str_replace('|', '-', $valV);
                        $vStr .= '|' . $valV;
                    }
                    $vStr = substr($vStr, 1);
                    $str .= '||' . $vStr;
                }
            }
            return $str;
        }
        return '';
    }

    /**
     * 获取组信息数组
     * @param string $value 组信息字符串
     * @return array 信息数组
     */
    public function getGroupArr($value) {
        if ($value != '') {
            $valArr = explode('||', $value);
            if ($valArr) {
                $returnArr = null;
                $keyArr = explode('|', $valArr[0]);
                foreach ($valArr as $k => $v) {
                    if ($v && $k > 0) {
                        $vArr = explode('|', $v);
                        $returnArr[$k - 1] = null;
                        foreach ($keyArr as $kKey => $kVal) {
                            $returnArr[$k - 1][$kVal] = isset($vArr[$kKey]) == true ? $vArr[$kKey] : '';
                        }
                    }
                }
                return $returnArr;
            }
        }
        return null;
    }

}

?>
