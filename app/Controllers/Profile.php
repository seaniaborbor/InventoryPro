<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AuditLogModel;
use App\Libraries\TotpService;

class Profile extends BaseController
{
    protected $userModel;
    protected $auditLogModel;
    protected $totpService;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->auditLogModel = new AuditLogModel();
        $this->totpService = new TotpService();
        
        // Check authentication
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }
    
    /**
     * Show profile page
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);
        
        $data = [
            'title' => 'My Profile',
            'user' => $user,
            'activePage' => 'profile'
        ];
        
        return view('profile/index', $data);
    }
    
    /**
     * Update profile
     */
    public function update()
    {
        $userId = session()->get('user_id');
        
        $rules = [
            'full_name' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $userId . ']',
            'phone' => 'permit_empty|max_length[50]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone')
        ];
        
        // Get old data for audit
        $oldData = $this->userModel->find($userId);
        
        if ($this->userModel->update($userId, $data)) {
            // Update session
            session()->set('full_name', $data['full_name']);
            session()->set('email', $data['email']);
            
            // Log audit
            $this->auditLogModel->log(
                $userId,
                'profile_update',
                'User',
                $userId,
                $oldData,
                $data
            );
            
            return redirect()->back()->with('success', 'Profile updated successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to update profile');
        }
    }
    
    /**
     * Change password page
     */
    public function changePassword()
    {
        $data = [
            'title' => 'Change Password',
            'activePage' => 'profile'
        ];
        
        return view('profile/change_password', $data);
    }
    
    /**
     * Update password
     */
    public function updatePassword()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);
        
        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()->with('error', 'Current password is incorrect');
        }
        
        // Check new password length
        if (strlen($newPassword) < 6) {
            return redirect()->back()->with('error', 'New password must be at least 6 characters');
        }
        
        // Check password confirmation
        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('error', 'New passwords do not match');
        }
        
        // Update password
        $data = [
            'password' => $newPassword,
            'remember_token' => null,
            'remember_token_expiry' => null,
        ];
        
        if ($this->userModel->update($userId, $data)) {
            // Log audit
            $this->auditLogModel->log(
                $userId,
                'password_change',
                'User',
                $userId
            );
            
            return redirect()->back()->with('success', 'Password changed successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to change password');
        }
    }
    
    /**
     * Setup 2FA
     */
    public function setup2fa()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);
        
        if (!$user['two_factor_secret']) {
            $secret = $this->totpService->generateSecret();
            
            $this->userModel->update($userId, ['two_factor_secret' => $secret]);
            $user['two_factor_secret'] = $secret;
        }

        $otpUri = $this->totpService->getProvisioningUri($user['email'], $user['two_factor_secret'], 'Innovative Graphics');
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode($otpUri);
        
        $data = [
            'title' => 'Setup Two-Factor Authentication',
            'user' => $user,
            'qrCodeUrl' => $qrCodeUrl,
            'otpUri' => $otpUri,
            'activePage' => 'profile'
        ];
        
        return view('profile/setup_2fa', $data);
    }
    
    /**
     * Enable 2FA
     */
    public function enable2fa()
    {
        $userId = session()->get('user_id');
        $code = $this->request->getPost('code');
        $user = $this->userModel->find($userId);

        if ($user && $user['two_factor_secret'] && $this->totpService->verifyCode($user['two_factor_secret'], $code)) {
            $this->userModel->update($userId, ['two_factor_enabled' => 1]);
            
            // Log audit
            $this->auditLogModel->log(
                $userId,
                '2fa_enabled',
                'User',
                $userId
            );
            
            return redirect()->to('/profile')->with('success', 'Two-factor authentication enabled successfully');
        } else {
            return redirect()->back()->with('error', 'Invalid verification code');
        }
    }
    
    /**
     * Disable 2FA
     */
    public function disable2fa()
    {
        $userId = session()->get('user_id');
        
        $this->userModel->update($userId, [
            'two_factor_enabled' => 0,
            'two_factor_secret' => null
        ]);
        
        // Log audit
        $this->auditLogModel->log(
            $userId,
            '2fa_disabled',
            'User',
            $userId
        );
        
        return redirect()->to('/profile')->with('success', 'Two-factor authentication disabled');
    }
}
