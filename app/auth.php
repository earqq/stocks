<?php

declare(strict_types=1);

use Slim\App;
use Slim\Exception\HttpUnauthorizedException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Tuupola\Middleware\HttpBasicAuthentication;

return function (App $app) {
    $username = $_ENV["ADMIN_USERNAME"] ?? 'root';
    $password = $_ENV["ADMIN_PASSWORD"] ?? 'secret';

     //Middleware
     $container = $app->getContainer();
     $app->add(new Tuupola\Middleware\JwtAuthentication([
        "path" => "/",
        "logger" => $container->get('logger'),
        "secret" => $_ENV['JWT_SECRET_KEY'],
        "rules" => [
            new \Tuupola\Middleware\JwtAuthentication\RequestPathRule([
                "path" => "/",
                "ignore" => ["/register", ]
            ]),
        ],
        "error" => function ($response) {
            return $response->withStatus(401);
        }
    ]));

    
  

};
