<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdjustmentIndexes extends Migration
{
    public function up()
    {
        // Check if table exists
        if (!$this->db->tableExists('adjustment_events')) {
            return;
        }
        
        // Get existing indexes
        $indexes = $this->db->getIndexData('adjustment_events');
        $existingIndexes = array_keys($indexes);
        
        // Add indexes if they don't exist
        if (!in_array('idx_adj_sale', $existingIndexes)) {
            $this->forge->addKey('related_sale_id', false, false, 'idx_adj_sale');
        }
        
        if (!in_array('idx_adj_job', $existingIndexes)) {
            $this->forge->addKey('related_production_job_id', false, false, 'idx_adj_job');
        }
        
        if (!in_array('idx_adj_date', $existingIndexes)) {
            $this->forge->addKey('event_date', false, false, 'idx_adj_date');
        }
        
        if (!in_array('idx_adj_product', $existingIndexes)) {
            $this->forge->addKey('product_id', false, false, 'idx_adj_product');
        }
        
        if (!in_array('idx_adj_created', $existingIndexes)) {
            $this->forge->addKey('created_at', false, false, 'idx_adj_created');
        }
        
        // Process the indexes
        $this->forge->processIndexes('adjustment_events');
        
        echo "Indexes added to adjustment_events table successfully.\n";
    }

    public function down()
    {
        if (!$this->db->tableExists('adjustment_events')) {
            return;
        }
        
        // Drop indexes if they exist
        $this->forge->dropKey('adjustment_events', 'idx_adj_sale');
        $this->forge->dropKey('adjustment_events', 'idx_adj_job');
        $this->forge->dropKey('adjustment_events', 'idx_adj_date');
        $this->forge->dropKey('adjustment_events', 'idx_adj_product');
        $this->forge->dropKey('adjustment_events', 'idx_adj_created');
        
        echo "Indexes dropped from adjustment_events table successfully.\n";
    }
}