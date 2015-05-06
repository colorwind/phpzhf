<?php

/**
 * 
 * bean 类的使用说明
 * 
 * b_tbl_demo 使用 b_ 开头，后面跟数据库中表的名字：tbl_demo
 * tbl_demo.sql
 * 注意：使用bean必须有主键
 * CREATE TABLE IF NOT EXISTS `tbl_demo` (
 *   `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键',
 *   `name` varchar(20) NOT NULL COMMENT '名称',
 *   `type_id` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'demo类型',
 *   `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'demo状态：0:无效，1:有效',
 *   PRIMARY KEY (`id`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 * INSERT INTO `xygmt`.`tbl_demo` (`id`, `name`, `type_id`, `status`) VALUES 
 * (1, '测试1', '1', '1'), 
 * (2, '测试2', '2', '0');
 * 
 * 使用： 
 * $pk = 1;
 * $bobj = b_tbl_demo::i($pk);  //获取主键为1的记录。bean中有很多方法，请参考sys/class/bean.php
 * $bobj->name                  //返回当前记录的name字段的值。
 * $bobj->name = "xxxx";        //修改当前name的值。此时并不更新数据库。
 * $bobj->status = 0;           //修改status的值。
 * $bobj->save();               //更新修改到数据库。只有此时，才对数据库更新。
 * $bobj->r_type_name           //返回对应的type对象。等同调用 $bobj->r_type_name()
 * 
 * @author zhf
 * 
 */

class b_tbl_demo extends bean {

//    const DB_NAME = "game_s1";       //string 数据库配置名，cfg_db配置名。使用非默认数据库时设置
//    const TABLE_NAME = "table_name"; //string 数据库表名,除非多数据库表名有重复时，否则尽量使用类名匹配。
//    const PRIMARY_KEY_NAME = "id";   //string 数据表主键名 尽量在简表时设置好主键。一般情况不需要配置
    
    function r_type() {
        return b_tbl_demo_type::i($this->type_id);
    }
    
    function r_type_name() {
        return $this->r_type->type_name; 
        //以"r_"开头的属性，返回bean中对应方法的值。
        //$this->r_type 等同 $this->r_type();
    }
    
    function r_type_desc() {
        return $this->r_type->type_desc;
    }
    
    function r_status() {
        return $this->status==0 ? "无效" : "有效";
    }
}
