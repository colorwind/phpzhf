<?php
defined('SYSPATH') or die('No direct script access.');

require_once SYSPATH . 'class/model/lib/Predis.php';

/**
 * m_cache_redis redis缓存类
 *
 * @author freewind
 */
class m_cache_redis extends mc {

    protected $_connection = NULL;

    public function __construct(array $c) {
        if (count(util::arr($c, 'server')) > 0) {
            $c['exptime'] = isset($c['exptime']) ? (int) $c['exptime'] : NULL;
            $c['persistent'] = isset($c['persistent']) && ($c['persistent'] === TRUE);
            $c['weight'] = isset($c['weight']) ? (int) $c['weight'] : 1;
            $this->_config = $c;
        }
        else {
            throw new Exception('cache config ' . $c['confkey'] . '.redis error');
        }
    }

    protected function connect() {
        try {
            if ($this->_connection) {
                return;
            }
            $c = &$this->_config;
            if (isset($c['server']) && is_array($c['server'])) {
                $this->_connection = new Predis\Client($c['server'][0]);
//                foreach ($c['server'] as $s) {
//                    $this->_connection->addServer($s['host'], $s['port'], $c['persistent'], $c['weight'], $c['exptime']);
//                }
            }
        }
        catch (Exception $e) {
            echo "m_cache_redis connect error";
        }
    }

    protected function _set($key, $value, $exptime = 3600) {
        $this->connect();
        if ($this->_connection) {
            //connect has set default exptime
            $sv = serialize($value);
            if (SYSDEBUG) {
                util::log("缓存键值=" . $key . "\n缓存数据=" . $sv . "\n");
            }
            if ($exptime){
                $succ = $this->_connection->set($key, $sv);
                if ($succ){
                    $this->_connection->expire($key, $exptime);
                    return TRUE;
                }
                else{
                    return FALSE;
                }
            }
            else{
                return $this->_connection->set($key, $sv);
            }
        }
    }

    protected function _get($key, $default = NULL) {
        $this->connect();
        $value = $default;
        if ($this->_connection) {
            $value = unserialize($this->_connection->get($key));
        }
        if ($value === FALSE) {
            $value = $default;
            if (SYSDEBUG) {
                util::log("--------从redis读取{$key}失败---------\n");
            }
        }
        else {
            if (SYSDEBUG) {
                util::log("--------从redis读取成功---------\n");
            }
        }
        return $value;
    }

    protected function _delete($key) {
        $this->connect();
        if ($this->_connection && $key) {
            return $this->_connection->del($key);
        }
    }

    protected function _flush() {
        $this->connect();
        if ($this->_connection) {
            $this->_connection->flushdb();
        }
    }

}

