<?php

/**
 * controller base class
 * 
 * 本类为controller类的基础类，不支持overwrite
 *
 * @author zhf
 */
abstract class ctrl_base {
    
    protected $_views_ = array();
    protected static $info = null;
    
    protected $st = 0; //输出状态
    protected $msg = ''; //输出信息
    protected $dat = array(); //输出数据

    /////////////  接口方法  开始  /////////////
    
    /**
     * 返回ctrl_info实例接口，需要在应用的ctrl中实现你的业务逻辑。
     * 
     * @return ctrl_info
     */
    public static function get_info(){
        if(self::$info==NULL){
            $cname = req::request('c', 'index');
            $aname = req::request('a', 'index');
            self::$info= new ctrl_info($cname,$aname);
        }
        return self::$info;
    }
    
    /**
     * 需要在应用中实现此方法。此方法负责生成url。
     * 
     * @param mixed $ctrl         功能号，或者controller名action明的数组。按需要定义
     * @param array $params       参数数组
     * @return string             URL地址
     */
    public static function gen_url($ctrl, $params = array(),$urlencode=true){
        $a = FALSE;
        if ($ctrl->class_name && $ctrl->action_name) {
            $params['c'] = $ctrl->class_name;
            $params['a'] = $ctrl->action_name;
            $params = util::gen_querystring($params, $urlencode);
            $a = SYSMAIN . '?' . $params;
        }
        return $a;
    }


    /**
     * 前置函数 如果需要使用，需要在项目中overwrite
     */
    public function before() {}
    
    /**
     * 后置置函数 如果需要使用，需要在项目中overwrite
     */
    public function after() {}
    
    /////////////  接口方法  结束  /////////////
    
    
    /**
     * 跳转到指定的url地址
     * 
     * @param string $url
     */
    public final static function goto_url($url) {
        header('Location: ' . $url);
    }

    /**
     * 跳转到指定的controller
     * 
     * @param mixed $ctrl
     * @param array $param
     */
    public final static function goto_controller($ctrl, array $param = array()) {
        self::goto_url(ctrl::gen_url($ctrl, $param));
    }
    
    /**
     * 在当前ctrl中新添加view对象
     * 
     * @param string $file
     * @param array $data
     * @return view
     */
    protected final function new_view($file,array $data=array()){
        $v = view::factory($file,$data);
        $this->add_view($v);
        return $v;
    }

    /**
     * 把view添加到当前ctrl中。
     * 
     * @param view $v
     * @return boolean
     */
    public final function add_view(view &$v) {
        if (!in_array($v, $this->_views_)) {
            $this->_views_[] = &$v;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 清理当前ctrl中的所有view。
     */
    public final function clear_view(){
        $this->_views_ = array();
    }


    /**
     * 获取当前ctrl中的所有view实例。
     * 
     * @return array
     */
    public final function get_views() {
        return $this->_views_;
    }
    
    
    /**
     * 标准接口json输出。
     */
    protected function json_out() {
        $out = array(
            'st'  => (int)$this->st,
            'msg' => $this->msg,
            'dat' => $this->dat
        );
        $this->clear_view();
        $this->new_view('json',array('val'=>$out));
    }
    
}
