<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ResetDatabase extends Seeder
{
    public function run()
    {
        // Get all tables
        $tables = $this->db->listTables();
        
        // Disable foreign key checks
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        
        // Drop all tables
        foreach ($tables as $table) {
            if ($table !== 'migrations') { // Don't drop migrations table
                $this->db->query("DROP TABLE IF EXISTS `{$table}`");
                echo "Dropped table: {$table}\n";
            }
        }
        
        // Re-enable foreign key checks
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        
        echo "\nAll tables dropped successfully!\n";
        echo "Now run: php spark migrate\n";
        echo "Then run: php spark db:seed DatabaseSeeder\n";
    }
}