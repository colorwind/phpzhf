<?php
/**
 * Description of log
 *
 * @author zhf
 */
final class log {

    
    //获取log文件目录
    private static function getLogFile($filename="",$logtype="logs"){
        $filename = preg_replace("/\W/","", $filename);
        $logtype = preg_replace("/\W/","" ,$logtype);
        $path = UPLOADPATH."/$logtype/". date("Ym");
        $fn = $path."/".$filename . date('Ymd').".log";
        if(!is_dir($path)){
            mkdir($path, 0777, true);
        }
        return $fn;
    }
    
    
    static function D($msg,$filename="") {
        if(SYSDEBUG){
            self::L($msg, $filename,"DEBUG ");
        }
    }
    
    static function I($msg,$filename="") {
        self::L($msg, $filename,"INFOR ");
    }
    
    static function E($msg,$filename="") {
        self::L($msg, $filename,"ERROR ");
    }
    
    static function W($msg,$filename="") {
        self::L($msg, $filename,"WARNI ");
    }
    
    static function ilog($msg='') {
        $db = db::i(); //select|insert|update|replace|delete|truncate
        $msg && ($msg .= ';');
        $ilog = round((microtime(TRUE) - SYS_START_TIME), 3)
                . ',' . (memory_get_peak_usage() - SYS_START_MEMORY)
                . ',' . intval(util::arr($db->sql_array, 'insert', 0) + util::arr($db->sql_array, 'replace', 0))
                . ',' . intval(util::arr($db->sql_array, 'delete', 0) + util::arr($db->sql_array, 'truncate', 0))
                . ',' . intval(util::arr($db->sql_array, 'update', 0))
                . ',' . intval(util::arr($db->sql_array, 'select', 0) + util::arr($db->sql_array,'desc',0)) 
                . ',' . intval(util::arr($db->sql_array, 'count' , 0))
                . ',"' . $msg. '"'
        ;

        $ilog = str_replace(array("\r", "\n"), ' ', $ilog);
        self::L($ilog, 'ilog',"","ilog");
    }
    
    private static function L($msg,$filename,$tag="",$logtype='logs'){
        $logf = self::getLogFile($filename, $logtype);
        
        if (is_array($msg) || is_object($msg)) {
            ob_start();
            var_dump($msg);
            $msg = ob_get_clean();
        }

        $a = util::gen_querystring(array_merge($_GET,$_POST), false);
        $meth = util::arr($_SERVER, 'REQUEST_METHOD','NULL');
        $msg = $tag.date('ymdHis').",$meth,$msg,\"$a\"\r\n";
        file_put_contents($logf, $msg, FILE_APPEND | LOCK_EX);
        chmod($logf, 0777);
    }
    
}
