<?php
use PhpRedis\Redis;

final class TransactionTest extends PHPUnit\Framework\TestCase
{
    protected static $redis;

    public static function setUpBeforeClass()
    {
        self::$redis = new Redis();
    }
    
    public function test()
    {
        self::$redis->flushall();
        self::$redis->MULTI();
        self::$redis->INCR("id");
        self::$redis->INCR("id");
        self::$redis->SET("succ", 1);
        self::$redis->EXEC();
        $this->assertEquals(2, self::$redis->get("id"));
    }
}
