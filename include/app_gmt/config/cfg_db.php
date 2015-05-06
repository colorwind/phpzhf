<?php

return array(
    //调用：db::i()->foo()
    'default' => array(
        'hostname'   => '127.0.0.1:3306',
        'database'   => 'xygmt',
        'username'   => 'root',
        'password'   => '123456',
        'persistent' => FALSE,
        'charset'    => 'utf8',
    ),
    
    //调用：db::i('game_s1')->foo()
    'game_s1' => array(
        'hostname'   => '172.0.0.12:3306',
        'database'   => 'xy_s1',
        'username'   => 'root',
        'password'   => '123456',
        'persistent' => FALSE,
        'charset'    => 'utf8',
    ),
);
