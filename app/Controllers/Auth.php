<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AuditLogModel;
use App\Libraries\TotpService;

class Auth extends BaseController
{
    protected $userModel;
    protected $auditLogModel;
    protected $session;
    protected $totpService;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->auditLogModel = new AuditLogModel();
        $this->session = \Config\Services::session();
        $this->totpService = new TotpService();
        
        // Load helper functions
        helper('cookie');
    }
    
    /**
     * Show login page
     */
    public function login()
    {
        // If already logged in, redirect to dashboard
        if ($this->session->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        
        // Check for remember me cookie
        $rememberToken = get_cookie('remember_token');
        if ($rememberToken) {
            $user = $this->userModel->getUserByRememberToken($rememberToken);
            if ($user) {
                $this->completeLogin($user, false, true);
                return redirect()->to('/dashboard');
            }
        }
        
        $data = [
            'title' => 'Login',
            'validation' => \Config\Services::validation()
        ];
        
        return view('auth/login', $data);
    }
    
    /**
     * Authenticate user
     */
    public function authenticate()
    {
        // Validation rules
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Please enter username and password');
        }
        
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $remember = (bool) $this->request->getPost('remember');
        
        // Authenticate user
        $user = $this->userModel->authenticate($username, $password);
        
        if (!$user) {
            // Log failed login attempt
            $this->auditLogModel->log(
                null,
                'login_failed',
                'User',
                null,
                null,
                ['username' => $username, 'reason' => 'Invalid credentials', 'ip' => $this->request->getIPAddress()],
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString()
            );
            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Invalid username/email or password');
        }
        
        // Check if user is active
        if (!$user['is_active']) {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Your account is deactivated. Please contact administrator.');
        }
        
        // Check for 2FA if enabled
        if ($user['two_factor_enabled']) {
            $this->session->set('2fa_user_id', $user['id']);
            $this->session->set('2fa_remember', $remember);
            return redirect()->to('/auth/verify-2fa');
        }

        $this->completeLogin($user, $remember);

        return redirect()->to('/dashboard')->with('success', 'Welcome back, ' . $user['full_name'] . '!');
    }
    
    /**
     * Set user session data
     */
    private function setUserSession($user)
    {
        $sessionData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role_id' => $user['role_id'],
            'isLoggedIn' => true,
            'display_currency' => 'LRD'
        ];
        
        $this->session->set($sessionData);
    }
    
    /**
     * Verify 2FA
     */
    public function verify2fa()
    {
        $userId = $this->session->get('2fa_user_id');
        
        if (!$userId) {
            return redirect()->to('/auth/login');
        }
        
        $data = [
            'title' => 'Two-Factor Authentication'
        ];
        
        return view('auth/verify_2fa', $data);
    }
    
    /**
     * Verify 2FA code
     */
    public function verify2faCode()
    {
        $userId = $this->session->get('2fa_user_id');
        
        if (!$userId) {
            return redirect()->to('/auth/login');
        }
        
        $code = $this->request->getPost('code');
        $user = $this->userModel->find($userId);
        $remember = (bool) $this->session->get('2fa_remember');
        
        if ($user && $user['two_factor_secret'] && $this->totpService->verifyCode($user['two_factor_secret'], $code)) {
            $this->session->remove(['2fa_user_id', '2fa_remember']);
            $this->completeLogin($user, $remember);

            return redirect()->to('/dashboard')->with('success', 'Welcome back, ' . $user['full_name'] . '!');
        } else {
            return redirect()->back()->with('error', 'Invalid verification code');
        }
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        $userId = $this->session->get('user_id');
        
        if ($userId) {
            $this->userModel->clearRememberToken($userId);
            // Log logout
            $this->auditLogModel->log(
                $userId,
                'logout',
                'User',
                $userId,
                null,
                ['ip' => $this->request->getIPAddress()],
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString()
            );
        }
        
        // Destroy session
        $this->session->destroy();
        
        // Delete remember me cookie
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        
        return redirect()->to('/auth/login')->with('success', 'You have been logged out successfully');
    }
    
    /**
     * Forgot password page
     */
    public function forgotPassword()
    {
        $data = [
            'title' => 'Forgot Password'
        ];
        
        return view('auth/forgot_password', $data);
    }
    
    /**
     * Send password reset link
     */
    public function sendResetLink()
    {
        $email = $this->request->getPost('email');
        
        if (!$email) {
            return redirect()->back()->with('error', 'Email address is required');
        }
        
        $user = $this->userModel->where('email', $email)->first();
        
        if (!$user) {
            return redirect()->back()->with('error', 'Email address not found');
        }
        
        // Generate reset token
        $token = $this->userModel->setResetToken($email);
        if (!$token) {
            return redirect()->back()->with('error', 'Unable to generate reset link. Please try again.');
        }
        
        // Send email (using CodeIgniter's email library)
        $emailService = \Config\Services::email();
        $resetLink = base_url('auth/reset-password/' . $token);
        
        $emailService->setTo($email);
        $emailService->setFrom(config('Email')->fromEmail, config('Email')->fromName);
        $emailService->setSubject('Password Reset Request');
        
        $message = "Hello " . $user['full_name'] . ",\n\n";
        $message .= "Click the link below to reset your password:\n";
        $message .= $resetLink . "\n\n";
        $message .= "This link will expire in 1 hour.\n\n";
        $message .= "If you did not request this, please ignore this email.\n\n";
        $message .= "Regards,\nInnovative Graphics";
        
        $emailService->setMessage($message);
        
        if ($emailService->send()) {
            $this->auditLogModel->log(
                $user['id'],
                'password_reset_link_sent',
                'User',
                $user['id'],
                null,
                ['email' => $email]
            );
            return redirect()->back()->with('success', 'Password reset link has been sent to your email');
        } else {
            log_message('error', 'Password reset email failed: ' . $emailService->printDebugger(['headers']));
            return redirect()->back()->with('error', 'Failed to send email. Please try again.');
        }
    }
    
    /**
     * Reset password page
     */
    public function resetPassword($token)
    {
        $user = $this->userModel->where('reset_token', $token)
                                ->where('reset_token_expiry >', date('Y-m-d H:i:s'))
                                ->first();
        
        if (!$user) {
            return redirect()->to('/auth/login')->with('error', 'Invalid or expired reset token');
        }
        
        $data = [
            'title' => 'Reset Password',
            'token' => $token
        ];
        
        return view('auth/reset_password', $data);
    }
    
    /**
     * Update password
     */
    public function updatePassword()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');
        
        if (empty($password) || empty($confirmPassword)) {
            return redirect()->back()->with('error', 'All fields are required');
        }
        
        if ($password !== $confirmPassword) {
            return redirect()->back()->with('error', 'Passwords do not match');
        }
        
        if (strlen($password) < 6) {
            return redirect()->back()->with('error', 'Password must be at least 6 characters');
        }
        
        $user = $this->userModel->where('reset_token', $token)
                                ->where('reset_token_expiry >', date('Y-m-d H:i:s'))
                                ->first();
        
        if (!$user) {
            return redirect()->to('/auth/login')->with('error', 'Invalid or expired reset token');
        }
        
        // Update password
        $this->userModel->update($user['id'], [
            'password' => $password,
            'reset_token' => null,
            'reset_token_expiry' => null,
            'remember_token' => null,
            'remember_token_expiry' => null,
        ]);
        
        // Log password change
        $this->auditLogModel->log(
            $user['id'],
            'password_reset',
            'User',
            $user['id'],
            null,
            ['ip' => $this->request->getIPAddress()],
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString()
        );
        
        return redirect()->to('/auth/login')->with('success', 'Password has been reset successfully. Please login with your new password.');
    }

    private function completeLogin(array $user, bool $remember = false, bool $preserveRemember = false): void
    {
        $this->setUserSession($user);
        $this->userModel->updateLastLogin($user['id'], $this->request->getIPAddress());

        $this->auditLogModel->log(
            $user['id'],
            'login_success',
            'User',
            $user['id'],
            null,
            ['ip' => $this->request->getIPAddress()],
            $this->request->getIPAddress(),
            $this->request->getUserAgent()->getAgentString()
        );

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
            $this->userModel->setRememberToken($user['id'], $token, $expiry);
            setcookie('remember_token', $token, [
                'expires' => time() + (60 * 60 * 24 * 30),
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } elseif (!$preserveRemember) {
            $this->userModel->clearRememberToken($user['id']);
            setcookie('remember_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }
}
