<?php

namespace App\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Publisher
{
    /**
     * @throws \Exception
     */
    public function __invoke(int $userId, string $message): void
    {
        $connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'], $_ENV['RABBITMQ_PORT'], $_ENV['RABBITMQ_USERNAME'], $_ENV['RABBITMQ_PASSWORD']
        );

        $channel = $connection->channel();

        $channel->exchange_declare(
            $_ENV['RABBITMQ_EXCHANGE_NAME'],
            'x-consistent-hash',
            false,
            true,
            false
        );

        $message = ['user_id' => $userId, 'message' => $message];

        $msg = new AMQPMessage(json_encode($message));

        $channel->basic_publish($msg, $_ENV['RABBITMQ_EXCHANGE_NAME'], $userId);

        $channel->close();
        $connection->close();
    }
}