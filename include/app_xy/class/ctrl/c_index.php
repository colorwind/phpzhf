<?php

/**
 * 测试方法 of c_index
 *
 * @author zhf
 */
class c_index extends ctrl {
    
    
    function a_index(){
        $cinfo = new ctrl_info("index", "test");
        $url = ctrl::gen_url($cinfo, array("name"=>"小明"));
        $this->st=1;
        $this->msg="test_url:" . $url;
        $this->dat = array(1,2,3,4);
        $this->json_out();
    }
    
    function a_test() {
        $name = req::get("name");
        $this->st=1;
        $this->msg = $this->hello($name);
        $this->json_out();
    }
    
    function hello($name) {
        return "hello {$name}";
    }
    
}
