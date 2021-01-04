<?php
namespace app\index\controller;

use think\Controller;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use think\Log;

class Rabbitmq
{
    const consumerTag = 'consumer'; // 消费者标签
    const exchange = 'router'; // 交换机名
    const queue = 'msgs';// 的队列名

    /**
     * 推入消息到队列中
     */
    public static  function pushMessage()
    {
        $data = [
            "test" => 32132132
        ];
        // 连接rabbitMQ
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        // 开启一个信道
        $channel = $connection->channel();

        // 声明一个队列
        // queue 队列名
        // passive 检测队列是否存在  true 只检测不创建 false 创建
        // durable 是否持久化队列 true 为持久化
        // exclusive 私有队列 不允许其它用户访问  设置true 将会变成私有
        // auto_delete  当所有消费客户端连接断开后，是否自动删除队列
        $channel->queue_declare(self::queue, false, true, false, false);

        // exchange 交换机名称
        // type 交换器类型
        // passive 检测交换机是否存在   true 只检测不创建 false 创建
        // durable 是否持久化队列 true 为持久化
        // auto_delete  当所有绑定队列都不在使用时，是否自动删除交换器 true：删除false：不删除
        $channel->exchange_declare(self::exchange, 'direct', false, true, false);
        // 绑定队列和交换机
        $channel->queue_bind(self::queue, self::exchange);
        // 写入队列的消息
        $messageBody = json_encode($data) ;
        // 消息内容
        // delivery_mode  投递模式  delivery mode 设置为 2标记持久化
        $message = new AMQPMessage($messageBody, array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        // $message 消息内容
        // $exchange  交换器名称
        // routing_key   路由键 (routing key)  主题交换机会用到
        $channel->basic_publish($message, self::exchange,'');
        // 关闭信道
        $channel->close();
        //关闭 amqp 连接
        $connection->close();
        return "ok";
    }


    function shutdown($channel, $connection)
    {
        $channel->close();
        $connection->close();
        Log::info("closed",3);
    }

    function process_message($message)
    {
        if ($message->body !== 'quit') {
            $obj = json_decode($message->body);
            if (!isset($obj->id)) {
                echo 'error data\n';
                //  消费成功会在 日志里面写入一条数据
                Log::info("error data111111111111111:" . $message->body, 2);
            } else {
                try {
                    Log::info("data:" . json_encode($message));
                } catch (\Think\Exception  $e) {
                    Log::info($e->getMessage(), 2);
                    Log::info(json_encode($message), 2);
                } catch (\PDOException $pe) {
                    Log::info($pe->getMessage(), 2);
                    Log::info (json_encode($message), 2);
                }
            }
        }
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        // Send a message with the string "quit" to cancel the consumer.
        if ($message->body === 'quit') {
            $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
        }
    }

    /**
     * 启动
     *
     * @return \think\Response
     */
    public function start()
    {

        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare(self::queue, false, true, false, false);
        $channel->exchange_declare(self::exchange, 'direct', false, true, false);
        $channel->queue_bind(self::queue, self::exchange);

        // queue   队列名称
        // consumer_tag  消费者标签
        // no_ack  在设置了 no_ack=false 的情况下）只要 consumer 手动应答了 Basic.Ack ，就算其“成功”处理了
        // no_ack=true （此时为自动应答）
        // exclusive  是否是私有队列 设置true 将会变成私有
        // callback = null, 回调函数
        $channel->basic_consume(self::queue, self::consumerTag, false, false, false, false, array($this, 'process_message'));

        // 不管你的php代码执行是否成功，最后都会执行 shutdown方法，关闭信道和连接
        register_shutdown_function(array($this, 'shutdown'), $channel, $connection);
        while (count($channel->callbacks)) {
            $channel->wait();
        }
        Log::info ("starting",3);
    }

}