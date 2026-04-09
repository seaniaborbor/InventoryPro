<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentFieldsToProductionJobs extends Migration
{
    public function up()
    {
        // Add payment fields to production_jobs table
        $fields = [
            'payment_status' => [
                'type' => 'ENUM',
                'constraint' => ['Unpaid', 'Partially Paid', 'Paid'],
                'default' => 'Unpaid',
                'after' => 'status',
            ],
            'amount_paid' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
                'after' => 'payment_status',
            ],
            'payment_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'amount_paid',
            ],
        ];

        $this->forge->addColumn('production_jobs', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('production_jobs', 'payment_date');
        $this->forge->dropColumn('production_jobs', 'amount_paid');
        $this->forge->dropColumn('production_jobs', 'payment_status');
    }
}