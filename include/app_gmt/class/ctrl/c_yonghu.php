<?php

/**
 * Description of c_yonghu
 *
 * @author zhf
 */
class c_yonghu extends ctrl {
    
    //功能号：1000
    function a_login(){
        $uname  = req::post("uname");
        $passwd = req::post("passwd");
        $logout = req::get("logout");
        $v = new view("yh/login");
        if(
                $uname &&
                $passwd &&
                ($yh = b_yh_xinxi::getYH($uname)) &&
                $yh->checkpasswd($passwd)
        ){
            req::set_cookie(b_yh_xinxi::IDKEY, $yh->yhid);
            return ctrl::goto_controller(array("index", "index"));
        } elseif($uname){
            $v->err = "用户名或密码有误.";
        } elseif($logout){
            req::delete_cookie(b_yh_xinxi::IDKEY);
        }
        
        $this->add_view($v);
    }
    
    //功能号：1012
    function a_changepwd() {
        $yh = $this->__yh;
        $old = req::post('old');
        $new = req::post('new');
        if($new)
        if($yh->checkpasswd($old)){
            $yh->yh_passwd = $yh->mkpasswd($new);
            $yh->save();
            req::delete_cookie(b_yh_xinxi::IDKEY);
            $this->new_view('print',array('val'=>'更改成功。'));
            return;
        } else {
            $this->new_view('print',array('val'=>'<font color=red>原密码错误</font>'));
        }
        $this->new_view('yh/changepwd');
    }
    
}
