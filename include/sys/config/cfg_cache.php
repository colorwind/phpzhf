<?php

return array(
    'default' => array(
        'enabled' => FALSE, //[FALSE|TRUE];
        'type' => 'mem',    //[apc|mem|redis];
        'mem' => array(
            'persistent' => FALSE, //[FALSE|TRUE];
            'weight' => 1,
            'exptime' => 900,
            'prefix' => 'gamecenter', //key前缀
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
    )
);
