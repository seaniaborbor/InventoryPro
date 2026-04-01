<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login')->with('error', 'Please login to access this page');
        }
        
        // Check if user is active
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find(session()->get('user_id'));
        
        if (!$user || !$user['is_active']) {
            session()->destroy();
            return redirect()->to('/auth/login')->with('error', 'Your account has been deactivated');
        }
        
        // Check role if specified
        if (!empty($arguments)) {
            $role = $arguments[0];
            $roleModel = new \App\Models\RoleModel();
            $userRole = $roleModel->find(session()->get('role_id'));
            
            if ($userRole['role_name'] !== $role) {
                return redirect()->to('/dashboard')->with('error', 'Access denied. Insufficient privileges.');
            }
        }
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}