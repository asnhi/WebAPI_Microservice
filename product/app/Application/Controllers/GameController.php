<?php
declare(strict_types=1);

namespace App\Application\Controllers;


use Illuminate\Http\Request;
use App\Application\Service\GameService;
use App\Application\Requests\GameRequest;
use App\Application\Actions\Game\ShowGame;
use App\Application\Actions\Game\CreateGame;
use App\Application\Actions\Game\DeleteGame;
use App\Application\Actions\Game\GameOfCart;

class GameController extends Controller
{
    
    private $showGame;
    private $createGame;
    private $deleteGame;
    private $gameService;
    private $gameOfCart;
    

    public function __construct(GameOfCart $gameOfCart,ShowGame $showGame, CreateGame $createGame, DeleteGame $deleteGame, GameService $gameService)
    {
        $this->showGame = $showGame;
        $this->createGame = $createGame;
        $this->deleteGame = $deleteGame;
        $this->gameService = $gameService;
        $this->gameOfCart = $gameOfCart;
    }


    // public function showGenreOfGameID(Request $request)
    // {
    //     $id = (int) $request->input('id');

    //     return $this->showGame->showGenreOfGame($id);
    // }
    
/**
 * @OA\Get(
 *     path="/api/game/search",
 *     tags={"Game"},
 *     description="Tìm kiếm trò chơi theo từ khóa hoặc giá từ một mức giá cụ thể",
 *     @OA\Parameter(
 *         name="keyword",
 *         in="query",
 *         required=false,
 *         description="Từ khóa để tìm kiếm trò chơi",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="fromPrice",
 *         in="query",
 *         required=false,
 *         description="Giá tối thiểu để tìm kiếm trò chơi",
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Danh sách trò chơi được tìm thấy",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Game")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Không tìm thấy trò chơi nào"
 *     )
 * )
 */
/**
 * @OA\Schema(
 *     schema="Game",
 *     required={"id", "name", "description", "price", "image", "publisher_id", "like", "status"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Tên của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Mô tả của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="double",
 *         description="Giá của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         description="Đường dẫn hình ảnh của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="publisher_id",
 *         type="integer",
 *         description="ID của nhà xuất bản",
 *     ),
 *     @OA\Property(
 *         property="like",
 *         type="integer",
 *         description="Số lượt thích của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Trạng thái của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Thời điểm tạo bản ghi"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Thời điểm cập nhật bản ghi"
 *     )
 * )
 */

    public function showSearch(Request $request)
    {
        $keyword = $request->query('keyword');
        $fromPrice = (int) $request->query('fromPrice');
    
        if ($keyword !== null && $keyword !== '') {
            return $this->showGame->showResultSearch($keyword); // Truyền giá trị keyword
        } elseif ($fromPrice > 0) {
            return $this->showGame->searchGameByPrice($fromPrice); // Truyền giá trị fromPrice
        }
    }
    

    /**
     * @OA\Get(
     *     path="/api/game/favorate",
     *     tags={"Game"},
     *     description="Trả về top 5 các game được yêu thích",
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Missing Data"
     *     )
     * )
     */

    public function showFavorate()
    {
        return $this->showGame->showFavorate();
    }


/**
 * @OA\Schema(
 *     schema="GameRequest",
 *     required={"name", "description", "price", "image", "publisher_id", "like", "status"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Tên của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Mô tả của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="double",
 *         description="Giá của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         description="Đường dẫn hình ảnh của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="publisher_id",
 *         type="integer",
 *         description="ID của nhà xuất bản",
 *     ),
 *     @OA\Property(
 *         property="like",
 *         type="integer",
 *         description="Số lượt thích của trò chơi"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Trạng thái của trò chơi"
 *     )
 * )
 */

/**
 * @OA\Post(
 *     path="/api/game",
 *     tags={"Game"},
 *     description="Tạo mới một trò chơi",
 *     @OA\RequestBody(
 *         required=true,
 *         description="Dữ liệu trò chơi cần tạo",
 *         @OA\JsonContent(ref="#/components/schemas/GameRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Trò chơi đã được tạo thành công",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Thông báo thành công"
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/Game"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Dữ liệu không hợp lệ"
 *     )
 * )
 */
    public function createGame(GameRequest $request)
    {
        // Validate request data using GameRequest
      
        // Gọi phương thức handle của CreateGame và truyền dữ liệu đã xác thực và instance của GameService
        return $this->createGame->handle($request, $this->gameService);
    }
    
/**
 * @OA\Delete(
 *     path="/api/game/{id}",
 *     tags={"Game"},
 *     description="Xóa một trò chơi",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID của trò chơi cần xóa",
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Trò chơi đã được xóa thành công",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Thông báo thành công"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Không tìm thấy trò chơi với ID đã cung cấp"
 *     )
 * )
 */
    public function deleteGame(GameService $gameService, Request $request)
    {
        // Không cần validate dữ liệu ở đây vì DeleteGame không cần dữ liệu từ request
        
        // Gọi phương thức handle của DeleteGame và truyền instance của GameService và id
        return $this->deleteGame->handle($gameService, (int) $request->id);
    }

    public function showGameByID(Request $request)
    {
        return $this->showGame->findGame((int) $request->id);
    }

    public function addToCart(Request $request)
    {
        return $this->gameOfCart->addGameToCart($request);
    }

    /**
     * @OA\Put(
     *     path="/api/cart/update",
     *     tags={"Cart"},
     *     summary="Cập nhật giỏ hàng với ID trò chơi",
     *     description="Cập nhật sản phẩm trong giỏ hàng",
     *     security={{"bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Thông tin cập nhật trò chơi trong giỏ hàng",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 description="ID của trò chơi"
     *             ),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 description="Số lượng"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đã cập nhật số lượng"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ"
     *     )
     * )
     */
    public function updateCart(Request $request)
    {
        return $this->gameOfCart->updateGameOfCart($request);
    }


    /**
     * @OA\Get(
     *     path="/api/cart",
     *     tags={"Cart"},
     *     summary="Hiển thị giỏ hàng",
     *     description="Hiển thị giỏ hàng hiện tại",
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin giỏ hàng hiện tại",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     description="ID của trò chơi"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="Tên của trò chơi"
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="number",
     *                     description="Giá"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     description="Hình ảnh"
     *                 ),
     *                 @OA\Property(
     *                     property="quantity",
     *                     type="integer",
     *                     description="Số lượng"
     *                 ),
     *                 @OA\Property(
     *                     property="total_price",
     *                     type="number",
     *                     description="Tổng giá khi đã nhân với số lượng"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ"
     *     )
     * )
     */
    public function showCart()
    {
        return $this->gameOfCart->showGameofCart();
    }

        /**
     * @OA\Delete(
     *     path="/api/cart/remove/{productId}",
     *     tags={"Cart"},
     *     summary="Xóa trò chơi khỏi giỏ hàng (Xóa hết & xóa theo ID)",
     *     description="Xóa sản phẩm khỏi giỏ hàng",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=false,
     *         description="ID của trò chơi cần xóa",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trò chơi đã được xóa khỏi giỏ hàng"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ"
     *     )
     * )
     */
    public function removeFromCart(Request $request, $productId = null)
    {
        return $this->gameOfCart->removeGameOfCart($request, $productId);
    }
    
}
