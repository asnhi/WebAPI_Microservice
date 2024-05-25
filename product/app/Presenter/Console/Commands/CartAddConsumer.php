<?php

namespace App\Presenter\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Domain\Entities\Game;
use Illuminate\Http\Request; // Thêm import này
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth; // Thêm import này

class CartAddConsumer extends Command
{
    protected $signature = 'rabbitmq:consume-cart-add';
    protected $description = 'Consume add to cart messages';

    public function handle()
    {
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST'),
            env('RABBITMQ_PORT'),
            env('RABBITMQ_USER'),
            env('RABBITMQ_PASSWORD'),
            env('RABBITMQ_VHOST')
        );

        $channel = $connection->channel();
        $channel->queue_declare('cart_add', false, true, false, false); // Đặt 'durable' thành true

        $callback = function ($msg) {
            $data = json_decode($msg->body, true);
            $this->addToCart($data);
        };

        $channel->basic_consume('cart_add', '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    protected function addToCart($data)
    {
        $id = $data['id'];
        $game = Game::findOrFail($id);

        $cart = $this->getCart();

        if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
            $cart[$id]['totalPrice'] = $game->price * $cart[$id]['quantity'];
        } else {
            $cart[$id] = [
                'id' => $game->id,
                'name' => $game->name,
                'price' => $game->price,
                'image' => $game->image,
                'quantity' => 1,
                'totalPrice' => $game->price
            ];
        }

        $this->saveCartInCache($cart);

        $this->info('Added to cart successfully: ' . json_encode($cart));
    }

    protected function getCart()
    {
        $userId = Auth::id();
        $cartKey = 'cart:' . $userId;
        return Cache::get($cartKey, []);
    }

    protected function saveCartInCache($cart)
    {
        $userId = Auth::id();
        $cartKey = 'cart:' . $userId;
        Cache::put($cartKey, $cart);
    }
}