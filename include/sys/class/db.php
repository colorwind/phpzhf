<?php

/**
 * mysql数据库操作类
 *
 * @author zhf
 */
class db {

    protected static $instances = array();
    protected static $default = 'default';
    public $last_query = NULL;
    public $sql_array = array();
    public $affected_rows = 0;
    protected $_connection = NULL;
    protected $_config_key = NULL;
    protected $_config = NULL;

    final static function get_instances_list(){
    	return self::$instances;
    }

    /**
     * 数据库单例方法
     * @param type $confkey
     * @return db  
     */
    public static function i($confkey = NULL) {
        if (!$confkey) {
            $confkey = self::$default;
        }
        if (!isset(self::$instances[$confkey])) {
            self::$instances[$confkey] = new self($confkey);
        }
        return self::$instances[$confkey];

        //Deceive ide
        return new self($confkey);
    }

    protected function __construct($confkey) {
        $conf = sys::config('cfg_db.' . $confkey);
        if (is_array($conf) && count($conf) > 0) {
            $this->_config_key = $confkey;
            $this->_config = $conf;
        } else {
            throw new Exception('DB Config ' . $confkey . ' error');
        }
    }

    final public function __destruct() {
        $this->close();
    }

    public function close() {
        try {
            $status = TRUE;
            if (is_resource($this->_connection)) {
                if (($status = $this->_connection->close())) {
                    $this->_connection = NULL;
                }
            }
        } catch (Exception $e) {
            $status = !is_resource($this->_connection);
        }
        return $status;
    }

    protected function connect() {
        if ($this->_connection) {
            return;
        }
        $c = &$this->_config;
        $hostname = isset($c['hostname']) ? $c['hostname'] : '';
        $database = isset($c['database']) ? $c['database'] : '';
        $username = isset($c['username']) ? $c['username'] : '';
        $password = isset($c['password']) ? $c['password'] : '';
        $charset = isset($c['charset']) ? $c['charset'] : '';
        
        $hostname = explode(':', $hostname);
        ($port = intval($hostname[1])) || ($port="3306");
        ($hostname = $hostname[0]) || ($hostname = 'localhost');
        $this->_connection = new mysqli($hostname, $username, $password, $database, $port);
        
        if($this->_connection->connect_error){
            throw new Exception('Connection error :[' . $this->_connection->connect_errno . '] ' . $this->_connection->connect_error);
        }
        
        if ($charset) {
            $this->set_charset($charset);
        }
    }

    public function set_charset($charset) {
        $status = (bool) $this->execute('set names ' . $charset);
        if ($status === FALSE) {
            throw new Exception('set charset error [' . $this->_connection->errno . '] ' . $this->_connection->error);
        }
    }

    /**
     * 通过数组像数据表中插入一条记录
     *
     * @param string $table_name 表名
     * @param array  $parms      字段数据
     * @param bool  $replace     如果主键冲突是否替换。默认不替换（false）
     * @return int  操作结果
     */
    public function insert_row($table_name, array $parms, $replace=false) {
        $columns = $values = array();
        foreach ($parms as $keys => $value) {
            $columns[] = $keys;
            $values [] = self::escape($value);
        }

        if (!$table_name || !$columns || !$values) {
            return FALSE;
        }

        $sql = ($replace? 'replace' : 'insert') . ' into ' . $table_name . ' ( ' . implode(' , ', $columns) . ' ) values ( ' . implode(' , ', $values) . ')';
        return $this->query($sql);
    }

    /**
     * 数据表删除一条记录
     *
     * @param string $tn    表名
     * @param array  $d     实例的数据
     * @param string $pkn   主键名
     * @return int
     */
    function delete_row($tn, array $d, $pkn) {
        if (!$tn || !$pkn || !$d) {
            return FALSE;
        }
        $sql = "DELETE FROM {$tn} WHERE {$pkn}=".self::escape($d[$pkn]);
        return $this->query($sql);
    }

    /**
     * 通过数组，更新数据库中的记录
     *
     * @param string $table_name
     * @param array $parms
     * @param mixed $key_column
     * @return int
     */
    public function update_row($table_name, array $parms, $key_column) {
        $pairs = $where_clause = array();
        if (is_array($key_column)) {
            foreach ($key_column as $key_col) {
                $where_clause[] = $key_col . ' = ' . self::escape($parms[$key_col]);
                unset($parms[$key_col]);
            }
        } else {
            $where_clause[] = $key_column . ' = ' . self::escape($parms[$key_column]);
            unset($parms[$key_column]);
        }
        if (!$where_clause) {
            return FALSE;
        }

        foreach ($parms as $keys => $value) {
            $pairs[] = $keys . "=" . self::escape($value);
        }

        if (!$pairs) {
            return FALSE;
        }

        $sql = 'update ' . $table_name . ' set ' . implode(" , ", $pairs) . " where " . implode(' and ', $where_clause);
        return $this->query($sql);
    }

    /**
     * 返回数据集第一行
     *
     * @param string $sql
     * @param array $params
     * @param mixed $result_type
     * @return array
     */
    public function once_query($sql, array $params = NULL, $result_type = MYSQL_ASSOC) {
        if (($rs = $this->query($sql . ' limit 1 ', $params, $result_type))) {
            if (is_array($rs) && count($rs) > 0) {
                return $rs[0];
            }
        }
        return FALSE;
    }

    /**
     * 用于翻页的查询方法。和正常查询没有什么区别。只是在返回的结果集中增加了
     * 页码等信息。返回值结构：
     * array(
     *     'page_info'=> array('total'=>'全集记录条数','total_page'=>'共计页数'),
     *     'data'     => array('你查询的数据集返回')
     * )
     *
     * @param string $sql             sql查询
     * @param array  $params          绑定参数
     * @param int    $items_per_page  每页显示数据条数
     * @param int    $current_page    当前页面  设置为NULL 将试图自动获页号
     * @param mixed  $result_type     查询类型 ：MYSQL_ASSOC
     * @return array
     */
    public function page_query($sql, array $params = NULL, $items_per_page=15, $current_page=0,  $result_type = MYSQL_ASSOC) {
        $s = preg_replace("/^\s*select(.*?)from\s/i", "select count(*) from ", $sql);
        if ($s == $sql) {
            throw new Exception('This query is not a page select type!');
        }
        $current_page = $current_page ? (int) $current_page : (isset($_GET['p']) ? (int) $_GET['p'] : 1);
        $current_page = max((int) $current_page, 1);
        $items_per_page = max((int) $items_per_page, 1);
        $total = (int) $this->single_query($s, $params);
        $limit = '';
        $start = 0;
        if ($total > $items_per_page) {
            $total_page = ceil($total / $items_per_page);
            $current_page = min($current_page, $total_page);
            $start = ($current_page - 1) * $items_per_page;
            $limit = " limit {$start},{$items_per_page}";
        } else {
            $total_page = 1;
        }

        $rs['pageinfo'] = array('total' => $total, 'total_page' => $total_page,'current_page'=>$current_page,'start'=>$start,'items_per_page'=>$items_per_page);
        $rs['data'] = $this->query($sql . $limit, $params, $result_type);

        return $rs;
    }
    
    /**
     * 返回单个查询结构，适用于查询单个结果。例如 select count(*) from tbla1
     * 将直接返回一个int值;
     *
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function single_query($sql, array $params = NULL) {
        $rs = NULL;
        if (($rs = $this->once_query($sql, $params))) {
            if (count($rs) > 0) {
                foreach ($rs as $_) {
                    return $_;
                }
            }
        }
        return $rs;
    }

    public function dm_query($sql,array $params = NULL){
        $sql = self::gen_sql($sql,$params);
        $key = sha1($this->_config_key . $sql);
        $rs  = NULL;
        if(!($rs = mc::i()->get($key)) && ($rs = $this->query($sql))){
            mc::i()->set($key, $rs);
        }
        return $rs;
    }

    /**
     * queyr db
     * code:  $db->query("select * from tab1 where id > :id and con = :co",array("id"=>5,"co"=>"U1"));
     *        as same to query "select * from tab1 where id > ".db::escape(5)." and con = ".db::escape("U1")
     * @param string $sql
     * @param array $params
     * @param mixed $result_type
     * @return mixed
     */
    public function query($sql, array $params = NULL, $result_type = MYSQL_ASSOC) {
        $sql = self::gen_sql($sql, $params);

        preg_match("/^\s*(select|insert|update|replace|delete|truncate|desc)\s/i", $sql, $matches);
        if ($matches && ($matches = strtolower($matches[1]))) {
            $rs = $this->execute($sql,$matches);
            switch ($matches) {
                case 'select':
                case 'desc':
                    $data = array();
                    while ($row = $rs->fetch_array($result_type)) {
                        $data[] = $row;
                    }
                    $rs->free();
                    return $data;
                case 'insert':
                    $this->affected_rows = $this->_connection->affected_rows;
                    $_ = $this->_connection->insert_id;
                    $_ = $_ ? $_ : $this->affected_rows > 0 ;
                    return $_;
                default :
                    return ($this->affected_rows = $this->_connection->affected_rows);
            }
        } else {
            throw new Exception('count not execute this query: [' . $sql . '] ');
        }
    }

    protected function execute($sql,$type='') {
        $this->connect();
        if(SYSDEBUG){
            $this->last_query = $sql;
            $this->sql_array[] = $sql;
        }

        if($type){
            $this->sql_array['count']++;
            $this->sql_array[$type]++;
        }

        if (($result = $this->_connection->query($sql))) {
            return $result;
        }
        
        if(!SYSDEBUG){
            $this->sql_array['err_sql'][] = $sql;
        }
        $eno = $this->_connection->errno;
        $ems = $this->_connection->error;
        $msg = 'query error [' . $eno . '] ' . $ems . (SYSDEBUG ? "[{$sql}]" : '' );
        throw new Exception($msg,$eno);
    }
    
    /**
     * 根据参数生成sql语句
     * @param string $sql
     * @param array $params 
     */
    public static function gen_sql($sql,array $params=null){
        if ($params && count($params) > 0) {
            krsort($params);
            foreach ($params as $key => $val) {
                if (($key = trim($key))) {
                    $_val = self::escape($val);
                    if(!$_val && $val && is_array($val)){
                        //in() 参数传入数组
                        foreach($val as &$vv){
                            $vv = self::escape($vv);
                        }
                        $_val = implode(',', $val);
                    }
                    $sql = str_replace(':' . $key, $_val, $sql);
                }
            }
        }
        return $sql;
    }

    public static function escape($value) {

        if (is_array($value)) {
            return $value['function'];
        } elseif ($value === NULL) {
            return 'NULL';
        } elseif ($value === TRUE) {
            return "'1'";
        } elseif ($value === FALSE) {
            return "'0'";
        } elseif (is_int($value)) {
            return (int)$value;
        } else {
            return "'" . str_replace(array('\\', "'"), array('\\\\', "\\'"), $value) . "'";
        }

//        $this->connect();
//        if (($value = mysqli_real_escape_string((string) $value, $this->_connection)) === FALSE) {
//            throw new Exception('[' . mysqli_errno($this->_connection) . '] ' . mysqli_error($this->_connection));
//        }
//        return "'$value'";
    }

}
