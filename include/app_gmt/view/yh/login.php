<html>
    <head>
        <script src="resource/js/jquery.js"></script>
        <script>
            $(function() {
                $('input').focus(function() {
                    $('#errmsg').html('')
                })
            })
            if(top!=this){
                top.location.reload();
            }
        </script>
    </head>
    <body>

        <form action="<?php echo ctrl::gen_url(array('yonghu', "login")); ?>" method="post">
            用户：<input name="uname" value="<?php echo html::chars(req::post("uname")); ?>"><br>
            密码：<input name="passwd" type="password" value="<?php echo html::chars(req::post("passwd")); ?>"><br>
            <input type="submit" value="登录" >
        </form>

        <div id="errmsg" style="color: red">
            <?php echo $err; ?>
        </div>


    </body>
</html>