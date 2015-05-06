<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of m_cache_apc
 *
 * @author Administrator
 */
class m_cache_none  extends  mc{
    
    
    protected function _set($key, $value, $exptime = NULL){
        return TRUE;
    }
    
    
    protected function _get($key, $default = NULL){
        return $default;
    }


    protected function _delete($key){
        return TRUE;
    }
    
    
    protected function _flush(){
        return TRUE;
    }
}