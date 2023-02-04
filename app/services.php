<?php
declare(strict_types=1);

use DI\ContainerBuilder;
return function (ContainerBuilder $containerBuilder) {
      //Setting the database connection
    $containerBuilder->addDefinitions([
        'settings' => [
            // Slim Settings
            'determineRouteBeforeAppMiddleware' => false,
            'displayErrorDetails' => true,
            'db' => [
                'driver' => 'mysql',
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'database' => $_ENV['DB_NAME'] ?? 'stocks',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? 'root',
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ]
        ],
        
        
    ]);
    $containerBuilder->addDefinitions([

        Swift_Mailer::class => function() {
            $host = $_ENV['MAILER_HOST'] ?? 'smtp.mailtrap.io';
            $port = intval($_ENV['MAILER_PORT']) ?? 465;
            $username = $_ENV['MAILER_USERNAME'] ?? 'test';
            $password = $_ENV['MAILER_PASSWORD'] ?? 'test';

            $transport = (new Swift_SmtpTransport($host, $port))
                ->setUsername($username)
                ->setPassword($password)
            ;

            return new Swift_Mailer($transport);
        },
        
    ]);
      //Adding the database connection
    $containerBuilder->addDefinitions([
        'db' => function ($container) {
            $capsule = new \Illuminate\Database\Capsule\Manager;
            $capsule->addConnection($container->get('settings')['db']);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            return $capsule;
        },
    ]); 
    //Passing users database table to Controller
    $containerBuilder->addDefinitions([
        App\UserController::class => function ($c) {
            $table = $c->get('db')->table('users');
            return new \App\UserController( $table);
        },
        
    ]);
    $containerBuilder->addDefinitions([
        App\StocksController::class => function ($c) {
            $table_users = $c->get('db')->table('users');
            $table_records = $c->get('db')->table('records');
            $mailer = $c->get(Swift_Mailer::class);
            return new \App\StocksController( $table_records,$table_users,$mailer);
        },
    ]);
    // Adding Log Monologer
    $containerBuilder->addDefinitions([
        'logger' => function($c) {
            $logger = new \Monolog\Logger('my_logger');
            $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
            $logger->pushHandler($file_handler);
            return $logger;
        }
    ]);
    // Adding jwt
    $containerBuilder->addDefinitions([
        'jwt' => function ($container) {
            return new StdClass;
        }
    ]);

};
