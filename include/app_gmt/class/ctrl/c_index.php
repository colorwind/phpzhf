<?php

/**
 * Description of c_index
 *
 * @author zhf
 */
class c_index extends ctrl {
    
    //功能号：1001
    function a_index(){
        $this->new_view("idx/index");
    }
    
    //功能号：1002
    function a_left() {
        $v = $this->new_view("idx/left");
        $v->yh = $this->__yh;
        $v->dat = b_cd_caidan::get_list("cd_stat>0 order by cd_tag,cd_name",NULL,true);
    }
    
    //功能号：1003
    function a_right(){
        $v = $this->new_view("idx/right");
        $v->title = "首页";
    }
    
    //功能号；1004
    function a_users() {
        $v = $this->new_view("idx/right");
        $v->title = "A1.用户相关 > 用户信息";
        $svb = $this->getsvrinfo();
        if(!$svb->sv_id){
            $v->body = "先选择要操作的服务器！";
            return;
        }
        
        //处理操作参数 开始
        $guid = req::get('guid');
        $guna = req::get('guname');
        $op = req::get('op');
        $where = "";
        if($guna){
            $guna = db::escape($guna);
            $guna = trim($guna, "'");
            $where = " and t1.user_name like '%{$guna}%'";
        }
        
        if($guid){
            if($op=='del'){
                return $this->delUser($guid);
            }elseif($op == 'edit'){
                return $this->editUser();
            } else {
                $where = " and t1.user_id=".db::escape($guid);
                $isshow = $op=='show';
            }
        }
        //处理操作参数 结束
        $tusdesc = sys::config("cfg_tabdesc.t_game_user");
        $cls = implode(',',array_keys($tusdesc));
        $sql = "select t1.*,t2.login_ip,t2.is_unusual,t2.stop_time from t_game_user t1 "
             . "left join t_game_player_login_info t2 on t1.user_id=t2.user_id "
             . "where t1.account_name!='robot'{$where} order by t1.user_id";
        $rs = gdb::i()->page_query($sql);
        
        //处理单用户内容：
        if($isshow){
            $v->body = view::factory("rowshow",array("keydesc"=>$tusdesc,"row"=>$rs['data'][0]));
            return;
        }
        
        //配置显示列表 开始
        $shtm = array(
            "href"=>ctrl::gen_url(1004, array('guid'=>'{#}','op'=>'show'),false)
        );
        $dhtm = array(
            "href"=>"javascript:ajGet('".ctrl::gen_url(1004, array('guid'=>'{#}','op'=>'show'),false)."')",
            "onclick"=>'return window.confirm("你确定要删除ID：[{#}]的用户吗？");',
        );
        
        //封禁处理
        for($i=0;$i<count($rs['data']);$i++){
            $row = $rs['data'][$i];
            if($row['is_unusual'] > 0 && $row['stop_time']>sys::date_time()){
                $rs['data'][$i]['atype'] = 'jf';
            } else {
                $rs['data'][$i]['atype'] = 'fj';
            }
        }
        
        $table_data = array(
            'data' => $rs['data'], //实际数据数组
            'cols' => array(
                array("col" => 'user_id',           "th" => $tusdesc['user_id']),
                array("col" => "user_name",         "th" => $tusdesc['user_name']),
                array("col" => "user_lv",           "th" => $tusdesc['user_lv']),
                array("col" => "user_Currency",     "th" => $tusdesc['user_Currency']),
                array("col" => "user_Vip_level",    "th" => $tusdesc['user_Vip_level']),
                array("col" => "login_ip",          "th" => '登录IP'),
                array("col" => "stop_time",         "th" => '封禁时间'),
                array("col" => array("atype",'user_id'),
                    "th" => '操作',
                    'td' => html::a(' ',array('uid'=>'{#user_id}',
                        'atype'=>'{#atype}','class'=>'__fj','href'=>"#",
                        'aurl'=>ctrl::gen_url(1013)))
                ),
                //array("col" => "user_id",           "th" => "操作","td"=>html::a("编辑", $shtm)."&nbsp; &nbsp; ".html::a("删除", $dhtm)),
            )
        );
        
        //配置显示列表 结束
        $inputkey = array("guid"=>"用户ID","guname"=>"用户名");
        $v->body = $this->getQueryHTML($inputkey);
        $v->body .= view::factory("table",array("table_data"=>$table_data));
        $v->body .= view::factory("fenye",$rs['pageinfo']);
        $v->body .= html::mktag('script', "fengjin_init()");
    }
    
    
    //功能号：1007
    function a_svrconf() {
        $v = $this->new_view("idx/right");
        $v->title = "系统设置 > 服务器管理";
        $v->body = '';
        $svlist = b_sv_servers::svall();
        $svdat = array();
        foreach($svlist as $sv){
            $svdat[]=$sv->get_data();
        }
        $table_data = array(
            'data' => $svdat, //实际数据数组
            'cols' => array(
                array("col" => 'sv_id',      "th" => 'sv_id'),
                array("col" => "sv_name",    "th" => 'sv_name'),
                array("col" => "sv_host",    "th" => 'sv_host'),
                array("col" => "sv_dbhost",  "th" => 'sv_dbhost'),
                array("col" => "sv_dbname",  "th" => 'sv_dbname'),
                array("col" => "sv_dbuser",  "th" => 'sv_dbuser'),
                //array("col" => "sv_dbpwd",   "th" => 'sv_dbpwd'),
                array("col" => "sv_stat",    "th" => '服务器状态'),
                array("col" => "sv_id",      "th" => "操作","td"=>html::a("编辑")),
            )
        );
        
        $v->body .= view::factory('table',array('table_data'=>$table_data));
    }
    
    //功能号：1008
    function a_setsvrid() {
        $svid = (int)req::get("svid","0");
        req::set_cookie(b_sv_servers::SVIDKEY, $svid);
        $this->st=1;
        $this->msg = "设置完成";
        $this->json_out();
    }
    
    //功能号：1009
    function a_gamebugs() {
        $v = $this->new_view("idx/right");
        $v->title = "A2.运营相关 > 游戏BUG处理";
        $svb = $this->getsvrinfo();
        if(!$svb->sv_id){
            $v->body = "先选择要操作的服务器！";
            return;
        }
        
        $gbid = (int)req::get('gbid');
        $op = req::get('op');
        $gbtxt = db::escape(req::get('gbtxt'));
        $guid = (int)req::get('guid');
        $guname = req::get('guname');
        $resp = strtolower(req::get("resp")) == "y";
        $_GET['resp'] = $resp ? 'y' : 'n';
        
        if($gbid && $op){
            if($op == 'ig'){
                $sql = "update t_game_bug_report set bug_status='ig_{$this->__yhid}' where bug_id={$gbid}";
                $sql = gdb::i()->query($sql);
            } elseif($op == 're'){
                $tm = sys::date_time();
                $tme = sys::date_time(86400*15);
                $sql = "INSERT INTO t_game_player_msg_for_person "
                     . "(receiver_id,send_time,message_type,message,message_title,sender_name,expire_time) VALUES " 
                     . "({$guid},'{$tm}',2,{$gbtxt},'re:BUG反馈[{$gbid}]','GM：[{$this->__yh->yh_name}]','{$tme}');";
                gdb::i()->query($sql);
                $sql = "update t_game_bug_report set bug_status='re_{$this->__yhid}' where bug_id={$gbid}";
                $sql = gdb::i()->query($sql);
            }
            if($sql){
                $this->st=1;
                $this->msg="更新完成";
            }
            return $this->json_out();
        }
        
        $where = " and t2.bug_status is " . ($resp ? "not null" :"null");
        $guid && ($where .= " and t2.player_id={$guid}");
        
        $sql = "select t1.user_id,t1.user_name,t1.user_Vip_level,t2.* "
             . "from t_game_user t1,t_game_bug_report t2 "
             . "where t1.user_id = t2.player_id{$where} order by "
             . "t1.user_Vip_level desc,t2.bug_id desc";
        $rs = gdb::i()->page_query($sql);
        
        $tusdesc = sys::config("cfg_tabdesc.t_game_user") 
                 + sys::config('cfg_tabdesc.t_game_bug_report');
        
        $table_data = array(
            'data' => $rs['data'], //实际数据数组
            'cols' => array(
                array("col" => 'user_id',"th" => $tusdesc['user_id']),
                array("col" => "user_name","th" => $tusdesc['user_name']),
                array("col" => "user_Vip_level","th" => $tusdesc['user_Vip_level']),
                array("col" => "bug_id","th" => $tusdesc['bug_id']),
                array("col" => "begin_time","th" => $tusdesc['begin_time']),
                array("col" => "report","th" => $tusdesc['report']),
            )
        );
        if(!$resp){
            $table_data['cols'][] = array(
                'col'=>'bug_id',
                'th'=>'回复信息',
                'td'=>html::mktag('input', false, array(
                    'id'=>'gbugid{#}' , 'class'=>'wfull',
                    'value'=>'感谢您的反馈，我们会尽快处理！',
                )),
            );
            
            $url = ctrl::gen_url(1009)."&gbid={#bug_id}&op=";
            $table_data['cols'][] = array(
                'col'=>array('bug_id','user_id'),
                'th'=>'操作',
                'td'=>html::a('回复', array(
                    'href'=>"javascript:ajGet('{$url}re&guid={#user_id}&gbtxt='+encodeURIComponent($('#gbugid{#bug_id}').val()),true)"
                )) . "&nbsp; &nbsp; &nbsp;" .
                html::a("忽略", array(
                    'href'=>"javascript:ajGet('{$url}ig',true)"
                )),
            );
        }
        
        //配置显示列表 结束
        $inputkey = array('guid'=>'用户ID','resp'=>'是否回复[y|n]');
        $v->body = $this->getQueryHTML($inputkey);
        $v->body .= view::factory("table",array("table_data"=>$table_data));
        $v->body .= view::factory("fenye",$rs['pageinfo']);
    }
    
    
    //功能号：1010
    function a_sysmail() {
        $this->mail("A3.邮件 > 系统邮件", 'sys');
    }
    
    //功能号：1011
    function a_usrmail() {
        $this->mail("A3.邮件 > 玩家邮件", 'user');
    }
    
    
    //功能号：1013
    function a_fengjin(){
        $atype = req::get('atype');
        $guid = (int)req::get('guid');
        
        if($atype=='fj'){
            $tian = (int)req::get('tian') * 86400;
            $tian = sys::date_time($tian);
            $sql = "update t_game_player_login_info set stop_time='{$tian}',is_unusual=1 where user_id={$guid}";
            $b = b_sv_servers::getCookidSV_ID();
            $b = b_sv_servers::i($b);
            $url = "http://{$b->sv_host}/game?{\"miUserID\":{$guid},\"miProtocolId\":156}";
            @util::http_get($url);
        } elseif($atype=="jf"){
            $sql = "update t_game_player_login_info set stop_time=null,is_unusual=0 where user_id={$guid}";
        }
        gdb::i()->query($sql);
        $this->st=1;
        $this->msg="操作完成";
        $this->json_out();
    }


    /////////////////////////////////  通用方法 /////////////////////////////////
    
    function mail($title,$type){
        $v = $this->new_view("idx/right");
        $v->title = $title;
        $v->body = '';
        $svb = $this->getsvrinfo();
        if(!$svb->sv_id){
            $v->body = "先选择要操作的服务器！";
            return;
        }
        
        $guids   = explode(';', req::post('guids'));
        
        $stime   = trim(req::post('stime'));
        $etime   = trim(req::post('etime'));
        
        $jinqian = (int)req::post('jq');
        $xianyu  = (int)req::post('xy');
        $jjcbi   = (int)req::post('jjc');
        $mailtit = req::post('mailtit','系统邮件');
        $mailtxt = req::post('mailtxt');
        $dj_ids  = req::post('dj_id');
        $dj_shu  = req::post('dj_shu');
        $isupdate= req::post('update') == 'true';
        
        if($isupdate){
            
            
            $advs = array();
            $stradv = '';
            $jinqian > 0 && ($advs[] = "10 10 {$jinqian}");
            $xianyu  > 0 && ($advs[] = "11 11 {$xianyu}");
            $jjcbi   > 0 && ($advs[] = "13 13 {$jjcbi}");
            
            if($dj_ids && count($dj_ids)){
                $djshu = array();
                foreach($dj_ids as $k=>$val){
                    $sh = (int)$dj_shu[$k];
                    if($sh>0){
                        $djshu[$val] = $sh;
                    }
                }
                if (($djs = b_dj_wupin::get_list("dj_id in (:v1)", array('v1'=> array_keys($djshu)))) && is_array($djs)){
                    foreach($djs as $r){
                        $advs[] = "{$r->dj_id} {$r->dj_type} {$djshu[$r->dj_id]}";
                    }
                }
            }
            
            if($advs){
                $advs = array_slice($advs, 0, 5);
                $stradv = count($advs)." ".implode(' ', $advs);
            }
            
            $mail = array(
                'additional'=>$stradv,
                'message'=>$mailtxt,
                'message_title'=>$mailtit,
                'sender_name'=>"GM：[{$this->__yh->yh_name}]",
            );
            if($type == 'sys' && $stime && $etime){
                $mail['message_type'] = 1;
                $mail['send_time'] = $stime;
                $mail['expire_time'] = $etime;
                $isdone = gdb::i()->insert_row('t_game_player_msg', $mail);
            } elseif($type == 'user') {
                $mail['send_time'] = sys::date_time();
                $mail['expire_time'] = sys::date_time(86400*15);
                $mail['message_type'] = 2;
                if(is_array($guids) && count($guids)>0){
                    foreach($guids as $uid){
                        if($uid>0){
                            $mail['receiver_id'] = $uid;
                            $isdone = gdb::i()->insert_row('t_game_player_msg_for_person', $mail);
                        }
                    }
                }
            }
            if ($isdone){
                $url = ctrl::gen_url(ctrl::get_info());
                $link=html::a("这里", array('href'=>$url));
                return $v->body = "<h3>操作成功。点击 [{$link}] 返回</h3>";
            }
            $v->body .= '<font color="red">参数错误，请检查参数！</font><br>';
        }
        
        $v->body .= view::factory('idx/sendmail',array('type'=>$type))->render();
        
    }
            
    function editUser() {
        $uid = req::get("guid");
        //  TODO
    }
    
    function delUser($guid){
        $sql = "delete from t_game_user where user_id={$guid}";
        gdb::i()->query($sql);
        $this->st=1;
        $this->msg = "操作完成";
        $this->json_out();
    }

    function getsvrinfo(){
        $svid = b_sv_servers::getCookidSV_ID();
        $b = null;
        if($svid>0){
             $b=  b_sv_servers::i($svid);
        }
        return $b;
    }
    
           
    function getQueryHTML(array $param) {
        return view::factory("idx/guquery",array("dat"=>$param))->render();
    }
    
}
