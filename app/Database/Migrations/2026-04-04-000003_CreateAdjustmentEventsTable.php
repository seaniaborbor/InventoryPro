<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdjustmentEventsTable extends Migration
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
            'product_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'event_type' => ['type' => 'ENUM', 'constraint' => ['Damage', 'Refund', 'Theft', 'Return', 'Other']],
            'quantity' => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'unit_cost' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'total_value' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'currency' => ['type' => 'ENUM', 'constraint' => ['LRD', 'USD'], 'default' => 'LRD'],
            'reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'adjust_stock' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'event_date' => ['type' => 'DATETIME'],
            'related_sale_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'related_purchase_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'customer_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'updated_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('product_id');
        $this->forge->addKey('event_type');
        $this->forge->addKey('event_date');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('adjustment_events');
    }

    public function down()
    {
        $this->forge->dropTable('adjustment_events');
    }
}
