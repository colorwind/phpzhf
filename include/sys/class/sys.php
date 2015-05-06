<?php

/**
 * 编写代码时请注意：本类不支持overwrite。
 *
 * @author zhf
 */
class sys {

    static $config;

    public static function find_file($path) {
        $p = array(APPPATH, SYSPATH);
        foreach ($p as $v) {
            if (is_file(($filepath = $v . $path))) {
                return $filepath;
            }
        }
        return NULL;
    }

    public static function config($group) {
        if (strpos($group, '.') !== FALSE) {
            list ($group, $path) = explode('.', $group, 2);
        }
        if (!isset(self::$config[$group])) {
            $file = self::find_file("config/{$group}.php");
            self::$config[$group] = self::load($file);
        }
        if (isset($path)) {
            $path = explode('.', $path);
            $v = self::$config[$group];
            for ($i = 0; $i < count($path); $i++) {
                if (isset($v[$path[$i]])) {
                    $v = $v[$path[$i]];
                } else {
                    $v = NULL;
                    break;
                }
            }
            return $v;
        } else {
            return self::$config[$group];
        }
    }

    public static function exception_handler(Exception $e) {
        
        $time = "Time:".self::date_time().";";
        $err = $time . $e->getMessage()."\r\n" .$e->getTraceAsString();
        if(SYSDEBUG){
            $msg = $err;
        } else {
            $msg = sprintf('Err[%s];%s,%s',$e->getCode(), self::date_time(),$e->getMessage());
        }
        if(self::is_ajax()){
            echo json_encode(array('st'=>0,'msg'=>$msg));
        } else {
            echo "<pre>".$msg;
        }
        log::ilog($err);
        
    }

    public static function error_handler($code, $error, $file = NULL, $line = NULL) {
        if (error_reporting() & $code) {
            self::exception_handler(new ErrorException($error, $code, 0, $file, $line));
            exit;
        }
        return TRUE;
    }

    public static function load($file) {
        return include $file;
    }

    /**
     * 
     * 系统自动加载类方法
     * @param boolean $class
     */
    public static function auto_load($class) {
        $class = strtolower($class);
        if (($p = strstr($class, '_', TRUE))) {
            $inc = array('b' => 'bean/', 'c' => 'ctrl/', 'm' => 'model/');
            if (isset($inc[$p])) {
                $class = $inc[$p] . $class;
            }
        }
        $path = self::find_file("class/{$class}.php");
        if (is_file($path)) {
            require $path;
            return TRUE;
        }
        return FALSE;
    }
    
    public static function is_ajax(){
		return (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
	}
    
    /**
     * 时间格式为：date("Y-m-d H:i:s") 例：2012-12-31 09:01:01
     * @param int $time 偏移的时间（秒）
     * @return string 计算后的时间字符串  
     */
    public static function date_time($time = 0,$format="Y-m-d H:i:s") {
        if (!is_numeric($time)) {
            $time = 0;
        }
        return date($format, time() + intval($time));
    }
    
}
