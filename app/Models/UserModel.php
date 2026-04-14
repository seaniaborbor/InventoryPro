<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'username', 'email', 'password', 'full_name', 'phone', 
        'role_id', 'is_active', 'last_login', 'last_ip',
        'two_factor_secret', 'two_factor_enabled', 'reset_token', 'reset_token_expiry',
        'remember_token', 'remember_token_expiry'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation - FIXED: password is NOT required on update
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username,id,{id}]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'permit_empty|min_length[6]',  // CHANGED: permit_empty instead of required
        'full_name' => 'required|min_length[3]|max_length[255]',
        'phone' => 'permit_empty|max_length[50]',
        'role_id' => 'required|integer|is_not_unique[roles.id]'
    ];

    protected $validationMessages = [
        'username' => [
            'required' => 'Username is required',
            'is_unique' => 'This username already exists'
        ],
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'Please provide a valid email address',
            'is_unique' => 'This email is already registered'
        ],
        'password' => [
            'min_length' => 'Password must be at least 6 characters'
        ]
    ];

    /**
     * Hash password before insert/update
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } elseif (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        return $data;
    }

    /**
     * Insert user with hashed password
     */
    public function insert($data = null, bool $returnID = true)
    {
        if (is_array($data)) {
            $data = $this->hashPassword($data);
        }
        return parent::insert($data, $returnID);
    }

    /**
     * Update user with hashed password if changed - FIXED
     */
    public function update($id = null, $data = null): bool
    {
        if (is_array($data)) {
            // If password is provided and not empty, hash it
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } 
            // If password is empty or not set, remove it from update data
            elseif (isset($data['password']) && empty($data['password'])) {
                unset($data['password']);
            }
        }
        return parent::update($id, $data);
    }

    /**
     * Get user with role details
     */
    public function getUserWithRole($userId)
    {
        $user = $this->find($userId);
        if (!$user) {
            return null;
        }
        
        $roleModel = new RoleModel();
        $user['role'] = $roleModel->find($user['role_id']);
        
        return $user;
    }

    /**
     * Get all users with role names
     */
    public function getAllUsersWithRoles()
    {
        return $this->select('users.*, roles.role_name')
                    ->join('roles', 'roles.id = users.role_id')
                    ->orderBy('users.id', 'DESC')
                    ->findAll();
    }

    /**
     * Authenticate user
     */
    public function authenticate($username, $password)
    {
        // Find user by username OR email
        $user = $this->where('username', $username)
                     ->orWhere('email', $username)
                     ->first();
        
        // Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Remove sensitive data
            unset($user['password']);
            unset($user['reset_token']);
            unset($user['two_factor_secret']);
            unset($user['remember_token']);
            return $user;
        }
        
        return false;
    }

    /**
     * Update last login info
     */
    public function updateLastLogin($userId, $ipAddress)
    {
        return $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s'),
            'last_ip' => $ipAddress
        ]);
    }

    /**
     * Get active users count
     */
    public function getActiveUsersCount()
    {
        return $this->where('is_active', 1)->countAllResults();
    }

    /**
     * Get user by reset token
     */
    public function getUserByResetToken($token)
    {
        return $this->where('reset_token', $token)
                    ->where('reset_token_expiry >', date('Y-m-d H:i:s'))
                    ->first();
    }

    /**
     * Set password reset token
     */
    public function setResetToken($email)
    {
        $user = $this->where('email', $email)->first();
        
        if (!$user) {
            return false;
        }
        
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expiry' => $expiry
        ]);
        
        return $token;
    }

    /**
     * Reset password
     */
    public function resetPassword($token, $newPassword)
    {
        $user = $this->getUserByResetToken($token);
        
        if (!$user) {
            return false;
        }
        
        return $this->update($user['id'], [
            'password' => $newPassword,
            'reset_token' => null,
            'reset_token_expiry' => null
        ]);
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($userId)
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
        return $this->update($userId, [
            'is_active' => $user['is_active'] ? 0 : 1
        ]);
    }

    public function setRememberToken($userId, string $token, string $expiry)
    {
        return parent::update($userId, [
            'remember_token' => $token,
            'remember_token_expiry' => $expiry,
        ]);
    }

    public function clearRememberToken($userId)
    {
        return parent::update($userId, [
            'remember_token' => null,
            'remember_token_expiry' => null,
        ]);
    }

    public function getUserByRememberToken(string $token)
    {
        return $this->where('remember_token', $token)
            ->where('remember_token_expiry >', date('Y-m-d H:i:s'))
            ->where('is_active', 1)
            ->first();
    }
}