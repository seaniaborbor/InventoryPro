<?php

namespace App\Controllers;

class Settings extends BaseController
{
    public function __construct()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }
    
    public function setCurrency()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }
        
        $currency = $this->request->getPost('currency');
        
        if (in_array($currency, ['LRD', 'USD'])) {
            session()->set('display_currency', $currency);
            return $this->response->setJSON(['status' => 'success']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid currency']);
    }
}