<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNotesToStockMovements extends Migration
{
    public function up()
    {
        // Check if column exists before adding
        if (!$this->db->fieldExists('notes', 'stock_movements')) {
            $this->forge->addColumn('stock_movements', [
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'total_value',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('notes', 'stock_movements')) {
            $this->forge->dropColumn('stock_movements', 'notes');
        }
    }
}