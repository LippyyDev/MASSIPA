<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TwoFaSettings extends Migration
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
                'null'       => true,
                'comment'    => 'NULL = global setting; user_id = per-user setting',
            ],
            'setting_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'setting_value' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'default'    => '0',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'setting_key']);
        $this->forge->createTable('two_fa_settings');

        // Seed: 2FA global default nonaktif
        $this->db->table('two_fa_settings')->insert([
            'user_id'       => null,
            'setting_key'   => 'global_2fa_enabled',
            'setting_value' => '0',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('two_fa_settings');
    }
}
