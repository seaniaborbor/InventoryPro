<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table            = 'units';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['unit_name', 'unit_symbol'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'unit_name' => 'required|min_length[1]|max_length[50]|is_unique[units.unit_name,id,{id}]',
        'unit_symbol' => 'required|max_length[10]'
    ];

    /**
     * Get all units with product count
     */
    public function getAllWithProductCount()
    {
        $units = $this->findAll();
        $productModel = new ProductModel();
        
        foreach ($units as &$unit) {
            $unit['product_count'] = $productModel->where('unit_id', $unit['id'])->countAllResults();
        }
        
        return $units;
    }

    /**
     * Get unit options for dropdown
     */
    public function getOptions()
    {
        $units = $this->findAll();
        $options = [];
        foreach ($units as $unit) {
            $options[$unit['id']] = $unit['unit_name'] . ' (' . $unit['unit_symbol'] . ')';
        }
        return $options;
    }
}