#!/bin/sh

PATH_SOCCER="/usr/ft/gamecenter";
COMMADN_PHP="/usr/local/fastweb/php_fpm/bin/php";

cd $PATH_SOCCER;

if [ -n "$1" ];then
	if [ `echo $1 | awk -F\= '{print $1}'` = "a" ];then
		$COMMADN_PHP $PATH_SOCCER/i.php $1 $2
	else
		echo "参数不正确！";
	fi
fi
