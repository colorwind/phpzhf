<?php

/**
 * Description of b_yh_xinxi
 *
 * @author zhf
 */
class b_yh_xinxi extends bean {

    const IDKEY = "xygmtyhid";

    static function getYH($uname) {
        return b_yh_xinxi::get_one("yh_name=:v1", array('v1' => $uname), true);
    }

    function checkpasswd($passwd) {
        return $this->yh_passwd == $this->mkpasswd($passwd);
    }

    function mkpasswd($str) {
        return md5($str);
    }
    
    function r_yhtype() {
        return util::arr(array('无效','用户','管理员'), (int)$this->yh_type);
    }

}
