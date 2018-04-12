<?php
use PhpRedis\Redis;
use PHPUnit\Framework\TestCase;

final class KeyTest extends TestCase
{
    protected static $redis;

    public static function setUpBeforeClass()
    {
        self::$redis = new Redis();
    }

    public function testDel()
    {
        self::$redis->flushall();
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
        self::$redis->flushall();
        self::$redis->set("string", "test");
        $this->assertEquals(1, self::$redis->exists("string"));
        $this->assertEquals(0, self::$redis->exists("string1"));
    }
    
    /*
    public function testExpire()
    {
        self::$redis->flushall();
        self::$redis->set("string", "test");
        self::$redis->expire("string", 3);
        sleep(1);
        $this->assertEquals(1, self::$redis->exists("string"));
        sleep(2);
        $this->assertEquals(0, self::$redis->exists("string"));
    }

    public function testExpireat()
    {
        self::$redis->flushall();
        self::$redis->set("string", "test");
        self::$redis->expireat("string", time()+3);
        sleep(1);
        $this->assertEquals(1, self::$redis->exists("string"));
        sleep(2);
        $this->assertEquals(0, self::$redis->exists("string"));
    }
    */

    public function testKeys()
    {
        self::$redis->flushall();
        self::$redis->mset("one", 1, "two", 2, "three", 3, "four", 4, "five", 5);
        $this->assertEquals([], array_diff(["one", "two", "four"], self::$redis->keys("*o*")));
        $this->assertEquals([], array_diff(["four", "five"], self::$redis->keys("f*")));
    }

    public function testMove()
    {
        self::$redis->flushall();
        $redis = new Redis(1);
        $redis->flushall();
        self::$redis->set("test", 123);
        self::$redis->move("test", 1);
        $this->assertEquals(-1, self::$redis->get("test"));
        $this->assertEquals(123, $redis->get("test"));
    }

    public function testPersist()
    {
        self::$redis->flushall();
        self::$redis->set("test", 123, "EX", 86400);
        $this->assertGreaterThan(0, self::$redis->ttl("test"));
        self::$redis->persist("test");
        $this->assertEquals(-1, self::$redis->ttl("test"));
    }
}
