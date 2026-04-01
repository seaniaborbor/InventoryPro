<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['role_name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'role_name' => 'required|min_length[3]|max_length[50]|is_unique[roles.role_name,id,{id}]',
        'description' => 'permit_empty|max_length[500]'
    ];

    protected $validationMessages = [
        'role_name' => [
            'required' => 'Role name is required',
            'is_unique' => 'This role name already exists'
        ]
    ];

    /**
     * Get role with permissions
     */
    public function getRoleWithPermissions($roleId)
    {
        $role = $this->find($roleId);
        if (!$role) return null;
        
        $permissionModel = new PermissionModel();
        $role['permissions'] = $permissionModel->getPermissionsByRole($roleId);
        
        return $role;
    }

    /**
     * Get all roles with permission count
     */
    public function getAllRolesWithPermissionCount()
    {
        $roles = $this->findAll();
        $permissionModel = new PermissionModel();
        
        foreach ($roles as &$role) {
            $role['permission_count'] = $permissionModel->getPermissionCountByRole($role['id']);
        }
        
        return $roles;
    }
}