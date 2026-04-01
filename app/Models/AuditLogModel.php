<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'action', 'entity', 'entity_id', 
        'old_data', 'new_data', 'ip_address', 'user_agent'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = false;

    /**
     * Log an action - SIMPLIFIED VERSION
     */
    public function log($userId, $action, $entity, $entityId = null, $oldData = null, $newData = null, $ipAddress = null, $userAgent = null)
    {
        // Build data array with only string keys
        $data = [];
        
        // Add each field individually
        $data['user_id'] = $userId;
        $data['action'] = (string) $action;
        $data['entity'] = (string) $entity;
        
        if ($entityId !== null) {
            $data['entity_id'] = (string) $entityId;
        }
        
        if ($ipAddress !== null) {
            $data['ip_address'] = $ipAddress;
        } else {
            $data['ip_address'] = service('request')->getIPAddress();
        }
        
        if ($userAgent !== null) {
            $data['user_agent'] = $userAgent;
        } else {
            $data['user_agent'] = service('request')->getUserAgent()->getAgentString();
        }
        
        if ($oldData !== null) {
            $data['old_data'] = is_array($oldData) ? json_encode($oldData) : (string) $oldData;
        }
        
        if ($newData !== null) {
            $data['new_data'] = is_array($newData) ? json_encode($newData) : (string) $newData;
        }
        
        // Direct database insert bypassing model validation
        $db = \Config\Database::connect();
        $builder = $db->table($this->table);
        
        try {
            return $builder->insert($data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to insert audit log: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get logs by user
     */
    public function getByUser($userId, $limit = 100)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get activity feed for a specific user profile.
     */
    public function getActivityForUserProfile($userId, $limit = 100)
    {
        $actorLogs = $this->select('audit_logs.*, users.full_name AS actor_name, users.username AS actor_username')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->where('audit_logs.user_id', $userId)
            ->orderBy('audit_logs.created_at', 'DESC')
            ->findAll($limit);

        $entityLogs = $this->select('audit_logs.*, users.full_name AS actor_name, users.username AS actor_username')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->where('audit_logs.entity', 'User')
            ->where('audit_logs.entity_id', (string) $userId)
            ->orderBy('audit_logs.created_at', 'DESC')
            ->findAll($limit);

        $combinedLogs = array_merge($actorLogs, $entityLogs);

        usort($combinedLogs, static function ($first, $second) {
            return strtotime($second['created_at']) <=> strtotime($first['created_at']);
        });

        $uniqueLogs = [];
        $seenIds = [];

        foreach ($combinedLogs as $log) {
            $key = $log['id'] ?? md5(json_encode($log));
            if (isset($seenIds[$key])) {
                continue;
            }

            $seenIds[$key] = true;
            $uniqueLogs[] = $log;

            if (count($uniqueLogs) >= $limit) {
                break;
            }
        }

        return $uniqueLogs;
    }

    /**
     * Get recent logs
     */
    public function getRecent($limit = 50)
    {
        return $this->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
