<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingColumnsToProductionJobs extends Migration
{
    public function up()
    {
        // Add missing columns to production_jobs table
        $fields = [
            'customer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'job_name',
            ],
            'production_category_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'production_date',
            ],
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
        ];
        
        $this->forge->addColumn('production_jobs', $fields);
        
        // Add foreign keys
        $this->db->query('ALTER TABLE `production_jobs` ADD CONSTRAINT `production_jobs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `production_jobs` ADD CONSTRAINT `production_jobs_production_category_id_foreign` FOREIGN KEY (`production_category_id`) REFERENCES `production_categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        // Drop foreign keys first
        $this->db->query('ALTER TABLE `production_jobs` DROP FOREIGN KEY `production_jobs_customer_id_foreign`');
        $this->db->query('ALTER TABLE `production_jobs` DROP FOREIGN KEY `production_jobs_production_category_id_foreign`');
        
        // Drop columns
        $this->forge->dropColumn('production_jobs', 'customer_id');
        $this->forge->dropColumn('production_jobs', 'production_category_id');
        $this->forge->dropColumn('production_jobs', 'payment_status');
        $this->forge->dropColumn('production_jobs', 'amount_paid');
    }
}