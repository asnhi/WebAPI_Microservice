<?php
declare(strict_types=1);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('api')->group(function () {
    $routes = glob(__DIR__ . "/api/*.php");
    foreach ($routes as $route) {
        require($route);
    }
});