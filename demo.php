<?php
require 'vendor/autoload.php';
use PhpRedis\Config;
use PhpRedis\Redis;

$redis = new Redis(1);
$str = "this";
var_dump($redis->set('a', $str));
var_dump($redis->get('a'));
