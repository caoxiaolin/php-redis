<?php
namespace redisdemo;

require 'vendor/autoload.php';

use PhpRedis\Redis;

class TestRedis
{
    public function test()
    {
        $redis = new Redis();
        $redis->flushall();
        $res = $redis->set("a", 1);
        $res = $redis->set("b", 2);
        $res = $redis->set("c", 3);
        $res = $redis->scan(0);
        //var_dump($res);
    }

    /**************  消息订阅回调  **************/
    public function message() {
        $redis = new Redis();
        $res = $redis->psubscribe("testmsg", "test*", __NAMESPACE__ . '\TestRedis::callback');
    }
    public function callback($arr)
    {
        //pmessage
        //pattern
        //channel
        //msg
        echo json_encode($arr);
    }
    /********************************************/

    public function transaction()
    {
        $redis = new Redis();
        $redis->flushall();
        $redis->watch("start");
        $redis->MULTI();
        $redis->SET("start", 1);
        $redis->INCR("id");
        $redis->INCR("id");
        $redis->INCR("id");
        $redis->SET("over", 1);
        sleep(10);
        $redis->EXEC();
        //$redis->DISCARD();
        //var_dump($res);
        //var_dump($redis->get("id"));
        //var_dump($redis->get("over"));
    }
}

try {
    $obj = new TestRedis();

    //订阅消息消费
    //$obj->message();

    //事务
    //$obj->transaction();

    $obj->test();

}catch (\Exception $e) {
    //var_dump($e->getMessage());
}

