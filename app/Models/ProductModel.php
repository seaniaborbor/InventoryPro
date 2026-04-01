<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_name', 'sku', 'barcode', 'qr_code', 'description',
        'category_id', 'unit_id', 'supplier_id', 'purchase_price', 
        'selling_price', 'quantity', 'minimum_stock', 'product_image',
        'created_by', 'updated_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'product_name' => 'required|min_length[2]|max_length[255]',
        'sku' => 'required|min_length[2]|max_length[100]|is_unique[products.sku,id,{id}]',
        'barcode' => 'permit_empty|max_length[100]|is_unique[products.barcode,id,{id}]',
        'category_id' => 'required|integer|is_not_unique[categories.id]',
        'unit_id' => 'required|integer|is_not_unique[units.id]',
        'purchase_price' => 'required|decimal',
        'selling_price' => 'required|decimal',
        'quantity' => 'permit_empty|decimal',
        'minimum_stock' => 'permit_empty|decimal'
    ];

    /**
     * Get product with relations (category, unit, supplier)
     */
    public function getProductWithRelations($productId)
    {
        $builder = $this->builder();
        $builder->select('products.*, categories.category_name, units.unit_name, units.unit_symbol, suppliers.supplier_name');
        $builder->join('categories', 'categories.id = products.category_id', 'left');
        $builder->join('units', 'units.id = products.unit_id', 'left');
        $builder->join('suppliers', 'suppliers.id = products.supplier_id', 'left');
        $builder->where('products.id', $productId);
        
        return $builder->get()->getRowArray();
    }

    /**
     * Get all products with relations
     */
    public function getAllProductsWithRelations()
    {
        $builder = $this->builder();
        $builder->select('products.*, categories.category_name, units.unit_name, units.unit_symbol, suppliers.supplier_name');
        $builder->join('categories', 'categories.id = products.category_id', 'left');
        $builder->join('units', 'units.id = products.unit_id', 'left');
        $builder->join('suppliers', 'suppliers.id = products.supplier_id', 'left');
        $builder->orderBy('products.id', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get low stock items - FIXED VERSION
     */
    public function getLowStockItems($threshold = null)
    {
        // Default threshold value
        if ($threshold === null) {
            $threshold = 10;
        }
        
        return $this->where('quantity <=', $threshold)
                    ->where('quantity >', 0)
                    ->findAll();
    }

    /**
     * Get out of stock items
     */
    public function getOutOfStockItems()
    {
        return $this->where('quantity', 0)->findAll();
    }

    /**
     * Update stock quantity
     */
    public function updateStock($productId, $quantity, $type = 'add', $userId = null)
    {
        $product = $this->find($productId);
        if (!$product) return false;
        
        $oldQuantity = $product['quantity'];
        
        if ($type === 'add') {
            $newQuantity = $oldQuantity + $quantity;
        } elseif ($type === 'subtract') {
            if ($oldQuantity < $quantity) return false;
            $newQuantity = $oldQuantity - $quantity;
        } else {
            $newQuantity = $quantity;
        }
        
        // Update product quantity
        $updated = $this->update($productId, ['quantity' => $newQuantity, 'updated_by' => $userId]);
        
        if ($updated) {
            // Log stock movement
            $movementModel = new StockMovementModel();
            $movementModel->logMovement([
                'product_id' => $productId,
                'movement_type' => $type === 'add' ? 'Adjustment' : 'Adjustment',
                'reference_type' => 'stock_adjustment',
                'reference_id' => $productId,
                'quantity' => $type === 'add' ? $quantity : -$quantity,
                'previous_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'created_by' => $userId
            ]);
        }
        
        return $updated;
    }

    /**
     * Get total stock value
     */
    public function getTotalStockValue($currency = 'LRD')
    {
        $products = $this->findAll();
        $total = 0;
        
        foreach ($products as $product) {
            $total += $product['quantity'] * $product['purchase_price'];
        }
        
        return $total;
    }

    /**
     * Search products by name, SKU, or barcode
     */
    public function search($keyword)
    {
        return $this->like('product_name', $keyword)
                    ->orLike('sku', $keyword)
                    ->orLike('barcode', $keyword)
                    ->findAll();
    }

    /**
     * Get product by barcode
     */
    public function getByBarcode($barcode)
    {
        return $this->where('barcode', $barcode)->first();
    }

    /**
     * Get product by SKU
     */
    public function getBySku($sku)
    {
        return $this->where('sku', $sku)->first();
    }

    /**
     * Get products by category
     */
    public function getByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)->findAll();
    }

    /**
     * Get products by supplier
     */
    public function getBySupplier($supplierId)
    {
        return $this->where('supplier_id', $supplierId)->findAll();
    }

    /**
     * Generate SKU automatically
     */
    public function generateSku($productName, $categoryId)
    {
        $category = (new CategoryModel())->find($categoryId);
        $prefix = strtoupper(substr($category['category_name'] ?? 'PRD', 0, 3));
        $namePart = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $productName), 0, 5));
        $random = strtoupper(substr(uniqid(), -5));
        
        return $prefix . '-' . $namePart . '-' . $random;
    }
}