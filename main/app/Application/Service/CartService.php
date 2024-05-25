<?php
declare(strict_types=1);
namespace App\Application\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Domain\Entities\Game;
use App\Domain\Entities\Key;
use Carbon\Carbon;
use App\Jobs\AddToCartJob;

use Illuminate\Support\Str;
use App\Domain\ValueObjects\OrderDetailValueObject;
use App\Domain\ValueObjects\OrderValueObject;


class CartService
{

    protected $orderService;
    protected $orderDetailService;

    public function __construct(OrderService $orderService, OrderDetailService $orderDetailService)
    {
        $this->orderService = $orderService;
        $this->orderDetailService = $orderDetailService;
    }


    public function addToCart(Request $request)
    {
        $data = $request->only('id');
        AddToCartJob::dispatch($data);

        return response()->json(['message' => 'Game added to cart successfully']);
    }


    protected function getCart()
    {
        $userId = auth()->id();
        $cartKey = 'cart:' . $userId;
        return Cache::get($cartKey, []);
    }
    protected function saveCartInCache($cart)
    {
        $cartKey = 'cart:' . auth()->id();
        Cache::put($cartKey, $cart);
    }
    protected function removeCartInCache()
    {
        $cartKey = 'cart:' . auth()->id();
        Cache::forget($cartKey);
    }

    public function payCart(Request $request)
    {
        try {
            // Lấy thông tin người dùng đang đăng nhập
            $user = Auth::user();
    
            // Lấy thông tin giỏ hàng từ session
            $cart = $this->getCart();
    
            // Kiểm tra giỏ hàng có sản phẩm không
            if (empty($cart)) {
                return response()->json(['error' => 'Giỏ hàng của bạn đang trống.'], 400);
            }
    
            // Tạo đơn hàng mới
            $order = $this->createOrder($user, $cart);
    
            // Xóa giỏ hàng sau khi đã đặt hàng thành công
            $this->removeCartInCache();
    
            return response()->json(['message' => 'Đơn hàng của bạn đã được đặt thành công.'], 200);
        } catch (\Exception $ex) {
            return response()->json(['error' => 'Đã xảy ra lỗi khi đặt hàng.'], 500);
        }
    }
    
    private function createOrder($user, $cart)
    {
        $currentTime = Carbon::now();
    
        // Tạo đơn hàng mới
        $orderValueObject = new OrderValueObject([
            'user_id' => $user->id,
            'total' => $this->calculateTotalPrice($cart), // Tính tổng giá trị đơn hàng
            'order_status' => 'Pending',
            'pay_type' => 'VNPAY',
            'order_id_ref' => Str::upper(Str::random(8)), // Mã đơn hàng tham chiếu
            'created_at' => $currentTime,
            'updated_at' => $currentTime,
        ]);
    
        // Tạo đơn hàng
        $order = $this->orderService->createOrder($orderValueObject);
    
        // Lưu thông tin chi tiết đơn hàng
        $this->saveOrderDetails($order, $cart, $currentTime);
    
        return $order;
    }
    
    private function calculateTotalPrice($cart)
    {
        $totalPrice = 0;
    
        foreach ($cart as $productId => $productData) {
            $product = Game::find($productId);
            if ($product) {
                $totalPrice += $product->price * $productData['quantity'];
            }
        }
    
        return $totalPrice;
    }
    
    private function saveOrderDetails($order, $cart, $currentTime)
    {
        foreach ($cart as $productId => $productData) {
            $product = Game::find($productId);
            if ($product) {
                $orderDetailValueObject = new OrderDetailValueObject([
                    'order_id' => $order->id,
                    'game_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'price' => $product->price,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime,
                ]);
    
                // Tạo chi tiết đơn hàng
                $this->orderDetailService->createOrderDetail($orderDetailValueObject);
    
                // Giảm số lượng key trong cơ sở dữ liệu
                $this->reduceKeys($product, $productData['quantity'], $currentTime);
            }
        }
    }
    
    private function reduceKeys($product, $quantity, $currentTime)
    {
        $keysToReduce = min($quantity, $product->availableKeys());
    
        for ($i = 0; $i < $keysToReduce; $i++) {
            $key = Key::where('game_id', $product->id)
                        ->where('is_expired', 0)
                        ->where('is_redeemed', 0)
                        ->first();
            if ($key) {
                $key->is_redeemed = 1;
                $key->updated_at = $currentTime;
                $key->save();
            }
        }
    }
    


    // public function payCart(Request $request)
    // {
    //     // Lấy thông tin người dùng đang đăng nhập
    //     $user = Auth::user();

    //     // Lấy thông tin giỏ hàng từ session
    //     $cart = $this->getCart();

    //     // Kiểm tra giỏ hàng có sản phẩm không
    //     if (empty($cart)) {
    //         return response()->json(['error' => 'Giỏ hàng của bạn đang trống.'], 400);
    //     }

    //     $currentTime = Carbon::now();

    //     // Tạo đơn hàng mới
    //     $orderValueObject = new OrderValueObject([
    //         'user_id' => $user->id,
    //         'total' => 0, // Sẽ cập nhật sau khi tính tổng giá trị đơn hàng
    //         'order_status' => 'Pending',
    //         'pay_type' => 'VNPAY',
    //         'order_id_ref' => Str::upper(Str::random(8)), // Mã đơn hàng tham chiếu
    //         'created_at' => $currentTime,
    //         'updated_at' => $currentTime,
    //     ]);

    //     // Tạo đơn hàng
    //     $order = $this->orderService->createOrder($orderValueObject);

    //     $totalPrice = 0;

    //     // Lưu thông tin chi tiết đơn hàng (order_details) và tính tổng giá trị đơn hàng
    //     foreach ($cart as $productId => $productData) {
    //         $product = Game::find($productId);
    //         if ($product) {
    //             $orderDetailValueObject = new OrderDetailValueObject([
    //                 'order_id' => $order->id,
    //                 'game_id' => $product->id,
    //                 'quantity' => $productData['quantity'],
    //                 'price' => $product->price,
    //                 'created_at' => $currentTime,
    //                 'updated_at' => $currentTime,
                    
    //             ]);

    //             // Tạo chi tiết đơn hàng
    //             $this->orderDetailService->createOrderDetail($orderDetailValueObject);

    //             // Tính tổng giá trị đơn hàng
    //             $totalPrice += $product->price * $productData['quantity'];

    //             // Giảm số lượng key trong cơ sở dữ liệu
    //             // Sử dụng phương thức availableKeys() để lấy số lượng keys có sẵn
    //             $keysToReduce = min($productData['quantity'], $product->availableKeys());

    //             for ($i = 0; $i < $keysToReduce; $i++) {
    //                 $key = Key::where('game_id', $product->id)
    //                             ->where('is_expired', 0)
    //                             ->where('is_redeemed', 0)
    //                             ->first();
    //                 if ($key) {
    //                     $key->is_redeemed = 1;
    //                     $key->updated_at = $currentTime;
    //                     $key->save();
    //                 }
    //             }
    //         }
    //     }

    //     // Cập nhật tổng giá trị đơn hàng
    //     $order->total = $totalPrice;
    //     $order->save();

    //     // Xóa giỏ hàng sau khi đã đặt hàng thành công
    //     $this->removeCartInCache();

    //     return response()->json(['message' => 'Đơn hàng của bạn đã được đặt thành công.'], 200);
    // }
}
