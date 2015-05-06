<div class="wfull">
    <form action="<?php echo SYSMAIN; ?>" method="post">
        <input type="hidden" name="a" value="<?php echo  req::i()->gong_neng['gn_dmid']; ?>" />
        <input type="hidden" name="update" value="true" />
        <?php if ($type == 'user') { ?>
        <p>
            用户ID：<input name="guids" value="<?php echo html::chars(req::post('guids')); ?>" /> &nbsp; &nbsp;使用“;”分割
        </p>
        <?php } else { ?>
        <p>
            开始时间：<input name="stime" value="<?php echo  html::chars(req::post('stime')); ?>" /> &nbsp; &nbsp;
            结束时间：<input name="etime" value="<?php echo  html::chars(req::post('etime')); ?>" /> &nbsp; &nbsp;
            时间格式：2015-01-31 15:04:05
        </p>
        <?php } ?>
        <p>
            金钱数：<input name="jq" value="<?php echo html::chars(req::post('jq')); ?>" /> &nbsp; &nbsp;
            仙玉数：<input name="xy" value="<?php echo html::chars(req::post('xy')); ?>" /> &nbsp; &nbsp;
            竞技场币数：<input name="jjc" value="<?php echo html::chars(req::post('jjc')); ?>" /><br>
        </p>
        
        <p>
            邮件标题：<input name="mailtit" value="<?php echo html::chars(req::post('mailtit')); ?>" /><br>
            邮件内容：<textarea class="wfull" rows="5" name="mailtxt"><?php echo html::chars(req::post('mailtxt')); ?></textarea>
        </p>
        <p>
        <table class="tab-1">
            <tr><th>已选择的物品 最多5个</th></tr>
            <tr><td height="50px"><p id="wpchecked" class="fenye"></p></td></tr>
        </table>

        <div class="center">
            如果检查无误
            <input type="submit" value="确认发送" />
        </div>
        物品列表：
        <?php
        $djlist = b_dj_wupin::djall();
        $b = new b_dj_wupin(11);
        $list = array();
        foreach ($djlist as $dj) {
            $list[] = $dj->get_data();
        }
        $table_data = array(
            'table_att' => 'border="0" cellspacing="0" class="tab-1" id="wpchk"',
            'data' => $list, //实际数据数组
            'cols' => array(
                array("col" => array('dj_name', 'dj_id'), "th" => "名称", 'td' => html::a(" ", array('name' => '{#dj_id}')) . '{#dj_name}'),
                array("col" => "dj_adv", "th" => "品质", "desc" => b_dj_wupin::$ADV),
                array(
                    "col" => array("dj_id", "dj_name", "dj_adv"),
                    "th" => "选择物品&nbsp; &nbsp; <button id='clr'>清空选择</button>",
                    "td" => html::mktag('input', false, array(
                        'type' => 'checkbox', 'name' => 'dj_id[]', 'id' => 'wpbox_{#dj_id}', 'value' => '{#dj_id}',
                        'dj_id' => '{#dj_id}',
                        'dj_name' => '{#dj_name}',
                        'dj_adv' => '{#dj_adv}'
                    ))
                ),
            )
        );
        echo view::factory('table', array('table_data' => $table_data));
        ?>
        </p>

    </form>
</div>
<script>
    function wpshuch(o) {
        o = $(o)
        var id = o.attr('dj_id');
        var t = intval(o.val());
        var shu = Math.max(1, t);
        o.val(shu);
        $('#wpspan_' + id).text("x" + shu);
    }

    $(function() {
        $('#clr').click(function() {
            $(':checkbox', '#wpchk').each(function() {
                if (this.checked) {
                    $(this).click()
                }
            });
            return false;
        })

        $(':checkbox', '#wpchk').change(function() {
            var o = $(this)
            var id = o.attr('dj_id');
            var name = o.attr('dj_name');
            var adv = o.attr('dj_adv');
            if (this.checked) {
                var str = '<a id="wpshow_' + id + '" class="wpadv_' + adv + '" href="#' + id + '">' + name + '<span id="wpspan_' + id + '">x1</span></a>';
                $('#wpchecked').append(str);
                o.parent().append('<input style="width:30px" onchange="wpshuch(this)" name="dj_shu[]" value="1" dj_id="' + id + '" id="wpshu_' + id + '">');
            } else {
                $('#wpshow_' + id).remove();
                $('#wpshu_' + id).remove();
            }
        })

<?php
$_wpbox = req::post("dj_id");
for ($i = 0; $i < count($_wpbox); $i++) {
    echo '$("#wpbox_' . $_wpbox[$i] . '").click();';
}
?>
    })
</script>
