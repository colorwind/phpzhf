<?php

/**
 * controller 功能信息 ctrl_info
 * 请根据实际业务overwrite此类
 *
 * @author zhf
 */
class ctrl_info {
    private $class_name = null;
    private $action_name = null;
    
    public function __construct($class_name,$action_name) {
        if($class_name && $action_name){
            $this->class_name =  substr($class_name, 0,2) == 'c_' ? $class_name : 'c_'.$class_name;
            $this->action_name = substr($action_name,0,2) == 'a_' ? $action_name : 'a_'.$action_name;
        } else {
            throw new Exception('name can not be empty!');
        }
    }
    
    public function get_class_name(){
        return $this->class_name;
    }
    
    public function get_action_name() {
        return $this->action_name;
    }

    public function __get($name) {
        switch ($name) {
            case 'class_name':
                return $this->class_name;
            case 'action_name':
                return $this->action_name;
        }
        return NULL;
    }
}
