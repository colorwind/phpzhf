<?php
//自定义配置文件
//读取：$conf = sys::config('cfg_tabdesc.t_game_user.user_id') //$conf＝"用户ID";

return array(
    't_game_user' => array(
        'user_id' => "用户ID",
        'user_name' => "用户名称",
        'user_Currency'=>"玩家货币(仙玉,金币,竞技币,远征币)",
        'user_lv'=>'用户等级',
        "user_Vip_level"=>'VIP等级',
        ''
    ),
    't_game_bug_report'=>array(
        'bug_id'=>'BUG ID',
        'player_id'=>'玩家ID',
        'bug_type'=>'BUG类型',
        'report'=>'BUG信息',
        'begin_time'=>'开始时间',
        'bug_status'=>'BUG状态',
    ),
);