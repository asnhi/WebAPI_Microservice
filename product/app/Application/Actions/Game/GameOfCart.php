<?php
declare(strict_types=1);

namespace App\Application\Actions\Game;

use Illuminate\Http\Request;
use App\Application\Requests\GameRequest;
use App\Application\Service\GameService;
use App\Domain\ValueObjects\GameValueObject;


class GameOfCart
{
    protected $builder;

    public function __construct(GameService $builder)
    {
        $this->builder = $builder;
    }

    public function addGameToCart(Request $request)
    {
        return $this->builder->addToCart($request);
    }

    public function showGameofCart()
    {
        return $this->builder->showCart();
    }

    public function removeGameOfCart(Request $request, $productId = null)
    {
        return $this->builder->removeFromCart($request, $productId);
    }

    public function updateGameOfCart(Request $request)
    {
        return $this->builder->updateCart($request);
    }
}