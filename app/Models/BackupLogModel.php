<?php

namespace App\Models;

use CodeIgniter\Model;

class BackupLogModel extends Model
{
    protected $table            = 'backup_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['backup_type', 'backup_file', 'backup_size', 'status', 'message', 'created_by'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = false;

    /**
     * Insert backup log safely with schema-compatible values.
     */
    public function insert($data = null, bool $returnID = true)
    {
        if (!is_array($data)) {
            return false;
        }

        $insertData = [
            'backup_type' => $this->normalizeBackupType($data['backup_type'] ?? 'Manual'),
            'backup_file' => (string) ($data['backup_file'] ?? ''),
            'backup_size' => isset($data['backup_size']) ? (int) $data['backup_size'] : null,
            'status' => ($data['status'] ?? 'Success') === 'Failed' ? 'Failed' : 'Success',
            'message' => isset($data['message']) ? (string) $data['message'] : null,
            'created_by' => isset($data['created_by']) ? (int) $data['created_by'] : null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $builder = $this->db->table($this->table);
        $result = $builder->insert($insertData);

        if (!$result) {
            return false;
        }

        return $returnID ? $this->db->insertID() : true;
    }
    
    /**
     * Get recent backups
     */
    public function getRecent($limit = 20)
    {
        return $this->orderBy('created_at', 'DESC')->limit($limit)->findAll();
    }
    
    /**
     * Get backups by type
     */
    public function getByType($type)
    {
        return $this->where('backup_type', $type)->orderBy('created_at', 'DESC')->findAll();
    }
    
    /**
     * Get backup statistics
     */
    public function getStats()
    {
        $stats = [
            'total' => $this->countAllResults(),
            'successful' => $this->where('status', 'Success')->countAllResults(),
            'failed' => $this->where('status', 'Failed')->countAllResults(),
            'total_size' => $this->selectSum('backup_size')->first()['backup_size'] ?? 0
        ];
        
        return $stats;
    }
    
    /**
     * Clean old backups
     */
    public function cleanOld($days = 30)
    {
        $date = date('Y-m-d H:i:s', strtotime("-$days days"));
        return $this->where('created_at <', $date)->delete();
    }

    private function normalizeBackupType($backupType)
    {
        return $backupType === 'Manual' ? 'Manual' : 'Automatic';
    }
}
