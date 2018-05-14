<?php
namespace PhpRedis;

class Config
{
    public static $redisConfig = [
        'host'      => 'localhost',
        'port'      => 6379,
        'password'  => '',
        'ctimeout'  => 10, //connection timeout
        'rwtimeout' => 10, //write & read timeout
        'retries'   => 3, //connection retry times
    ];
}
