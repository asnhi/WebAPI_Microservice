<?php

declare(strict_types=1);

namespace App\Application\Service;


use App\Domain\Entities\Game;
use App\Domain\Entities\Genre;
use App\Domain\Entities\Publisher;
use App\Domain\ValueObjects\GameValueObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Domain\Entities\Key;
use Carbon\Carbon;

class GameService
{
    public function findGameByID($id)
    {
        $game = Game::find($id);

        if ($game) {
            return response()->json($game);
        } else {
            return response()->json(['message' => 'Game not found'], 404);
        }
    }
    public function filterGamesByPrice($fromPrice)
    {
        $query = Game::query();
        if ($fromPrice) {
            $query->where('price', '>=', $fromPrice);
        }
        return $query->get();
    }
    
    public function newGame(GameValueObject $data)
    {
        // Sử dụng factory để tạo ra đối tượng Game từ dữ liệu đầu vào
        $game = Game::create($data->toArray());

        // Lưu game vào cơ sở dữ liệu
        $game->save();

        return $game;
    }
    
    public function findID($id)
    {
    
        return $game = Game::findOrFail($id);
    }

    // public function findGenreOfID($id)
    // {
    
    //     $game = Game::findOrFail($id);
    //     $genres = $game->genres->pluck('name');

    //     return $genres;
    // }


    public function searchGame($keyword)
    {
        return Game::where('name', 'like', '%' . $keyword . '%')->get();
    }

    //Trong ShowGame.php -> vì chỉ hiện in4 của Game đó thôi
    public function findPublisherOfGame($id)
    {
        return Publisher::where('id', $id)->get('name');
    }

    // Phương thức tùy chỉnh để lấy các trò chơi phổ biến
    public function popular()
    {
        // Tạo một truy vấn sử dụng model Game
        $query = Game::query();
    
        // Lấy các bản ghi có trường 'like' lớn hơn 300 và sắp xếp theo số lượng like giảm dần
        $popularGames = $query->where('like', '>', 300)->orderByDesc('like')->take(5)->get();
    
        return $popularGames;
    }
    
    
    public function topPopular()
    {
        // Tạo một truy vấn sử dụng model Game
        $query = Game::query();
    
        // Sắp xếp các kết quả theo số lượng like giảm dần
        $popularGame = $query->orderByDesc('like')->first();
    
        return $popularGame;
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

    

    public function updateCart(Request $request)
    {
        // Xử lý logic cập nhật giỏ hàng
        try {
            $id = $request->input('id');
            $quantity = $request->input('quantity');
    
            // Kiểm tra xem id và quantity có tồn tại không
            if ($id && $quantity) {
                // Lấy thông tin game từ ID
                $game = Game::findOrFail($id);
    
                // Kiểm tra số lượng key có đủ để cập nhật không
                $availableKeys = $game->keys()->where('is_redeemed', 0)->count();
                if ($availableKeys >= $quantity) {
                    // Lấy thông tin giỏ hàng từ cache
                    $cart = $this->getCart();
    
                    // Kiểm tra xem phần tử trong giỏ hàng có tồn tại không
                    if (isset($cart[$id])) {
                        $cart[$id]['quantity'] = $quantity;
                        $cart[$id]['totalPrice'] = $game->price * $cart[$id]['quantity'];
    
                        // Cập nhật giỏ hàng trong cache
                        $this->saveCartInCache($cart);
    
                        // Trả về phản hồi JSON thành công
                        return response()->json([
                            'success' => true, 
                            'message' => 'Cập nhật giỏ hàng thành công',
                            'cart' => $cart
                        ]);
                    } else {
                        // Phần tử không tồn tại trong giỏ hàng
                        return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
                    }
                } else {
                    // Trả về phản hồi JSON lỗi nếu số lượng key không đủ
                    return response()->json(['success' => false, 'message' => 'Đã hết hàng']);
                }
            }
        } catch (\Exception $ex) {
            // Trả về phản hồi JSON lỗi nếu có lỗi xảy ra
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi']);
        }
    }

    public function showCart()
    {
        try {
            // Lấy thông tin giỏ hàng từ cache
            $cart = $this->getCart();
    
            // Trả về phản hồi JSON với thông tin giỏ hàng
            return response()->json([
                'success' => true,
                'cart' => $cart
            ]);
        } catch (\Exception $ex) {
            // Trả về phản hồi JSON lỗi nếu có lỗi xảy ra
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi']);
        }
    }

    public function removeFromCart(Request $request, $productId = null)
    {
        // Lấy giỏ hàng từ cache
        $cart = $this->getCart();

        // Kiểm tra xem giỏ hàng có trống không
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng đã trống'
            ], 404);
        }

        if ($productId) {
            // Xóa sản phẩm cụ thể khỏi giỏ hàng
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                $this->saveCartInCache($cart);

                return response()->json([
                    'success' => true,
                    'message' => 'Xóa sản phẩm khỏi giỏ hàng thành công'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại trong giỏ hàng'
                ], 404);
            }
        } else {
            // Xóa toàn bộ giỏ hàng
            $this->removeCartInCache();

            return response()->json([
                'success' => true,
                'message' => 'Xóa giỏ hàng thành công'
            ]);
        }
    }

}
