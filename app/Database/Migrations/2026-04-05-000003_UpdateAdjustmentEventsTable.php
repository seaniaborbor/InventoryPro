<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateAdjustmentEventsTable extends Migration
{
    public function up()
    {
        // Add related_production_job_id
        $this->forge->addColumn('adjustment_events', [
            'related_production_job_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'related_sale_id',
            ],
        ]);
        $this->forge->addForeignKey('related_production_job_id', 'production_jobs', 'id', 'SET NULL', 'CASCADE');

        // Remove columns not needed
        $this->forge->dropColumn('adjustment_events', 'related_purchase_id');
    }

    public function down()
    {
        $this->forge->dropForeignKey('adjustment_events', 'adjustment_events_related_production_job_id_foreign');
        $this->forge->dropColumn('adjustment_events', 'related_production_job_id');

        $this->forge->addColumn('adjustment_events', [
            'related_purchase_id' => [
                'type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true,
            ],
        ]);
    }
}
