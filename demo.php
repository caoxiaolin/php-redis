<?php
require 'vendor/autoload.php';

use PhpRedis\Redis;
use Monolog\Logger;

$logger = new Logger('redis');
try{
    $redis = new Redis();
    $res = $redis->HSTRLEN("website", "google");
    var_dump($res);
}
catch (Exception $e){
    $logger->error($e->getMessage());
}
