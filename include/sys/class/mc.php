<?php

/**
 * Description of mc
 *
 * @author zhf
 */
abstract class mc {
    
    //公用缓存定义区 开始
    //bean 缓存
    public static $B_CLASS_INFO;  //实例对象信息数组
    public static $B_CLASS_LIST;  //原始实例对象数组
    
    //公用缓存定义区 结束

    protected static $default = 'default';
    protected static $instances = array();
    protected $_config;

    public static function i($confkey = NULL) {
        if (!$confkey) {
            $confkey = mc::$default;
        }
        if (!isset(mc::$instances[$confkey])) {
            $c = sys::config('cfg_cache.' . $confkey);
            $type = 'none';
            if (util::arr($c, 'enabled', true)) {
                $type = util::arr($c, 'type','mem');
                $c = $c[$type];
                $c['confkey'] = $confkey;
            }
            $type = 'm_cache_'.$type; //具体实现类
            mc::$instances[$confkey] = new $type($c);
        }
        return mc::$instances[$confkey];
        
        //Deceive ide
        return new m_cache_none();
    }
    
    /**
     * 为了避免同一框架的key冲突
     */
    protected function makeKey($key){
        $prefix = util::arr($this->_config, 'prefix','');
        if(is_array($key)){
            foreach($key as &$k){
                $k = $prefix.'.'.$k;
            }
            return $key;
        }
        return $prefix.$key;
    }


    public function set($key, $value, $exptime = NULL){
        $key = $this->makeKey($key);
        return $this->_set($key, $value, $exptime);
    }

    public function get($key, $default = NULL){
        $key = $this->makeKey($key);
        return $this->_get($key, $default);
    }

    public function delete($key){
        $key = $this->makeKey($key);
        return $this->_delete($key);
    }

    public function flush(){
        return $this->_flush();
    }



    abstract protected function _set($key, $value, $exptime = NULL);

    abstract protected function _get($key, $default = NULL);

    abstract protected function _delete($key);

    abstract protected function _flush();
}
