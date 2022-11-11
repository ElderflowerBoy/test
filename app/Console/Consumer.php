<?php

namespace App\Console;

use Closure;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

class Consumer
{
    public function __construct(protected int $processId) { }

    /**
     * @throws \ErrorException
     */
    public function __invoke(): void
    {
        $queueName = 'consumer_' . $this->processId;

        $connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USERNAME'],
            $_ENV['RABBITMQ_PASSWORD']
        );

        $channel = $connection->channel();
        $channel->basic_qos((int)null, 1, (bool)null);
        $channel->exchange_declare(
            $_ENV['RABBITMQ_EXCHANGE_NAME'],
            'x-consistent-hash',
            false,
            true,
            false
        );

        $arg = new AMQPTable(["x-single-active-consumer" => true]);

        $channel->queue_declare($queueName, durable: true, auto_delete: false, arguments: $arg);

        $channel->queue_bind($queueName, $_ENV['RABBITMQ_EXCHANGE_NAME'], 1);

        $channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            false,
            false,
            $this->callback()
        );

        register_shutdown_function($this->shutdown(), $channel, $connection);

        $channel->consume();
    }

    public function callback(): Closure
    {
        return function ($msg) {
            $b = json_decode($msg->body, true);
            $message = "User " . $b['user_id'] . " " . $b['message'] . PHP_EOL;
            echo $message;
            sleep(1);
            $msg->ack();
        };
    }

    function shutdown(): Closure
    {
        return function (AMQPChannel $channel, AbstractConnection $connection) {
            $channel->close();
            $connection->close();
        };
    }
}

require_once(__DIR__ . '/../../vendor/autoload.php');

try {
    $n = 0;
    $options = getopt("n:");
    if (isset($options['n'])) {
        $n = intval($options['n']);
    }

    (new Consumer($n))();
} catch (\ErrorException $e) {
}
