<?php
// 配置实例：
//$table_data = array(
////    'table_att' => 'border="0" cellspacing="0" summary="" class="tab-1"',
////    'tr_head_att' => 'class="even"',
////    'tr_att' => array('', 'class="even"'),
////    'minrows'=>0,  //生成最小行数。如果没有数据，经生成空的行。
//    'emptymsg' => '暂无数据！',
//    'data' => array(),  //实际数据数组
//    'cols' => array(
//        array("col" => "__#i__",            "th" => "&nbsp;"),
//        array("col" => 'name',              "th" => "球队名称"),
//        array("col" => "ls_cc_sheng",       "th" => "胜"),
//        array("col" => "ls_cc_ping",        "th" => "平"),
//        array("col" => "ls_cc_fu",          "th" => "负"),
//        array("col" => "ls_jin_qiu",        "th" => "进球"),
//        array("col" => "ls_shi_qiu",        "th" => "失球"),
//        array("col" => "ls_jing_sheng_qiu", "th" => "净胜球"),
//        array("col" => "ls_lian_sheng",     "th" => "连胜"),
//        array("col" => "ls_ji_fen",         "th" => "积分"),
//    )
//);

if (!is_array($table_data) || count($table_data) < 1) {
    return;
}
extract($table_data + array(
    'table_att' => 'border="0" cellspacing="0" class="tab-1"',
    'tr_head_att' => 'class="even"',
    'tr_att' => array('', 'class="even"'),
    'minrows' => 0,
    'emptymsg' => '暂无数据！',
    'data' => array(),
    'cols' => array(),
        ), EXTR_SKIP);

$out = "<table {$table_att}>\n";
$thstr = '';
foreach ($cols as $th) {
    if(isset($th['th'])){
        if(stripos($th['th'],'<t')===0){
            $thstr .= $th['th'];
        } else {
            $thstr .= "<th>{$th['th']}</th>";
        }
    }
}
if($thstr){
     $out .= "\t<thead><tr {$tr_head_att}>{$thstr}</tr></thead>\n";
}


$_c = is_array($tr_att)?count($tr_att):0;
$rs = $data;

$out .= "\t<tbody>\n";
for ($i = 0;is_array($data) && $i < count($rs) || $i < $minrows; $i++) {
    $tr = "\t<tr";
    if (is_array($tr_att)) {
        if ($_c && isset($tr_att[$i % $_c])) {
            $_t = $tr_att[$i % $_c];
            if($_t){
                $tr .= (' ' . $_t);
            }
        }
    } elseif ($tr_att) {
        $tr .= (' ' . $tr_att);
    }
    $tr .= '>';
    foreach ($cols as $td) {
        $col = $td["col"];
        if(!isset ($td['td']) || !$td['td']){
            $_='{#}';
        } else {
            $_ = $td["td"];
        }
        if (strpos($_, '{#') !== FALSE) {
            $is_chars = isset($td['html']) && $td['html'] === TRUE;
            if (is_array($col)) {
                krsort($col);
                foreach ($col as $k) {
                    $val = '';
                    if(isset ($rs[$i][$k])){
                        $val = $rs[$i][$k];
                    }
                    if($val && $is_chars){
                        $val = html::chars($val);
                    }
                    if (!$val && substr($k, 0, 6) == '__#i__') {
                        $val = $i+(int)(substr($k, 6))+1;
                    }

                    if (isset($td["desc"][$k]) && isset($td["desc"][$k][$val])) {
                        $val = $td["desc"][$k][$val];
                    }
                    $_ = str_replace('{#}', $val, $_);
                    $_ = str_replace('{#' . $k . '}', $val, $_);
                }
            } else {
                $val = '';
                if(isset ($rs[$i][$col])){
                    $val = $rs[$i][$col];
                }
                if($val && $is_chars){
                    $val = html::chars($val);
                }
                if (!$val && substr($col, 0, 6) == '__#i__') {
                    $val = $i+(int)(substr($col, 6))+1;
                }
                if (isset($td["desc"]) && isset($td["desc"][$val])) {
                    $val = $td["desc"][$val];
                }
                $_ = str_replace('{#}', $val, $_);
                $_ = str_replace('{#' . $col . '}', $val, $_);
            }
        }

        if(stripos($_, '<td') === FALSE){
            $_ = "<td>{$_}</td>";
        }

        $tr .= $_;
    }
    $tr .= "</tr>\n";
    $tr = preg_replace("/\{#[^\}]*\}/m", "", $tr);
    $out .= $tr;
}
if($i==0){
    $out .= "\t<tr><td colspan=\"".  count($cols) . "\">". $emptymsg ."</td></tr>\n";
}

$out .= "\t</tbody>\n";

$out .= "</table>\n";
echo $out;

