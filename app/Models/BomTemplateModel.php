<?php

namespace App\Models;

use CodeIgniter\Model;

class BomTemplateModel extends Model
{
    protected $table            = 'bom_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['template_name', 'description', 'finished_product_id', 'created_by'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'template_name' => 'required|min_length[2]|max_length[255]|is_unique[bom_templates.template_name,id,{id}]',
        'finished_product_id' => 'permit_empty|is_not_unique[products.id]'
    ];

    /**
     * Get all templates with items
     */
    public function getAllWithItems()
    {
        $templates = $this->findAll();
        $itemModel = new BomTemplateItemModel();
        
        foreach ($templates as &$template) {
            $template['items'] = $itemModel->getItemsWithProducts($template['id']);
            $template['item_count'] = count($template['items']);
        }
        
        return $templates;
    }

    /**
     * Get template with items
     */
    public function getTemplateWithItems($id)
    {
        $template = $this->find($id);
        
        if (!$template) {
            return null;
        }
        
        $itemModel = new BomTemplateItemModel();
        $template['items'] = $itemModel->getItemsWithProducts($id);
        
        return $template;
    }

    /**
     * Get templates for dropdown
     */
    public function getOptions()
    {
        $templates = $this->orderBy('template_name', 'ASC')->findAll();
        $options = [];
        
        foreach ($templates as $template) {
            $options[$template['id']] = $template['template_name'];
        }
        
        return $options;
    }

    /**
     * Duplicate template
     */
    public function duplicate($id, $newName)
    {
        $template = $this->find($id);
        
        if (!$template) {
            return false;
        }
        
        $itemModel = new BomTemplateItemModel();
        $items = $itemModel->where('bom_template_id', $id)->findAll();
        
        $newTemplateId = $this->insert([
            'template_name' => $newName,
            'description' => $template['description'] . ' (Copy)',
            'finished_product_id' => $template['finished_product_id'],
            'created_by' => session()->get('user_id')
        ]);
        
        if ($newTemplateId) {
            foreach ($items as $item) {
                $itemModel->insert([
                    'bom_template_id' => $newTemplateId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity']
                ]);
            }
            return $newTemplateId;
        }
        
        return false;
    }

    /**
     * Check if template is used in production jobs
     */
    public function isUsed($id)
    {
        $productionJobModel = new ProductionJobModel();
        // Check if any production job references this template (if you store template_id in production_jobs)
        // For now, return false
        return false;
    }
}