<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>GMT</title>
        <link href="resource/css/right.css" rel="stylesheet" type="text/css" />
        <script src="resource/js/jquery.js"></script>
        <script src="resource/js/gmt.js"></script>
        <script>
            $(function() {
                $('#svrset').change(function() {
                    var val = this.value
                    ajGet('<?php echo ctrl::gen_url(array("index", "setsvrid")); ?>&svid='+val);
                })
            })
        </script>
        
    </head>
    <body>
        <div class="wfull">
            GMT管理：<?php echo $title; ?>
            <span class="worning right"> 
                服务器：
                <select id="svrset">
                    <option value="0">请选择操作的服务器</option>
                    <?php
                    $svrs = b_sv_servers::svall();
                    if ($svrs) {
                        foreach ($svrs as $row) {
                            $pas = array("value" => $row->sv_id);
                            if (req::get_cookie(b_sv_servers::SVIDKEY) == $row->sv_id) {
                                $pas["selected"] = "true";
                            }
                            echo html::mktag("option", $row->sv_name.($row->sv_stat==0?'[无效]':''), $pas);
                        }
                    }
                    ?>
                </select>
            </span>
        </div>
        <hr>
        <div class="wfull">
            <?php echo $body; ?>
        </div>
    </body>
</html>
