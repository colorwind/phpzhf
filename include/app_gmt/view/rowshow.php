<?php


$cl = array();
//$keydesc = array();  每个列的说明
//$row =array(); 行数据
if($row && is_array($row)){
    foreach($row as $fid=>$val){
        $name = util::arr($keydesc, $fid,$fid);
        if($name){
            $cl[] = array("name"=>$name,"key"=>$fid,"val"=>$val);
        }
    }
}


$table_data = array(
    'data' => $cl,  //实际数据数组
    'cols' => array(
        array("col" => 'key',  "th" => "字段"),
        array("col" => "name", "th" => "描述"),
        array("col" => "val", "th" => "数值"),
    )
);

echo view::factory('table',array('table_data'=>$table_data))->render();
?>

<div class="wfull center">
    <a href='javascript:history.go(-1)'>返回</a>
</div>
