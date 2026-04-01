<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['category_name', 'description', 'parent_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'category_name' => 'required|min_length[2]|max_length[100]',
        'parent_id' => 'permit_empty|integer|is_not_unique[categories.id,id,{parent_id}]'
    ];

    /**
     * Get all categories with parent name
     */
    public function getAllWithParent()
    {
        $builder = $this->builder('categories c');
        $builder->select('c.*, p.category_name as parent_name');
        $builder->join('categories p', 'p.id = c.parent_id', 'left');
        $builder->orderBy('c.category_name', 'ASC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get parent categories (no parent)
     */
    public function getParentCategories()
    {
        return $this->where('parent_id', null)->findAll();
    }

    /**
     * Get subcategories by parent ID
     */
    public function getSubcategories($parentId)
    {
        return $this->where('parent_id', $parentId)->findAll();
    }

    /**
     * Get category with product count
     */
    public function getCategoryWithProductCount($categoryId)
    {
        $category = $this->find($categoryId);
        if (!$category) return null;
        
        $productModel = new ProductModel();
        $category['product_count'] = $productModel->where('category_id', $categoryId)->countAllResults();
        
        return $category;
    }

    /**
     * Get all categories with product counts
     */
    public function getAllWithProductCounts()
    {
        $categories = $this->findAll();
        $productModel = new ProductModel();
        
        foreach ($categories as &$cat) {
            $cat['product_count'] = $productModel->where('category_id', $cat['id'])->countAllResults();
        }
        
        return $categories;
    }
}