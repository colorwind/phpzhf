<html>
    <body>
        <form action="<?php echo SYSMAIN; ?>" method="post">
            <input type="hidden" name="a" value="<?php echo ctrl::$GongNeng->gn_dmid;?>">
            原密码：<input type="password" name="old" value="<?php echo html::chars(req::post('old'));?>"><br>
            新密码：<input name="new" value="<?php echo html::chars(req::post('new'));?>"><br>
            <input type="submit" value="更改密码">
        </form>
    </body>
</html>