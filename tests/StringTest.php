<?php
use PhpRedis\Redis;
use PHPUnit\Framework\TestCase;

final class StringTest extends TestCase
{
    protected static $redis;

    public static function setUpBeforeClass()
    {
        self::$redis = new Redis();
        self::$redis->flushall();
    }

    public function testAppend()
    {
        self::$redis->set("string1", "string1");
        self::$redis->append("string1", " string2");
        $this->assertEquals("string1 string2", self::$redis->get("string1"));

        self::$redis->append("string3", "string3");
        $this->assertEquals("string3", self::$redis->get("string3"));
    }

    public function testSet()
    {
        self::$redis->set("string", "test");
        $this->assertEquals("test", self::$redis->get("string"));

        self::$redis->set("int", 100);
        $this->assertEquals(100, self::$redis->get("int"));
    }
 
    public function testGet()
    {
        $this->assertEquals("test", self::$redis->get("string"));
        $this->assertEquals(100, self::$redis->get("int"));
        $this->assertEquals(-1, self::$redis->get("none"));
    }
}
