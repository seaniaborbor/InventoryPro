<?php

namespace App\Models;

use CodeIgniter\Model;

class BomTemplateItemModel extends Model
{
    protected $table            = 'bom_template_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['bom_template_id', 'product_id', 'quantity'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'bom_template_id' => 'required|is_not_unique[bom_templates.id]',
        'product_id' => 'required|is_not_unique[products.id]',
        'quantity' => 'required|decimal|greater_than[0]'
    ];

    /**
     * Get items with product details
     */
    public function getItemsWithProducts($templateId)
    {
        $builder = $this->builder();
        $builder->select('bom_template_items.*, products.product_name, products.sku, products.purchase_price, units.unit_name, units.unit_symbol');
        $builder->join('products', 'products.id = bom_template_items.product_id');
        $builder->join('units', 'units.id = products.unit_id', 'left');
        $builder->where('bom_template_items.bom_template_id', $templateId);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get items with product details for multiple templates
     */
    public function getItemsForTemplates($templateIds)
    {
        if (empty($templateIds)) {
            return [];
        }
        
        $builder = $this->builder();
        $builder->select('bom_template_items.*, products.product_name, products.sku, products.purchase_price');
        $builder->join('products', 'products.id = bom_template_items.product_id');
        $builder->whereIn('bom_template_items.bom_template_id', $templateIds);
        
        $items = $builder->get()->getResultArray();
        
        // Group by template_id
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item['bom_template_id']][] = $item;
        }
        
        return $grouped;
    }

    /**
     * Get total items count for a template
     */
    public function getItemCount($templateId)
    {
        return $this->where('bom_template_id', $templateId)->countAllResults();
    }

    /**
     * Get total material cost for a template
     */
    public function getTotalCost($templateId)
    {
        $builder = $this->builder();
        $builder->select('SUM(bom_template_items.quantity * products.purchase_price) as total');
        $builder->join('products', 'products.id = bom_template_items.product_id');
        $builder->where('bom_template_items.bom_template_id', $templateId);
        
        $result = $builder->get()->getRow();
        
        return $result->total ?? 0;
    }

    /**
     * Add multiple items to a template
     */
    public function addItems($templateId, array $items)
    {
        $data = [];
        
        foreach ($items as $item) {
            if (isset($item['product_id']) && isset($item['quantity']) && $item['quantity'] > 0) {
                $data[] = [
                    'bom_template_id' => $templateId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity']
                ];
            }
        }
        
        if (!empty($data)) {
            return $this->insertBatch($data);
        }
        
        return false;
    }

    /**
     * Update items for a template (replace all)
     */
    public function updateItems($templateId, array $items)
    {
        // Delete existing items
        $this->where('bom_template_id', $templateId)->delete();
        
        // Add new items
        return $this->addItems($templateId, $items);
    }

    /**
     * Clone items from one template to another
     */
    public function cloneItems($fromTemplateId, $toTemplateId)
    {
        $items = $this->where('bom_template_id', $fromTemplateId)->findAll();
        
        $data = [];
        foreach ($items as $item) {
            $data[] = [
                'bom_template_id' => $toTemplateId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity']
            ];
        }
        
        if (!empty($data)) {
            return $this->insertBatch($data);
        }
        
        return true;
    }
}