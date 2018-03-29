<?php
namespace PhpRedis;

class Config
{
    public static $redisConfig = [
        'host' => 'localhost',
        'port' => 6379,
        'password' => '123456',
        'ctimeout' => 10,
        'rwtimeout' => 10,
    ];
}
