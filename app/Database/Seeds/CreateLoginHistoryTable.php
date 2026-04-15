<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CreateLoginHistoryTable extends Seeder
{
    public function run()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `login_history` (
            `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id`          INT(11) UNSIGNED NOT NULL,
            `username`         VARCHAR(100)     DEFAULT NULL,
            `ip_address`       VARCHAR(45)      DEFAULT NULL,
            `user_agent`       TEXT             DEFAULT NULL,
            `device_type`      VARCHAR(20)      DEFAULT NULL,
            `device_os`        VARCHAR(100)     DEFAULT NULL,
            `browser`          VARCHAR(100)     DEFAULT NULL,
            `location_country` VARCHAR(100)     DEFAULT NULL,
            `location_region`  VARCHAR(100)     DEFAULT NULL,
            `location_city`    VARCHAR(100)     DEFAULT NULL,
            `created_at`       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user_id`    (`user_id`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

        $this->db->query($sql);
        echo 'Tabel login_history berhasil dibuat!' . PHP_EOL;
    }
}
