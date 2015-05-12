#!/bin/sh

#run as : #> autorun.sh a=1000&key=val&key=val&...

PZ_PHP_SRC="/pathto/phpzhf"
PZ_PHP_CMD="/usr/bin/php"
PZ_SYSMAIN="g.php"

cd $PZ_PHP_SRC

if [ -n "$1" ];then
    $PZ_PHP_CMD -f $PZ_SYSMAIN $1
else
    echo "参数不正确！";
fi
