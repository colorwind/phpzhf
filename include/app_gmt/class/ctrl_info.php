<?php

/**
 *
 * @author zhf
 */
class ctrl_info {
    private $class_name = null;
    private $action_name = null;
    private $gongneng = null;
    
    public function __construct($class_name,$action_name,  b_xt_dm_gongneng $gn=null) {
        if($class_name && $action_name){
            $this->class_name =  substr($class_name, 0,2) == 'c_' ? $class_name : 'c_'.$class_name;
            $this->action_name = substr($action_name,0,2) == 'a_' ? $action_name : 'a_'.$action_name;
            $this->gongneng = $gn;
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
    
    public function get_gongneng(){
        return $this->gongneng;
    }

    public function __get($name) {
        switch ($name) {
            case 'class_name':
                return $this->class_name;
            case 'action_name':
                return $this->action_name;
            case 'gongneng':
                return $this->gongneng;
        }
        return NULL;
    }
}
