<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveFinishedProductIdFromProductionJobs extends Migration
{
    public function up()
    {
        $this->forge->dropForeignKey('production_jobs', 'production_jobs_finished_product_id_foreign');
        $this->forge->dropColumn('production_jobs', 'finished_product_id');
    }

    public function down()
    {
        $this->forge->addColumn('production_jobs', [
            'finished_product_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'customer_id',
            ],
        ]);
        $this->forge->addForeignKey('finished_product_id', 'products', 'id', 'SET NULL', 'CASCADE');
    }
}
