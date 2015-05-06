
function ajGet(url, noalert) {
    $.get(url, function(dat) {
        try {
            dat = $.parseJSON(dat);
            if (!noalert || dat.st == 0) {
                alert(dat.msg);
            }
            if (dat.st == 1) {
                location.reload();
            }
        } catch (e) {
            alert(dat);
        }
    });
}

function intval(value, onFalse){
    value = parseInt(value);
    return isNaN(value) ? (onFalse !== undefined ? onFalse : 0) : value;
}

function fengjin_init(){
    $('.__fj').click(function(){
        var o = $(this);
        var atype = o.attr('atype');
        var url = o.attr('aurl') + "&guid="+o.attr('uid')+"&atype="+atype;
        if(atype=='fj'){
            var day = prompt("请输入封禁天数","365");
            day = intval(day);
            if(day>0){
                url += "&tian="+day;
                ajGet(url)
            }
        } else if(atype=='jf'){
            ajGet(url);
        }
        return false;
    }).each(function(){
        var o = $(this);
        var atype = o.attr('atype');
        if(atype=='fj'){
            o.text("封")
        }else{
            o.text('解')
        }
    })
}