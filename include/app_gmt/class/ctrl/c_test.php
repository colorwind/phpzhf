<?php

class c_test extends ctrl {
    public function a_test(){
        $this->st=1;
        $this->dat = $_GET;
        $this->msg = $_SERVER["REQUEST_METHOD"];
        $this->json_out();
    }
}


