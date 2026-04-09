<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionCategoryModel extends Model
{
    protected $table            = 'production_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['category_name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'category_name' => 'required|min_length[2]|max_length[100]|is_unique[production_categories.category_name]'
    ];

    protected $validationMessages = [
        'category_name' => [
            'required' => 'Category name is required',
            'min_length' => 'Category name must be at least 2 characters',
            'max_length' => 'Category name cannot exceed 100 characters',
            'is_unique' => 'Category name already exists'
        ]
    ];

    /**
     * Get all categories with job counts
     */
    public function getAllWithJobCounts()
    {
        $builder = $this->builder('production_categories pc');
        $builder->select('pc.*, COUNT(pj.id) as job_count');
        $builder->join('production_jobs pj', 'pj.production_category_id = pc.id', 'left');
        $builder->groupBy('pc.id');
        $builder->orderBy('pc.category_name', 'ASC');

        return $builder->get()->getResultArray();
    }
}