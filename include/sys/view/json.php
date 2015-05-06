<?php
isset($val) || (isset($data) && ($val=$data)) || ($val = false);
$val = json_encode($val);
if(($cb=req::request('pzcallback'))){
    $val = $cb."({$val})";
}
echo $val;

