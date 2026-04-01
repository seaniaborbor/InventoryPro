<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRememberTokenFieldsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'remember_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'reset_token_expiry',
            ],
            'remember_token_expiry' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'remember_token',
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['remember_token', 'remember_token_expiry']);
    }
}
