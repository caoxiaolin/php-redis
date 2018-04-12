<?php
namespace redisdemo;

require 'vendor/autoload.php';

use PhpRedis\Redis;

class TestRedis
{
    public function test()
    {
        $redis = new Redis();
        $res = $redis->set("a", 111);
        var_dump($res);
    }

    //消息订阅回调
    public function message(){
        try{
            $redis = new Redis();
            $res = $redis->psubscribe("testmsg", "test*",  __NAMESPACE__ .'\TestRedis::callback');
        }
        catch (Exception $e){
            var_dump($e->getMessage());
        }
    }
    public function callback($arr)
    {
        //pmessage
        //pattern
        //channel
        //msg
        var_dump($arr);
    }
}

$obj = new TestRedis();

//订阅消息消费
$obj->message();
