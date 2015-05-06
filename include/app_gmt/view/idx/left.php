<html>
    <head>
        <style>
            body {font-size: 12px;line-height: 18px;background-color: gainsboro}
            h3 {margin-bottom:2px}
            .right {float: right}
        </style>
        
    </head>
    <body>
        <h3>当前用户：<?php echo "{$yh->yh_name}[{$yh->r_yhtype}]";?></h3>
        <a href="<?php echo ctrl::gen_url(array('yonghu', 'changepwd'));?>" target="rightFrame">修改密码</a><br>
        <a href="<?php echo ctrl::gen_url(array('yonghu', 'login'), array('logout'=>1));?>" target="rightFrame">退出登录</a>
        <?php

        $keys = "";
        foreach ($dat as $v) {
            if ($keys != $v->cd_tag) {
                echo "<h3>", $v->cd_tag, "</h3>";
                $keys = $v->cd_tag;
            }
            $pas = array(
                "href" => ctrl::gen_url($v->gn_dmid),
                "target" => "rightFrame",
            );
            echo html::a($v->cd_name, $pas)."<br>";
        }
        ?>
    </body>
</html>
