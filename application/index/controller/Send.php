<?php
namespace app\index\controller;

use think\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Send
{
    public function sendMsg()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('hello', false, false, false, false);
    
        $msg = new AMQPMessage('Hello World!');
        $channel->basic_publish($msg, '', 'hello');
    
        echo " [x] Sent 'Hello World!'\n";
    
        $channel->close();
        $connection->close();
    }
}