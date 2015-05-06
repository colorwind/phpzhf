<?php

/**
 * memcache 实现类 m_cache_mem
 *
 * @author zhf
 */
class m_cache_mem extends mc {

    protected $_connection = NULL;

    
    /**
     *
     *
     * @param array $confkey
     *   $conf = array(
     *             #'persistent' => TRUE,
     *             'weight' => 1,
     *             'exptime' => 900,
     *             'server' => array(
     *                 array('host' => '127.0.0.1', 'port' => 11211),
     *           )
     *      );
     */
    public function __construct(array $c) {
        if (count(util::arr($c, 'server')) > 0) {
            $c['exptime'] = isset($c['exptime']) ? (int) $c['exptime'] : NULL;
            $c['persistent'] = isset($c['persistent']) && ($c['persistent'] === TRUE);
            $c['weight'] = isset($c['weight']) ? (int) $c['weight'] : 1;
            $this->_config = $c;
        } else {
            throw new Exception('cache config ' . $c['confkey'] . '.mem error');
        }
    }


    protected function connect() {
        try {
            $c = &$this->_config;
            if ($this->_connection) {
                return;
            }
            if (isset($c['server']) && is_array($c['server'])) {
                $this->_connection = new Memcache;
                foreach ($c['server'] as $s) {
                    $this->_connection->addServer($s['host'], $s['port'], $c['persistent'], $c['weight']);
                }
            }
        } catch (Exception $e) {
            echo "m_cache_mem connect err";
        }
    }


    protected function _set($key, $value, $ttl = NULL) {
        $this->connect();
        if ($this->_connection) {
            //connect has set default exptime
            $ttl === NULL || ($ttl = (int)$this->_config['exptime']);
            return $this->_connection->set($key, $value, 0, $ttl);
        }
    }


    protected function _get($key, $default = NULL) {
        $this->connect();
        $value = $default;
        if($this->_connection){
            $value = $this->_connection->get($key);
        }
        if ($value === FALSE) {
            $value = $default;
        }
        return $value;
    }


    protected function _delete($key) {
        $this->connect();
        if ($this->_connection && $key) {
            return $this->_connection->delete($key);
        }
    }



    protected function _flush() {
        $this->connect();
        if ($this->_connection) {
            $this->_connection->flush();
        }
    }

}