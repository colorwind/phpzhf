<?php

/**
 * Description of b_sv_servers
 *
 * @author zhf
 */
class b_sv_servers extends bean{
    //put your code here
    const SVIDKEY = "xygmtSVID";


    static function svall() {
        return b_sv_servers::get_list("sv_id>0 order by sv_stat desc,sv_name",NULL,true);
    }
    
    static function getCookidSV_ID(){
        return (int)req::get_cookie(b_sv_servers::SVIDKEY);
    }
}
