<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryAdjustmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'product_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'adjustment_type' => [
                'type' => 'ENUM',
                'constraint' => ['Increase', 'Decrease'],
            ],
            'quantity' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'previous_quantity' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'new_quantity' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'reason' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('product_id');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('inventory_adjustments');
    }

    public function down()
    {
        $this->forge->dropTable('inventory_adjustments');
    }
}