<?php

declare(strict_types=1);

namespace Migrations;

use Phoenix\Migration\AbstractMigration;

final class MigrateUsers extends AbstractMigration
{
    protected function up(): void
    {
        
        $this->execute('CREATE TABLE `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(200) NOT NULL,
            `email` varchar(200) NOT NULL,
            `token` varchar(5000) NOT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
        );
    }

    
    protected function down(): void
    {
        $this->table('users')
        ->drop();
    }
}
