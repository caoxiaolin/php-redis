<?php
use PhpRedis\Redis;
use PHPUnit\Framework\TestCase;

final class KeyTest extends TestCase
{
    protected static $redis;

    public static function setUpBeforeClass()
    {
        self::$redis = new Redis();
        self::$redis->flushall();
    }

    public function testDel()
    {
        self::$redis->set("string", "test");
        if (self::$redis->get("string")){
            self::$redis->del("string");
            $this->assertEquals(-1, self::$redis->get("string"));
        }

        self::$redis->set("a", 1);
        self::$redis->set("b", 2);
        $this->assertEquals(2, self::$redis->del("a", "b", "c", "d"));
    }

    public function testExists()
    {
        self::$redis->set("string", "test");
        $this->assertEquals(true, self::$redis->exists("string"));
        $this->assertEquals(false, self::$redis->exists("string1"));
    }

    public function testExpire()
    {
        self::$redis->set("string", "test");
        self::$redis->expire("string", 3);
        sleep(1);
        $this->assertEquals(true, self::$redis->exists("string"));
        sleep(2);
        $this->assertEquals(false, self::$redis->exists("string"));
    }
}
