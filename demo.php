<?php
require 'vendor/autoload.php';

use PhpRedis\Config;
use PhpRedis\Redis;
use Monolog\Logger;

$logger = new Logger('redis');
try{
    $redis = new Redis(1);
    $res = $redis->mget("a1", "a2", "a3");
    var_dump($res);
}
catch (Exception $e){
    $logger->error($e->getMessage());
}
