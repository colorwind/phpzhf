<?php
defined('SYSPATH') or die('No direct script access.');

/**
 * m_cache_apc apc缓存类
 * 2011.08.12 21:19:00
 *
 * @author zhangfeng
 */
class m_cache_apc  extends mc{
    
    protected $_config = NULL;
    
    /**
     *
     *
     * 'apc' => array(
     *             'exptime' => 900,         //默认超时时间。ttl 秒
     *             'prefix' => 'gamecenter', //key前缀
     *         ),
     */
    public function __construct(array $c) {
        $c['exptime'] = isset($c['exptime']) ? (int) $c['exptime'] : 900;
        $this->_config = $c;
    }
    
    protected function _set($key, $var, $ttl = NULL){
        $ttl === NULL || ($ttl = (int)$this->_config['exptime']);
        return apc_store($key, $var, $ttl);
    }
    
    protected function _get($key, $default = NULL){
        $succ = False;
        $var = apc_fetch($key, $succ);
        return $succ ? $var : $default;
    }

    protected function _delete($key){
        return apc_delete($key);
    }
    
    
    protected function _flush(){
        return apc_clear_cache("user");
    }
}