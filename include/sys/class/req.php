<?php

/**
 * request类
 *
 * @author zhf
 */
class req {

    private static $initial;
    private static $client_ip;
    public $ctrl = NULL;            //当前ctrl对象

    private function __construct() {
        
    }

    /**
     * 获取$_GET[$k] 的值。
     * @param string $k 键值
     * @param mixed $def 默认值。
     * @return mixed  获取$_GET[$k] 的值 如果为false，将返回 默认值
     */
    public static function get($k, $def = NULL) {
        return util::arr($_GET, $k, $def);
    }

    /**
     * 获取$_POST[$k] 的值。
     * @param string $k 键值
     * @param mixed $def 默认值。
     * @return mixed  获取 $_POST[$k] 的值 如果为false，将返回 默认值
     */
    public static function post($k, $def = NULL) {
        return util::arr($_POST, $k, $def);
    }
    
    /**
     * 获取 $_REQUEST[$k] 的值。
     * @param string $k 键值
     * @param mixed $def 默认值。
     * @return mixed  获取 $_REQUEST[$k] 的值 如果为false，将返回 默认值
     */
    public static function request($k, $def = NULL) {
        return util::arr($_REQUEST, $k, $def);
    }

    /**
     * 设置Session值
     * @param string $name
     * @param mixed $value
     */
    public static function set_session($name, $value) {
        if ($name) {
            $_SESSION[$name] = $value;
        }
    }

    /**
     * 获取session 值
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get_session($name, $default = NULL) {
        if (!isset($_SESSION[$name])) {
            return $default;
        }
        $default = $_SESSION[$name];
        return $default;
    }

    /**
     * 获取cookie的值
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function get_cookie($key, $default = NULL) {
        if (!isset($_COOKIE[$key])) {
            return $default;
        }
        $cookie = $_COOKIE[$key];
        $split = strlen(util::salt($key, NULL));
        if (isset($cookie[$split]) && $cookie[$split] === '~') {
            list ($hash, $value) = explode('~', $cookie, 2);
            if (util::salt($key, $value) === $hash) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * 设置cookie的值
     * @param string $name
     * @param string $value
     * @param int $expiration 过期时间（秒）
     * @return boolean  
     */
    public static function set_cookie($name, $value, $expiration = NULL) {
        if ($expiration === NULL) {
            $expiration = 86400;
        } else {
            $expiration = (int) $expiration;
        }

        if ($expiration !== 0) {
            $expiration += time();
        }

        $value = util::salt($name, $value) . '~' . $value;

        return setcookie($name, $value, $expiration, '/');
    }

    /**
     * 删除cookie 
     * @param string $name
     * @return boolean
     */
    public static function delete_cookie($name) {
        unset($_COOKIE[$name]);
        return self::set_cookie($name, NULL, -86400);
    }

    /**
     * 获取请求ip
     * @return string
     */
    public static function clinet_ip() {
        if (!self::$client_ip) {
            $ks = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
            foreach ($ks as $k) {
                self::$client_ip = util::arr($_SERVER, $k);
                $matches = array();
                preg_match('/\d{0,3}\.\d{0,3}\.\d{0,3}\.\d{0,3}/', self::$client_ip, $matches);
                if ($matches) {
                    self::$client_ip = $matches[0];
                    break;
                }
            }
        }
        return self::$client_ip;
    }

    /**
     * 获取domain host
     * @return string
     */
    public static function domain_host() {
        return util::vd($_SERVER['HTTP_HOST'], $_SERVER['REMOTE_HOST']);
    }

    /**
     * 判断是非是ajax请求
     * @return boolean
     */
    public static function is_ajax() {
        return sys::is_ajax();
    }
    
    
    
    
    public function execute() {
        $cinfo = ctrl::get_info();
        if(!$cinfo){
            throw new Exception('[405]The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found on this server.', 402);
        }
        return $this->run($cinfo);
    }

    private function run(ctrl_info $cinfo) {
        try {

            $ctrl = $cinfo->class_name;
            $action = $cinfo->action_name;
            if (!class_exists($ctrl)) {
                throw new Exception('[403]The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found on this server.', 403);
            }

            // Load the controller
            $class = new ReflectionClass($ctrl);

            if ($class->isAbstract()) {
                throw new Exception('Cannot create instances of abstract ' . $ctrl);
            }

            // Create a new instance of the controller
            $ctrl = $class->newInstance();

            if (!($ctrl instanceof ctrl_base)) {
                throw new Exception('[405]The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found on this server.', 405);
            }

            $this->ctrl = $ctrl;

            if (!$class->hasMethod($action)) {
                throw new Exception('[404]The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found on this server.', 404);
            }

            //执行前置方法 
            $ctrl->before();
            //执行action方法
            $ctrl->$action();
            //执行后置方法
            $ctrl->after();

            //流程都执行完成了，最后输出结果输出 views 一般情况，这里才是真正输出的位置
            $t_views = $ctrl->get_views();
            for ($i = 0; $i < count($t_views); $i++) {
                echo $t_views[$i]->render();
            }
            flush();

            log::ilog(); //ilog 记录
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 返回request对象
     * @return req
     */
    public static function i() {
        if (!req::$initial) {
            req::$initial = new req();
        }
        return req::$initial;
    }

}
