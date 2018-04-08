<?php
namespace PhpRedis;

class Config
{
    public static $redisConfig = [
        'host'      => 'localhost',
        'port'      => 6379,
        'password'  => '',
        'ctimeout'  => 10,
        'rwtimeout' => 10,
        'retries'   => 3,
    ];
}
