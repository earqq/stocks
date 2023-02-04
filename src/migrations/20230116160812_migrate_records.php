<?php

declare(strict_types=1);

namespace Migrations;

use Phoenix\Migration\AbstractMigration;

final class MigrateRecords extends AbstractMigration
{
    protected function up(): void
    {
        $this->execute('CREATE TABLE `records` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `symbol` varchar(20) NOT NULL,
            `date` datetime,
            `open` float,
            `high` float,
            `low` float,
            `close` float,
            `volume` float,
            `name` varchar(200) NOT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`user_id`) REFERENCES users(`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
        );
    }

    
    protected function down(): void
    {
        $this->table('records')
        ->drop();
    }
}
