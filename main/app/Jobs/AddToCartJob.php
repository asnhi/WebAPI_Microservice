<?php
declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class AddToCartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        Log::info('Starting AddToCartJob', ['data' => $this->data]);

        try {
            $connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST'),
                env('RABBITMQ_PORT'),
                env('RABBITMQ_USER'),
                env('RABBITMQ_PASSWORD'),
                env('RABBITMQ_VHOST')
            );
            Log::info('Connected to RabbitMQ');

            $channel = $connection->channel();
            $channel->queue_declare('cart_add', false, true, false, false);
            Log::info('Declared queue cart_add');

            $msg = new AMQPMessage(json_encode($this->data));
            $channel->basic_publish($msg, '', 'cart_add');
            Log::info('Published message to cart_add', ['message' => $this->data]);

            $channel->close();
            $connection->close();
            Log::info('Closed RabbitMQ connection');
        } catch (\Exception $e) {
            Log::error('Failed to process AddToCartJob: ' . $e->getMessage(), [
                'data' => $this->data,
                'exception' => $e
            ]);
            throw $e;
        }
    }
}
