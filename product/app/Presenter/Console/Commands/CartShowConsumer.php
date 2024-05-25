<?php

declare(strict_types=1);

namespace App\Console\Commands;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class CartShowConsumer extends Command
{
    protected $signature = 'rabbitmq:consume-cart-show';

    protected $description = 'Consume messages from the cart queue';

    public function handle()
    {
        $connection = new AMQPStreamConnection(
            Config::get('RABBITMQ_HOST'),
            Config::get('RABBITMQ_PORT'),
            Config::get('RABBITMQ_USER'),
            Config::get('RABBITMQ_PASSWORD'),
            Config::get('RABBITMQ_VHOST')
        );
        $channel = $connection->channel();
        $channel->queue_declare('cart_show', false, true, false, false);

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
        };

        $channel->basic_consume('cart_show', '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
