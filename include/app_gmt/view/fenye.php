<?php
/**
 *  使用说明：
 *  @param int $total_page     必填参数： 总共页数，可以使用 db::i()->page_query($sql) 查询。该查询包含计算后的总共页数。
 *  @param string $page_key    选填参数： 页码key名称 默认：p 例如：xxx.php?xx=xx&p=3，说明当期是第三页，page_key 为 p；
 *  @param int $current_page   选填参数： 当前页码，默认值为 max((int) $_GET[$page_key],1)；
 *  @param bool $is_json       选填参数： 是否返回json数据，默认false；
 *  @param string $aurl        选填参数： 翻页地址，格式xxx.php?a=xx&b=xx。 
 *                                        默认：'i.php?' . preg_replace('/&p=\d{0,}/', '', $_SERVER['QUERY_STRING'])
 *
 **/
$flag         = util::vd($flag, false);
$page_key     = util::vd($page_key,'p');
$current_page = max((int)util::vd($current_page,util::arr($_GET,$page_key)),1);
$is_json      = util::vd($is_json,false);
$no_echo      = util::vd($no_echo,false);

if (($total_page = (int)util::vd($total_page,util::vd($total_page))) < 1) {
    $total_page  = $current_page;
} elseif ($current_page > $total_page) {
    $current_page = $total_page;
}

$aurl = (isset($aurl) ? $aurl : SYSMAIN . '?' . preg_replace('/&'.$page_key.'=\d*/', '', $_SERVER['QUERY_STRING'])) . "&{$page_key}={#}";

$F = $P = $N = $E = '';
if ($current_page > 1) {
    $F = str_replace('{#}', '1', $aurl);
    $P = str_replace('{#}', $current_page - 1, $aurl);
}
if ($current_page < $total_page) {
    $N = str_replace('{#}', $current_page + 1, $aurl);
    $E = str_replace('{#}', $total_page, $aurl);
}

if($no_echo){
    return;
}

if($is_json){
    $dat = array(
        'current_page' => $current_page,
        'total_pages'  => $total_page,
        'aurl'         => $aurl,
        'F'            => $F,
        'P'            => $P,
        'N'            => $N,
        'E'            => $E
    );
    $html = json_encode($dat);
} else {
    $html = "";
    $F && ($html .= '<a href="' . $F . '">首页</a>');
    $P && ($html .= '<a href="' . $P . '">上一页</a>');
    $N && ($html .= '<a href="' . $N . '">下一页</a>');
    $E && ($html .= '<a href="' . $E . '">末页</a>');
    
    $total="共[{$total}]条";
    
    $html .= 
    // ($flag?'':"<span class='ipt'>第<input type='text' value='{$current_page}' />页<button  max='{$total_page}' cur='{$current_page}' onclick='fenye1.go(this)' aurl='{$aurl}' type='submit'>GO</button></span>").
    "<span class='code'>第{$current_page}/{$total_page}页 {$total}</span>"
    ;
    $html = "<div class=\"fenye wfull center\">{$html}</div>";
}
echo $html;

