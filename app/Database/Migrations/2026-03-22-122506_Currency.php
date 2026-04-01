<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemSettingsTable extends Migration
{
    public function up()
    {
        // System Settings table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'setting_key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
            ],
            'setting_value' => [
                'type' => 'TEXT',
            ],
            'setting_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'string',
            ],
            'group' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'general',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('system_settings');

        // Currency Rates table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'base_currency' => [
                'type' => 'VARCHAR',
                'constraint' => 3,
                'default' => 'USD',
            ],
            'target_currency' => [
                'type' => 'VARCHAR',
                'constraint' => 3,
            ],
            'rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,4',
            ],
            'date' => [
                'type' => 'DATE',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['base_currency', 'target_currency', 'date']);
        $this->forge->createTable('currency_rates');

        // Backup Logs table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'backup_type' => [
                'type' => 'ENUM',
                'constraint' => ['Manual', 'Automatic'],
            ],
            'backup_file' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'backup_size' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Success', 'Failed'],
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('backup_logs');
    }

    public function down()
    {
        $this->forge->dropTable('backup_logs');
        $this->forge->dropTable('currency_rates');
        $this->forge->dropTable('system_settings');
    }
}