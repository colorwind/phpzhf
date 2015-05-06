<?php


/**
 * gdb 是根据框架db继承后实现的另一种数据库配置链接方式。
 * 此例子展示类似问题的解决方案。和框架的扩展思路
 *
 * @author zhf
 */
class gdb extends db {
    
    protected static $instances = array();
    
    /**
     * 数据库单例方法，overwrite db 的单例方法
     * @param int $gsid 游戏数据配置表中的ID
     * @return gdb  
     */
    public static function i($gsid=NULL) {
        if (!$gsid) {
            $gsid = b_sv_servers::getCookidSV_ID();
        }
        if (!isset(self::$instances[$gsid])) {
            self::$instances[$gsid] = new self($gsid);
        }
        if(true){
            return self::$instances[$gsid];
        }
        //Deceive ide
        return new self($gsid);
    }

    /**
     * gdb构造方法
     * @param int $gsid  游戏数据配置表中的ID
     * @throws Exception
     */
    protected function __construct($gsid) {
        if($gsid && ($sv = b_sv_servers::i($gsid)) && $sv->sv_id>0){
            $this->_config_key = $gsid;
            $this->_config = array(
                'hostname'   => $sv->sv_dbhost,
                'database'   => $sv->sv_dbname,
                'username'   => $sv->sv_dbuser,
                'password'   => $sv->sv_dbpwd,
                'persistent' => FALSE,
                'charset'    => 'utf8',
            );
        } else {
            throw new Exception('Load game server sv_id [' . $gsid . '] error');
        }
    }
    
}
