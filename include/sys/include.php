<?php

/**
 * @author zhf
 * 
 * 2011.11.25 09:36:21
 */


define('SYSPATH', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR );

//配置是否debug （可覆盖）
if(!defined('SYSDEBUG')){
    $cfg_sysdebug = SYSPATH . 'config/cfg_sysdebug.php';
    if(file_exists($cfg_sysdebug)){
        require($cfg_sysdebug);
    }
    if(!defined('SYSDEBUG')){
        define('SYSDEBUG', FALSE);
    }
}

//设置框架入口文件（可覆盖）
if (!defined('SYSMAIN')) {
    define('SYSMAIN', 'i.php');
}

//配置app目录规则是以app开头 例如app_zq2 （可覆盖）
if (!defined('APP_DIR_NAME')) {
    define('APP_DIR_NAME', 'app');
}

//配置app上传,和log目录,如果不配置目录默认规则，替换开头的app为upload，例如app_zq2替换为upload_zq2（可覆盖）
if (!defined('UPLOADPATH')) {
    define('UPLOADPATH', 'upload'.substr(APP_DIR_NAME, 3));
}

/**
 * 系统参数配置完成，开始执行
 */

//系统执行时的参数处理
if (!$_GET && !$_POST && isset($argv) && $argv[1]) {
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
    parse_str($argv[1], $_GET);
}

session_start();

if (SYSDEBUG) {
    version_compare(PHP_VERSION, '5.3.0', '>=') || exit("Requires PHP 5.3.0 or newer, this version is " . PHP_VERSION);
    error_reporting(E_ALL ^ E_NOTICE);      //developing
} else {
    error_reporting(E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE);  //production
}

//设置默认系统字符集
define('SYSCHARSET', 'UTF-8');
//记录系统内存占用
define('SYS_START_MEMORY', memory_get_usage());
//记录开始时间
define('SYS_START_TIME', microtime(TRUE));
//设置系统默认时区
date_default_timezone_set('Asia/Shanghai');
//设置返回头文件字符集
header('Content-type: text/html; charset=' . SYSCHARSET);


//生产系统相关参数
define('INCPATH', realpath(SYSPATH . '..')  . DIRECTORY_SEPARATOR);
define('APPPATH', INCPATH . APP_DIR_NAME . DIRECTORY_SEPARATOR);


//加载框架基础类
require SYSPATH . 'class/sys.php';

//注册类加载函数
spl_autoload_register(array('sys', 'auto_load'));
ini_set('unserialize_callback_func', 'spl_autoload_call');

//绑定系统错误处理函数
set_exception_handler(array('sys', 'exception_handler'));
set_error_handler(array('sys', 'error_handler'));

//加载预定义类
require SYSPATH . 'class/util.php';
require SYSPATH . 'class/log.php';
require SYSPATH . 'class/mc.php';
require SYSPATH . 'class/req.php';
require SYSPATH . 'class/ctrl_base.php';


//开始执行
req::i()->execute();