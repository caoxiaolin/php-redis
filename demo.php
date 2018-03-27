<?php
require 'vendor/autoload.php';
use PhpRedis\Config;
use PhpRedis\Redis;

$redis = new Redis();
$str = "this is a\" test";
var_dump($redis->set('a', $str));
var_dump($redis->get('a'));
