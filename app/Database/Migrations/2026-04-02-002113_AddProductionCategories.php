<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductionCategories extends Migration
{
    public function up()
    {
        // Create production_categories table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'category_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
            ],
            'description' => [
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
        $this->forge->createTable('production_categories');

        // Add production_category_id to production_jobs table
        $this->forge->addColumn('production_jobs', [
            'production_category_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'finished_product_id',
            ],
        ]);

        // Add foreign key
        $this->forge->addForeignKey('production_category_id', 'production_categories', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        // Remove foreign key and column
        $this->forge->dropForeignKey('production_jobs', 'production_jobs_production_category_id_foreign');
        $this->forge->dropColumn('production_jobs', 'production_category_id');

        // Drop table
        $this->forge->dropTable('production_categories');
    }
}
