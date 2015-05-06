<?php

return array(
    //调用：mc::i()->foo()
    'default' => array(
        'enabled' => FALSE, //[FALSE|TRUE];
        'type' => 'mem',    //[apc|mem|redis];
        'mem' => array(
            'persistent' => FALSE, //[FALSE|TRUE];
            'weight' => 1,
            'exptime' => 900,
            'prefix' => 'xygmt', //key前缀
            'server' => array(
                array('host' => '127.0.0.1', 'port' => '11211')
            )
        ),
        'redis' => array(
            'persistent' => FALSE, //[FALSE|TRUE];
            'weight' => 1,
            'exptime' => 900,
            'server' => array(
                array('host' => '127.0.0.1', 'port' => '6379', 'database' => 15)
            )
        )
    ),
    
    //调用：mc::i('game_s1')->foo()
    'game_s1' => array(
        'enabled' => FALSE, //[FALSE|TRUE];
        'type' => 'mem',    //[apc|mem|redis];
        'mem' => array(
            'persistent' => FALSE, //[FALSE|TRUE];
            'weight' => 1,
            'exptime' => 900,
            'prefix' => 'games1', //key前缀
            'server' => array(
                array('host' => '172.0.0.12', 'port' => '11211')
            )
        ),
        'redis' => array(
            'persistent' => FALSE, //[FALSE|TRUE];
            'weight' => 1,
            'exptime' => 900,
            'server' => array(
                array('host' => '172.0.0.12', 'port' => '6379', 'database' => 15)
            )
        )
    ),
);
