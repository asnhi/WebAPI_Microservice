<?php

namespace App\Presenter\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\AddToCartJob;
use Illuminate\Http\Request; // Thêm import này

class FireEvent extends Command
{
    protected $signature = 'fire';

    public function handle()
    {
        $data = [
            'id' => 1, // ID của game cần thêm vào giỏ hàng
        ];

        AddToCartJob::dispatch($data);
    }
}