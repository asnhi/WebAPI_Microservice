<?php
declare(strict_types=1);
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ShowCartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle()
    {
        try {
            // Lấy thông tin giỏ hàng từ cache
            $cart = Cache::get('cart:' . auth()->id(), []);

            // In ra thông tin giỏ hàng
            info('Cart content:', $cart);
        } catch (\Exception $ex) {
            // Xử lý nếu có lỗi xảy ra
            info('Error occurred while printing cart:', ['error' => $ex->getMessage()]);
        }
    }
}
