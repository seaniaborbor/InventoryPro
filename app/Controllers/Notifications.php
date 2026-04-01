<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notifications extends BaseController
{
    protected $notificationModel;
    
    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }
    
    public function index()
    {
        $userId = session()->get('user_id');
        $notifications = $this->notificationModel->getByUser($userId, 100);
        
        $data = [
            'title' => 'Notifications',
            'notifications' => $notifications,
            'activePage' => 'notifications'
        ];
        
        return view('notifications/index', $data);
    }
    
    public function getUnread()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }
        
        $userId = session()->get('user_id');
        $notifications = $this->notificationModel->getUnread($userId);
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'count' => count($notifications),
                'notifications' => $notifications
            ]
        ]);
    }
    
    public function markRead()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }
        
        $id = $this->request->getPost('id');
        $userId = session()->get('user_id');
        
        if ($this->notificationModel->markAsRead($id, $userId)) {
            return $this->response->setJSON(['status' => 'success']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to mark as read']);
    }
}