<?php
use PhpRedis\Redis;

final class HashTest extends PHPUnit\Framework\TestCase
{
    protected static $redis;

    public static function setUpBeforeClass()
    {
        self::$redis = new Redis();
    }
    
    public function testHmset()
    {
        self::$redis->HMSET("website", "google", "www.google.com", "yahoo", "www.yahoo.com", "baidu", "www.baidu.com");
        $array = [
            'google' => 'www.google.com',
            'yahoo'  => 'www.yahoo.com',
            'baidu'  => 'www.baidu.com',
        ];
        $this->assertEquals([], array_diff($array, self::$redis->HGETALL("website")));
    }
}
