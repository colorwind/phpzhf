<?php
isset($val) || (isset($data) && ($val=$data)) || ($val = false);
if(is_array($val) ){
    foreach($val as $v){
        echo $v."\n";
    }
} else {
    echo $val."\n";
}
