<?php

/**
 * 单体bean的基类
 * $d是一个字典，对应数据库表的各个字段和值
 *
 * @author zhf
 * 
 * 2011.12.2 12:36:21
 *
 */
abstract class bean {
    //实例bean 配置项
    const DB_NAME = NULL;          //string 数据库配置名，cfg_db配置名。     可选
    const TABLE_NAME = NULL;       //string 数据库表名                     可选
    const PRIMARY_KEY_NAME = NULL; //string 数据表主键名                   可选
    //实例属性

    private $info = null;          //bean的类信息。'class_name'=>类名,'db_name'=>数据库配置名,'table_name'=>表名,'pk_name'=>主键名,'table_info'=>表结构信息
    private $db_data = array();    //数据，与数据库表中的字段相对应
    private $data = array();       //其他相关数据
    private $err_msg = NULL;       //错误信息，当执行出现错误的时候，这里将储存出错信息
    
    
    public function err_msg($m=NULL){
        if($m===NULL){
            return $this->err_msg;
        }
        $this->err_msg=$m;
    }
    public function is_empty(){
        return empty($this->db_data[$this->info['pk_name']]);
    }
    
    public static function get_class_info() {
        $cname = get_called_class();
        if (!mc::$B_CLASS_INFO[$cname]) {
            //获取数据库连接配置名称 cfg_db 配置名称
            ($dbname = $cname::DB_NAME) || $dbname = NULL;
            //获取表名称
            ($tname = $cname::TABLE_NAME) || substr($cname, 0, 2) != 'b_' || $tname = substr($cname, 2);
            //获取数据表结构。
            if ($tname) {
                $sql = "DESC " . $tname;
                $tinfo = db::i($dbname)->dm_query($sql);
            }
            if (!$tname || !$tinfo) {
                throw new Exception('错误：取不到BEAN:[' . $cname . '] 的表结构。');
            }
            if (!($pname = $cname::PRIMARY_KEY_NAME)) {
                foreach ($tinfo as $col) {
                    if ($col['Key'] == 'PRI') {
                        $pname = $col['Field'];
                        break;
                    }
                }
            }
            if (!$pname) {
                throw new Exception('错误：取不到BEAN:[' . $cname . '] 的主键字段名称。');
            }
            mc::$B_CLASS_INFO[$cname] = array(
                'class_name' => $cname,
                'db_name' => $dbname,
                'table_name' => $tname,
                'pk_name' => $pname,
                'table_info' => $tinfo
            );
        }
        return mc::$B_CLASS_INFO[$cname];
    }
    
    /**
     * 对象实例构造方法。如果使用主键构造，建议使用i($pk)函数。
     * @param int       $pk   主键值
     *
     */
    public function __construct($pk=NULL) {
        $this->info = self::get_class_info();
        $this->init($pk);
    }

    /**
     * 获得用户对象缓存键名方法。
     * @param int  $pk 主键id
     * @param string $ver
     * @return string 缓存键值
     */
    private function get_cache_key($pk=NULL, $ver='') {
        $pk || ($pk = $this->{$this->info['pk_name']});
        return '|'.$this->info['class_name'] . '|' . $pk . '|' . $ver;
    }

    /**
     * 根据一个实例，更新缓存的一个条目。
     *
     * @return TRUE|FALSE
     */
    public function set_cache() {
        $key = $this->get_cache_key();
        mc::$B_CLASS_LIST[$key] = array($this->db_data, $this->data);
        return mc::i()->set($this->get_cache_key(), mc::$B_CLASS_LIST[$key]);
    }

    /**
     * 获取缓存中的数据。
     *
     * @return $mixed
     */
    public function get_cache() {
        //获取内存中对象数据，然后再找memcache中的。
        $key = $this->get_cache_key();
        if (!(($rs = util::arr(mc::$B_CLASS_LIST, $key)) || ($rs = mc::i()->get($key)))) {
            return FALSE;
        }
        list ($this->db_data, $this->data) = $rs;
        return TRUE;
    }

    /**
     * 根据一个实例，删除缓存的一个条目。
     * @return TRUE|FALSE
     */
    public function del_cache($pk=NULL) {
        $key = $this->get_cache_key($pk);
        unset(mc::$B_CLASS_LIST[$key]);
        return mc::i()->delete($key);  //从缓存里删除
    }

    /**
     * 实例创建方法，做实际工作，一般被__construct构造方法所调用。
     * @param int       $pk  主键值
     * @return 对象实例
     */
    protected function init($pk = NULL) {
        ($pk && ($this->{$this->info['pk_name']} = $pk)) || ($pk = $this->{$this->info['pk_name']});
        if (!$pk) {
            $this->err_msg("初始化空对象.");
            foreach ($this->info['table_info'] as $item) {
                $this->db_data[$item['Field']] = $item['Default'];
            }
            $this->db_data[$this->info['pk_name']] = NULL;
            return FALSE;
        }
        if (!$this->get_cache()) {
            $this->err_msg("CACHE数据失败.");
            $sql = "SELECT * FROM {$this->info['table_name']} WHERE {$this->info['pk_name']}=" . db::escape($pk);
            $this->db_data = db::i($this->info['db_name'])->once_query($sql);
            if ($this->db_data) {
                $this->set_cache();
            }
        }
        if ($this->db_data) {
            return true;
        }
        $this->err_msg("初始化数据失败.");
        return FALSE;
    }

    /**
     *
     * bean对象创建的静态方法。
     * 
     * 
     * @param int $pk  主键值
     * @return bean    实例
     */
    public static function i($pk) {
        if(!$pk){
            return NULL;
        }
        $info = self::get_class_info();
        $cname = $info['class_name'];
        $ints = new $cname($pk);
        return $ints->is_empty() ? NULL : $ints ;
    }

    /**
     * 获取修改后的数据
     * @return array 被修改的数据。
     */
    private function get_edited() {
        $p = array();
        foreach ($this->db_data as $k => $v) {
            if ($k != "sj_geng_xin" && isset($this->data[$k]) && ($this->data[$k] != $v || ($this->data[$k] !== $v && !$v))) {
                $p[$k] = $this->data[$k];
            }
        }
        return $p;
    }

    /**
     * 向数据库表插入一个实例。
     * 不做更新缓存的操作，如果需要更新缓存，请调用save函数
     * @param array data 需要插入的数据。
     * @return TRUE|FALSE
     */
    public function insert_row() {
        $info = $this->info;
        $p = $this->get_edited();
        if(!$p[$info['pk_name']]){
            unset($p[$info['pk_name']]);
        }
        $id = db::i($info['db_name'])->insert_row($info['table_name'], $p);
        if ($id) {
            //更新时间
            unset($this->data['sj_geng_xin']);
            $this->db_data['sj_geng_xin'] = sys::date_time();
            $this->db_data[$info['pk_name']] = $id;
            //向数据库中保存成功，将变化了的值回写到$this->db_data数组
            foreach ($p as $k => $v) {
                $this->db_data[$k] = $v;
                unset($this->data[$k]);
            }
            //$this->del_cache();
        }
        return $id;
    }

    /**
     * 用一个实例更新数据库表的记录。
     * 不做更新缓存的操作，如果需要更新缓存，请调用save函数
     * @return TRUE|FALSE
     */
    public function update_row() {
        $info = $this->info;
        $p = $this->get_edited();
        if (!$p) {
            $this->err_msg("数据没有变化，不用更新数据库.");
            return TRUE;
        }
        $p[$info['pk_name']] = $this->db_data[$info['pk_name']];
        if (db::i($info['db_name'])->update_row($info['table_name'], $p, $info['pk_name'])) {
            unset($this->data['sj_geng_xin']);
            $this->db_data['sj_geng_xin'] = sys::date_time();
            //向数据库中保存成功，将变化了的值回写到$this->db_data数组
            foreach ($p as $k => $v) {
                $this->db_data[$k] = $v;
                unset($this->data[$k]);
            }
            $this->del_cache();
            return TRUE;
        }
        $this->err_msg("更新数据库出错.或者没有更新");
        return FALSE;
    }

    /**
     * 根据一个实例，删除数据库表的一条记录。
     * @return TRUE|FALSE
     */
    public function del($pk=NULL) {
        $info = $this->info;
        if($pk){
            //直接主键删除内容
            $sql = "delete from {$info['table_name']} where {$info['pk_name']}=".db::escape($pk);
            if(db::i($info['db_name'])->query($sql)){
                $this->del_cache($pk);
                return TRUE;
            }
        } elseif (db::i($info['db_name'])->delete_row($info['table_name'], $this->db_data, $info['pk_name'])) {
            $this->del_cache();
            return TRUE;
        }
        $this->err_msg("从数据库删除数据出错.");
        return FALSE;
    }

    /**
     * 根据一个实例，自动判断需要在数据库表中的插入还是更新一条记录。
     * @return TRUE|FALSE
     * 说明:如果是插入数据,return的是insert_row返回的id(modified by wenke 2011.09.21)
     */
    public function save() {
        if ($this->db_data[$this->info['pk_name']]) {
            return $this->update_row();
        }
        return $this->insert_row();
    }

    /**
     * 从数据库表选出所有实例的总数量。
     * @param string    $where   WHERE子句
     * @param array     $values  WHERE子句里的值
     * code:  db::i()->single_query("select * from tab1 where id > :id and con = :co",array("id"=>5,"co"=>"U1"));
     *        "id > :1 and con <= :2"——WHERE子句的例子
     *        array("1"=>5,"2"=>"U1")——WHERE子句里的值
     *
     * @return int 实例的总数量
     */
    public static function get_count($where=NULL, $values=NULL) {
        $info = self::get_class_info();
        $sql = "SELECT COUNT(*) FROM {$info['table_name']}" . ($where ? " WHERE " . $where : "");
        return db::i($info['db_name'])->single_query($sql, $values);
    }

    /**
     * 查询多条数据
     * @param string $where where 条件
     * @param array $values 绑定参数
     * @param bool $iscache 是否使用cache
     * @param bool $single_instance 是否返回单个
     * @return array bean 数组 
     */
    public static function get_list($where=NULL, $values=NULL, $iscache=FALSE, $single_instance=FALSE) {
        $info = self::get_class_info();
        $sc = $info['class_name'];
        $sql = "SELECT * FROM {$info['table_name']}" . ($where ? " WHERE " . $where : "") . ($single_instance ? " LIMIT 1" : "");
        $sql = db::gen_sql($sql, $values);
        
        $ret_ins = array();
        //缓存处理
        $iscache && ($k = sha1($sc . $sql)) && ($rtn = mc::i()->get($k));
        if ($rtn || ($rtn = db::i($info['db_name'])->query($sql))) {
            //缓存处理
            if ($iscache && $rtn) {
                mc::i()->set($k, $rtn);
            }
            foreach ($rtn as $ins) {
                $u = new $sc();
                $u->db_data = $ins;
                $ret_ins[] = $u;
            }
        }
        $rtn = $ret_ins ? ($single_instance ? $ret_ins[0] : $ret_ins) : NULL;
        return $rtn;
    }

    /**
     * 用于翻页的查询方法。和正常查询没有什么区别。只是在返回的结果集中增加了
     * 页码等信息。返回值结构：
     * array(
     *     'page_info'=> array('total'=>'全集记录条数','total_page'=>'共计页数'),
     *     'data'     => array('你查询的数据集返回')
     * )
     */
    public static function get_page($where=NULL, array $params = NULL, $iscache=FALSE, $items_per_page=15) {
        $info = self::get_class_info();
        $sc = $info['class_name'];
        $sql = "SELECT * FROM {$info['table_name']}" . ($where ? " WHERE " . $where : "");
        $sql = db::gen_sql($sql, $params);
        
        //缓存处理
        $current_page = intval($_GET['p']);
        if ($iscache && ($k = sha1($sc . $items_per_page .'|'. $current_page . $sql)) && ($rtn = mc::i()->get($k))) {
            return $rtn;
        }
        $rtn = db::i($info['db_name'])->page_query($sql, null, $items_per_page);
        $ret_ins = array();
        if($rtn){
            foreach ($rtn['data'] as $ins) {
                $u = new $sc();
                $u->db_data = $ins;
                $ret_ins[] = $u;
            }
        }
        
        //$rtn['pageinfo'] = (object)$rtn['pageinfo'];
        $rtn['data'] = $ret_ins;
        //缓存处理
        if ($iscache && $rtn['data']) {
            mc::i()->set($k, $rtn);
        }
        return $rtn;
    }

    /**
     * 从数据库表选出符合条件的实例。
     * @param string $where where 条件
     * @param array $values 绑定参数
     * @param bool $iscache 是否使用缓存
     * @return array bean 数组 
     */
    public static function get_one($where, $values=NULL, $iscache=false) {
        return self::get_list($where, $values, $iscache, TRUE);
    }

    /**
     * 当前数据
     * @return array 
     */
    public function get_data() {
        $this->db_data || $this->db_data = array();
        $this->data    || $this->data = array();
        return $this->data + $this->db_data;
    }

    /**
     * 魔术方法：用于方便地访问类数据字段。
     * 注意：基于张峰与伍鹏的性能测试，使用魔术方法比直接访问数组将多花费3～5倍的时间
     * @param string    $field_name   字段名
     *
     * @return mixed
     */
    public function &__get($field_name) {
        if (array_key_exists($field_name, $this->data)) {
            return $this->data[$field_name];
        } elseif (array_key_exists($field_name, $this->db_data)) {
            $rtn = $this->db_data[$field_name];
            return $rtn;
        } elseif (substr($field_name, 0, 2) === 'r_') {
            $this->data[$field_name] = $this->$field_name();
            return $this->data[$field_name];
        }

        return NULL;
    }

    /**
     * 魔术方法：用于方便地设置类数据字段。
     * 注意：基于张峰与伍鹏的性能测试，使用魔术方法比直接访问数组将多花费3～5倍的时间
     * @param string    $field_name   字段名
     * @param string    $value        值
     *
     * @return void
     */
    public function __set($field_name, $value) {
        $this->data[$field_name] = $value;
    }

    /**
     * 返回数据json编码数据。
     * @return string
     */
    function __toString() {
        return json_encode($this->get_data());
    }
}
