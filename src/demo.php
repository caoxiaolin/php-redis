<?php
require 'vendor/autoload.php';
use PhpRedis\Config;
use PhpRedis\Redis;

$redis = new Redis();
var_dump($redis->get('a'));
