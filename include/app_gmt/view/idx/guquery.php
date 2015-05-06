<div class="wfull">
    <form action="<?php echo SYSMAIN; ?>">
        <input name="a" type="hidden" value="<?php echo ctrl::$GongNeng->gn_dmid; ?>" >
        查询> &nbsp;&nbsp;
        <?php
        foreach($dat as $k => $v){
            echo $v.':<input name="'.$k.'" value="'.html::chars(req::get($k)).'"> &nbsp;&nbsp;';
        }
        ?>
        <input type="submit" value="查询" >
    </form>
</div>
