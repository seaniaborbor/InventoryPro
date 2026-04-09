<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductionJobItemsTable extends Migration
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
            'production_job_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'quantity_required' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'quantity_used' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'unit_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'total_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('production_job_items');

        // Add foreign keys
        $this->forge->addForeignKey('production_job_id', 'production_jobs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'products', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        // Drop foreign keys first
        $this->forge->dropForeignKey('production_job_items', 'production_job_items_production_job_id_foreign');
        $this->forge->dropForeignKey('production_job_items', 'production_job_items_item_id_foreign');

        // Drop table
        $this->forge->dropTable('production_job_items');
    }
}