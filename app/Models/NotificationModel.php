<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'title', 'message', 'type', 'link', 'is_read'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = false;

    /**
     * Create notification for user
     */
    public function createNotification($userId, $title, $message, $type = 'info', $link = null)
    {
        return $this->insert([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
            'is_read' => 0
        ]);
    }

    /**
     * Create notification for multiple users
     */
    public function createNotificationForUsers(array $userIds, $title, $message, $type = 'info', $link = null)
    {
        $data = [];
        foreach ($userIds as $userId) {
            $data[] = [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link' => $link,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        return $this->insertBatch($data);
    }

    /**
     * Get unread notifications for user
     */
    public function getUnread($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get all notifications for user
     */
    public function getByUser($userId, $limit = 50)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId = null)
    {
        $builder = $this->where('id', $notificationId);
        
        if ($userId) {
            $builder->where('user_id', $userId);
        }
        
        return $builder->set(['is_read' => 1])->update();
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead($userId)
    {
        return $this->where('user_id', $userId)
                    ->set(['is_read' => 1])
                    ->update();
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    /**
     * Delete old notifications
     */
    public function deleteOld($days = 30)
    {
        $date = date('Y-m-d H:i:s', strtotime("-$days days"));
        return $this->where('created_at <', $date)->delete();
    }
}