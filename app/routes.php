<?php

declare(strict_types=1);

use App\UserController;
use App\StocksController;
use Slim\App;

return function (App $app) {
    // unprotected routes
    $app->post('/register', UserController::class . ':register');
    
    // protected routes
    $app->get('/stock', StocksController::class . ':stockValue');
    $app->get('/history', StocksController::class . ':records');

};
