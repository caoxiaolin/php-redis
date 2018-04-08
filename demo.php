<?php
require 'vendor/autoload.php';

use PhpRedis\Redis;
use Monolog\Logger;

$logger = new Logger('redis');
try{
    $redis = new Redis();
    $redis->del("website");
    $redis->rpush("website", "google.com");
    $redis->rpush("website", "baidu.com");
    $redis->rpush("website", "rong360.com");
    $res = $redis->LLEN("website");
    var_dump($res);
    $res = $redis->LPOP("website");
    var_dump($res);
    $res = $redis->Lset("website", 3, "yahoo.com");
    var_dump($res);
    $res = $redis->lrange("website", 0, -1);
    var_dump($res);
}
catch (Exception $e){
    $logger->error($e->getMessage());
}
