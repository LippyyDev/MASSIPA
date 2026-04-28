<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TwoFaWhitelist extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => false,
                'comment'    => 'Supports IPv4 & IPv6',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'ip_address']);
        $this->forge->addKey('expires_at');
        $this->forge->createTable('two_fa_whitelist');
    }

    public function down()
    {
        $this->forge->dropTable('two_fa_whitelist');
    }
}
