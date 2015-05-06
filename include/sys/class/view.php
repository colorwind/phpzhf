<?php

/**
 * 生成view的基础类，关注文件查找规则参见：function set_filename
 *
 * @author zhf
 * 
 * 2011.11.27 19:03:01
 */
class view {

    protected static $_global = array();
    protected $_file;
    protected $_data = array();

    public static function factory($file = NULL, array $data = NULL) {
        return new view($file, $data);
    }

    public function __construct($file = NULL, array $data = NULL) {
        if ($file !== NULL) {
            $this->set_filename($file);
        }

        if ($data !== NULL) {
            $this->_data = $data;
        }
    }

    public static function set_global($key, $value = NULL) {
        if (is_array($key)) {
            foreach ($key as $key2 => $value) {
                self::$_global[$key2] = $value;
            }
        } else {
            self::$_global[$key] = $value;
        }
    }

    public function set_filename($file) {
        $path = sys::find_file("view/{$file}.php");
        if (!$path) {
            throw new Exception("The requested view {$file} could not be found", 404);
        }
        $this->_file = $path;
        return $this;
    }

    public function set($key, $value = NULL) {
        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $this->_data[$name] = $value;
            }
        } else {
            $this->_data[$key] = $value;
        }

        return $this;
    }

    public function render($file = NULL) {
        if ($file !== NULL) {
            $this->set_filename($file);
        }

        if (empty($this->_file)) {
            throw new Exception('You must set the file before render');
        }

        return self::exec($this->_file, $this->_data);
    }

    protected static function exec($view_filename, array $view_data) {
        extract($view_data, EXTR_SKIP);
        if (self::$_global) {
            extract(self::$_global, EXTR_SKIP);
        }
        ob_start();
        try {
            include $view_filename;
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
        return ob_get_clean();
    }

    public function & __get($key) {
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        } elseif (array_key_exists($key, self::$_global)) {
            return self::$_global[$key];
        } else {
            throw new Exception("view key [$key] is not set.");
        }
    }

    public function __set($key, $value) {
        $this->set($key, $value);
    }

    public function __isset($key) {
        return (isset($this->_data[$key]) || isset(self::$_global[$key]));
    }

    public function __unset($key) {
        unset($this->_data[$key], self::$_global[$key]);
    }

    public function __toString() {
        return $this->render();
    }

}
