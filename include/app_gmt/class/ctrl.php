<?php

/**
 * ctrl 代码 demo。
 *
 * @author zhf
 */
class ctrl extends ctrl_base {
    
    public static $GongNeng = null;
    
    protected $__yhid = 0; //通过登录功能设置角色ID
    protected $__yh = null;  // 当前用户对象
    

    /**
     * 实现cbase的接口方法
     * 创建 ctrl_info 实例对象
     * @return ctrl_info
     */
    public static function get_info() {
        $a = (int)req::request('a');
        $cinfo = null;
        if(self::$GongNeng || (self::$GongNeng = b_xt_dm_gongneng::i($a))){
            $cname = self::$GongNeng->gn_ctrl;
            $aname = self::$GongNeng->gn_act;
            $cinfo = new ctrl_info($cname,$aname,self::$GongNeng);
        }
        
        return $cinfo;
    }
    
    /**
     * 实现cbase的接口方法
     * 通过ctrl_info和参数来生成请求的url地址。
     * @param ctrl_info $ctrl
     * @param array $params
     * @return string
     */
    public static function gen_url($ctrl, $params = null,$urlencoed=true) {
        if(is_numeric($ctrl)){
            $params['a'] = $ctrl;
        } else if($ctrl instanceof ctrl_info) {
            $vals = array('v1'=>$ctrl->class_name,'v2'=>$ctrl->action_name);
            $bgn = b_xt_dm_gongneng::get_one("gn_ctrl=:v1 and gn_act=:v2",$vals,true);
            $params['a'] = $bgn->gn_dmid;
        } else if(is_array($ctrl)){
            $vals = array('v1'=>$ctrl[0],'v2'=>$ctrl[1]);
            $bgn = b_xt_dm_gongneng::get_one("gn_ctrl=:v1 and gn_act=:v2",$vals,true);
            $params['a'] = $bgn->gn_dmid;
        }
        $params = util::gen_querystring($params,$urlencoed);
        $a = FALSE;
        if ($ctrl) {
            $a = SYSMAIN . '?' . $params;
        }
        return $a;
    }
    
    
    /**
     * 构造方法，做验证相关逻辑实现
     */
    public final function __construct() {
        $gn = self::$GongNeng;
        
        //判断后台程序
        if ($gn->ip_pid) {
            // 后台功能
            $ip = req::clinet_ip();
            $sql = 'select * from ip_dm_xinren where ip_pid=:v1 and ip_dizhi=:v2';
            if ($ip != '127.0.0.1' && !db::i()->query($sql, array('v1' => $gn->ip_pid, 'v2' => $ip))) {
                throw new Exception("You don't have permission to access {$_SERVER['REQUEST_URI']} on this server.", 403);
            }
        }
        
        //登录验证
        if($gn->yz_leixing==1){
            $this->__yhid = req::get_cookie(b_yh_xinxi::IDKEY);
            if(!$this->__yhid || !($this->__yh=b_yh_xinxi::i($this->__yhid)) || $this->__yh->yh_type < 1){
                ctrl::goto_controller(array("yonghu", "login"));
                throw new Exception("Longin please!",403);
            }
            
        }
    }
    
    
    

}
