<?php

/**
 * ctrl 代码 demo。请根据自己的需求 overwrite。
 *
 * @author zhf
 */
class ctrl extends ctrl_base {
    

    /**
     * 实现cbase的接口方法
     * @return ctrl_info
     */
    public static function get_info() {
        if (self::$info == NULL) {
            $cname = req::request('c', 'index');
            $aname = req::request('a', 'index');
            self::$info = new ctrl_info($cname, $aname);
        }
        return self::$info;
    }

    /**
     * 通过ctrl_info和参数来生成请求的url地址。
     * @param ctrl_info $ctrl
     * @param array $params
     * @return string
     */
    public static function gen_url($ctrl, $params = array(), $urlencode = true) {
        $a = FALSE;
        if ($ctrl->class_name && $ctrl->action_name) {
            $params['c'] = $ctrl->class_name;
            $params['a'] = $ctrl->action_name;
            $params = util::gen_querystring($params, $urlencode);
            $a = SYSMAIN . '?' . $params;
        }
        return $a;
    }


}
