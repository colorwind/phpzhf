<?php

/**
 * Description of b_sv_servers
 *
 * @author zhf
 */
class b_dj_wupin extends bean{
    
    static $ADV = array(
        '白','绿','蓝','紫','橙'
    );


    static function djall() {
        return b_dj_wupin::get_list("dj_id>0 order by dj_adv,dj_name",NULL,true);
    }
    
}
