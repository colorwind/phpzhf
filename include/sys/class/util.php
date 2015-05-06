<?php

/**
 * 框架工具类，常用系统方法实现
 *
 * @author zhf
 */
class util {
    
    /**
     * 获取数组的值。支持多维数组
     * $a = array('kk'=>23,array('aa'=>6));
     * util::arr($a,'kk',-1); //23
     * util::arr($a,'0.aa');  //6
     *
     * @param mixed  $a        数组
     * @param string $k        键名例如：'kk'。如果是多维数组需要使用‘.’进行分割。'0.qy.0.id'
     * @param mixed  $d        默认值。如果对应键值没有设置。将返回 $d;
     * @return  mixed
     */
    public static function arr($a, $k, $d = NULL) {
        if (($k = explode('.', $k))) {
            foreach ($k as $v) {
                if (isset($a) && is_array($a) && isset($a[$v])) {
                    $a = $a[$v];
                } else {
                    return $d;
                }
            }
        } else {
            $a = $d;
        }
        return $a;
    }
    
    /**
     * value default ,获取默认值的简单方法
     * @param mixed $v         传入的值
     * @param mixed $d         默认值，如果$v为假，将返回该值
     * @param bool $isNull      判断条件是否只是$isNull。默认false。
     * @return mixed           如果$v为假，将返回$d
     */
    public static function vd(&$v, $d = null, $isNull = false) {
        if ($isNull === true) {
            $_ = $v !== null;
        } else {
            $_ = $v;
        }
        if ($_) {
            return $v;
        } else {
            return $d;
        }
    }
    
    
    /**
     * 计算概率，返回概率的 key 一个小功能
     * @param array $param 需要计算概率的一个数组。key=>gai_lv值 例如：array(100,50,'a'=>100,'b'=>300);
     * @param string $key 如果数组是一个二维数组，此key是该字段名称。例如： array('a'=>array('name'=>'xxx','gv'=>10),'b'=>array('name'=>'xx','gv'=>100));需要使用gv字段为key。
     * @param bool   $isreturnvalue  是否返回数据的值。默认否。
     * @return mixed 返回对应概率的key值。
     */
    public static function array_random(array $param, $key = null, $isreturnvalue = false) {
        $r = 0;
        foreach ($param as $k => $v) {
            $r += ( $key === null ? $v : $v[$key]);
        }
        if ($r) {
            $r = mt_rand(1, $r);
            $m = 0;
            foreach ($param as $k => $v) {
                $_v = ($key === null ? $v : $v[$key]);
                if ($r <= ($m+=$_v)) {
                    return $isreturnvalue === true ? $v : $k;
                }
            }
        }
        return null;
    }
    
    /**
     * 生成查询字符串
     * 
     * util::gen_querystring(array("a"=>1,"b"=>2),true,"&amp;") 
     * 返回：
     * 
     * 
     * @param type $params
     * @param type $urlencode
     * @param type $glue
     * @return type
     */
    public static function gen_querystring($params,$urlencode=true,$glue="&"){
        $pastr = array();
        if($params && is_array($params)){
            foreach($params as $k=>$v){
                if($urlencode){
                    $v = urlencode($v);
                }
                $pastr[] = "$k=$v";
            }
        }
        return implode($glue, $pastr);
    }
    
    
    /**
     * 使用GET方式获取远处URL内容
     * @param string $url  请求地址
     * @param array $data  请求数据
     * @param int $timeout 超时（秒）
     * @param array $header http 头数据
     * @return string  内容
     */
    public static function http_get($url,array $data=null,$timeout=null,array $header=null) {
        return self::http_request('get', $url, $data, $timeout, $header);
    }
    
    /**
     * 使用POST方式获取远处URL内容
     * @param string $url  请求地址
     * @param array $data  请求数据
     * @param int $timeout 超时（秒）
     * @param array $header http 头数据
     * @return string  内容
     */
    public static function http_post($url,array $data=null,$timeout=null,array $header=null) {
        return self::http_request('post', $url, $data, $timeout, $header);
    }
    

    private static function http_request($method,$url,array $data=null,$timeout=null,array $header=null) {
        $ch = curl_init();
        $options = array(CURLOPT_RETURNTRANSFER => true);
        strtolower($method) == 'post' && ($options[CURLOPT_POST] = true);
        ($timeout = intval($timeout)) > 0 && ($options[CURLOPT_TIMEOUT] = $timeout);
        
        if ($data) {
            if (is_array($data)) {
                $data = self::gen_querystring($data);
            }
            if($options[CURLOPT_POST]){
                $options[CURLOPT_POSTFIELDS] = $data;
            } else {
                if(strpos($url, '?')){
                    $url .= "&{$data}";
                } else {
                    $url .= "?{$data}";
                }
            }
        }
        
        if ($header) {
            $h = array();
            foreach ($header as $key => $value) {
                if (is_numeric($key)) {
                    $h[] = $value;
                } else {
                    $h[] = "{$key}:{$value}";
                }
            }
            $options[CURLOPT_HTTPHEADER] = $h;
        }
        
        if (stripos($url, 'https') === 0) {
            $options[CURLOPT_SSL_VERIFYHOST] = 1;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }
        
        $options[CURLOPT_URL] = $url;

        curl_setopt_array($ch, $options);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
    
    /**
     * 全局md5加密规则
     * @param string $str
     * @return string
     */
    public static function md5($str) {
        return md5($str . '||saltKey||');
    }
    
    public static function salt($name, $value) {
        $agent = self::arr($_SERVER,'HTTP_USER_AGENT','unknow');
        return self::md5($agent . $name . $value);
    }
}
