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

    protected $validationMessages = [
        'product_name' => [
            'required' => 'Product name is required',
            'min_length' => 'Product name must be at least 2 characters',
            'max_length' => 'Product name cannot exceed 255 characters'
        ],
        'sku' => [
            'required' => 'SKU is required',
            'is_unique' => 'This SKU already exists'
        ],
        'barcode' => [
            'is_unique' => 'This barcode already exists'
        ],
        'category_id' => [
            'required' => 'Category is required',
            'is_not_unique' => 'Selected category does not exist'
        ],
        'unit_id' => [
            'required' => 'Unit is required',
            'is_not_unique' => 'Selected unit does not exist'
        ],
        'purchase_price' => [
            'required' => 'Purchase price is required',
            'decimal' => 'Purchase price must be a valid number'
        ],
        'selling_price' => [
            'required' => 'Selling price is required',
            'decimal' => 'Selling price must be a valid number'
        ]
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
     * Get low stock items - FIXED: Uses each product's minimum_stock value
     * Returns products where quantity <= minimum_stock AND quantity > 0
     */
    public function getLowStockItems()
    {
        return $this->where('quantity <= minimum_stock', null, false)
                    ->where('quantity >', 0)
                    ->where('deleted_at', null)
                    ->orderBy('quantity', 'ASC')
                    ->findAll();
    }

    /**
     * Get critical low stock items (quantity <= 50% of minimum_stock)
     */
    public function getCriticalLowStockItems()
    {
        return $this->where('quantity <= (minimum_stock / 2)', null, false)
                    ->where('quantity >', 0)
                    ->where('deleted_at', null)
                    ->orderBy('quantity', 'ASC')
                    ->findAll();
    }

    /**
     * Get items that need reordering (below minimum stock, including out of stock)
     */
    public function getReorderItems()
    {
        return $this->where('quantity <= minimum_stock', null, false)
                    ->where('deleted_at', null)
                    ->orderBy('quantity', 'ASC')
                    ->findAll();
    }

    /**
     * Get out of stock items (quantity = 0)
     */
    public function getOutOfStockItems()
    {
        return $this->where('quantity', 0)
                    ->where('deleted_at', null)
                    ->orderBy('product_name', 'ASC')
                    ->findAll();
    }

    /**
     * Get products with stock status information
     */
    public function getProductsWithStockStatus()
    {
        $products = $this->getAllProductsWithRelations();
        
        foreach ($products as &$product) {
            $quantity = (float) ($product['quantity'] ?? 0);
            $minimumStock = (float) ($product['minimum_stock'] ?? 0);
            
            if ($quantity <= 0) {
                $product['stock_status'] = 'out';
                $product['stock_status_label'] = 'Out of Stock';
                $product['stock_status_class'] = 'danger';
            } elseif ($quantity <= $minimumStock) {
                $product['stock_status'] = 'low';
                $product['stock_status_label'] = 'Low Stock';
                $product['stock_status_class'] = 'warning';
            } else {
                $product['stock_status'] = 'in';
                $product['stock_status_label'] = 'In Stock';
                $product['stock_status_class'] = 'success';
            }
            
            $product['stock_value_purchase'] = $quantity * ($product['purchase_price'] ?? 0);
            $product['stock_value_selling'] = $quantity * ($product['selling_price'] ?? 0);
        }
        
        return $products;
    }

    /**
     * Update stock quantity - IMPROVED with better logging
     */
    public function updateStock($productId, $quantity, $type = 'add', $userId = null, $reference = null)
    {
        $product = $this->find($productId);
        if (!$product) return false;
        
        $oldQuantity = (float) $product['quantity'];
        $quantity = (float) $quantity;
        
        if ($type === 'add') {
            $newQuantity = $oldQuantity + $quantity;
        } elseif ($type === 'subtract') {
            if ($oldQuantity < $quantity) {
                log_message('error', "Insufficient stock for product {$productId}. Available: {$oldQuantity}, Required: {$quantity}");
                return false;
            }
            $newQuantity = $oldQuantity - $quantity;
        } else {
            $newQuantity = $quantity;
        }
        
        // Update product quantity
        $updated = $this->update($productId, [
            'quantity' => $newQuantity, 
            'updated_by' => $userId
        ]);
        
        if ($updated) {
            // Log stock movement
            $movementModel = new StockMovementModel();
            $movementModel->logMovement([
                'product_id' => $productId,
                'movement_type' => $type === 'add' ? 'Addition' : 'Reduction',
                'reference_type' => $reference ?? 'stock_adjustment',
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
     * Get stock value by category
     */
    public function getStockValueByCategory()
    {
        $builder = $this->builder();
        $builder->select('categories.category_name, SUM(products.quantity * products.purchase_price) as total_value, COUNT(products.id) as product_count');
        $builder->join('categories', 'categories.id = products.category_id', 'left');
        $builder->where('products.deleted_at', null);
        $builder->groupBy('products.category_id');
        $builder->orderBy('total_value', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Search products by name, SKU, or barcode
     */
    public function search($keyword)
    {
        return $this->like('product_name', $keyword)
                    ->orLike('sku', $keyword)
                    ->orLike('barcode', $keyword)
                    ->where('deleted_at', null)
                    ->findAll();
    }

    /**
     * Search products with stock filter
     */
    public function searchWithStockFilter($keyword, $stockFilter = null)
    {
        $builder = $this->like('product_name', $keyword)
                    ->orLike('sku', $keyword)
                    ->orLike('barcode', $keyword)
                    ->where('deleted_at', null);
        
        if ($stockFilter === 'low') {
            $builder->where('quantity <= minimum_stock', null, false);
            $builder->where('quantity >', 0);
        } elseif ($stockFilter === 'out') {
            $builder->where('quantity', 0);
        } elseif ($stockFilter === 'in') {
            $builder->where('quantity > minimum_stock', null, false);
        }
        
        return $builder->findAll();
    }

    /**
     * Get product by barcode
     */
    public function getByBarcode($barcode)
    {
        return $this->where('barcode', $barcode)
                    ->where('deleted_at', null)
                    ->first();
    }

    /**
     * Get product by SKU
     */
    public function getBySku($sku)
    {
        return $this->where('sku', $sku)
                    ->where('deleted_at', null)
                    ->first();
    }

    /**
     * Get products by category
     */
    public function getByCategory($categoryId)
    {
        return $this->where('category_id', $categoryId)
                    ->where('deleted_at', null)
                    ->findAll();
    }

    /**
     * Get products by supplier
     */
    public function getBySupplier($supplierId)
    {
        return $this->where('supplier_id', $supplierId)
                    ->where('deleted_at', null)
                    ->findAll();
    }

    /**
     * Get product count by category
     */
    public function getCountByCategory()
    {
        $builder = $this->builder();
        $builder->select('categories.category_name, COUNT(products.id) as product_count');
        $builder->join('categories', 'categories.id = products.category_id', 'left');
        $builder->where('products.deleted_at', null);
        $builder->groupBy('products.category_id');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        $totalProducts = $this->where('deleted_at', null)->countAllResults();
        $lowStock = $this->getLowStockItems();
        $outOfStock = $this->getOutOfStockItems();
        
        return [
            'total_products' => $totalProducts,
            'low_stock_count' => count($lowStock),
            'out_of_stock_count' => count($outOfStock),
            'total_stock_value' => $this->getTotalStockValue()
        ];
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
        
        $sku = $prefix . '-' . $namePart . '-' . $random;
        
        // Ensure SKU is unique
        $counter = 1;
        while ($this->where('sku', $sku)->first()) {
            $sku = $prefix . '-' . $namePart . '-' . $random . '-' . $counter;
            $counter++;
        }
        
        return $sku;
    }

    /**
     * Bulk update stock quantities
     */
    public function bulkUpdateStock($updates, $userId = null)
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];
        
        foreach ($updates as $update) {
            if (!isset($update['product_id']) || !isset($update['quantity'])) {
                $results['failed']++;
                $results['errors'][] = 'Missing product_id or quantity';
                continue;
            }
            
            $result = $this->updateStock(
                $update['product_id'],
                abs($update['quantity']),
                $update['quantity'] >= 0 ? 'add' : 'subtract',
                $userId,
                $update['reference'] ?? 'bulk_update'
            );
            
            if ($result) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Failed to update product ID: {$update['product_id']}";
            }
        }
        
        return $results;
    }

    /**
     * Check if product has sufficient stock
     */
    public function hasSufficientStock($productId, $requiredQuantity)
    {
        $product = $this->find($productId);
        if (!$product) return false;
        
        return (float) $product['quantity'] >= (float) $requiredQuantity;
    }

    /**
     * Get product with current stock and minimum stock comparison
     */
    public function getProductStockStatus($productId)
    {
        $product = $this->find($productId);
        if (!$product) return null;
        
        $quantity = (float) $product['quantity'];
        $minimumStock = (float) ($product['minimum_stock'] ?? 0);
        
        return [
            'product' => $product,
            'quantity' => $quantity,
            'minimum_stock' => $minimumStock,
            'is_low_stock' => $quantity <= $minimumStock && $quantity > 0,
            'is_out_of_stock' => $quantity <= 0,
            'reorder_quantity' => $quantity < $minimumStock ? ($minimumStock - $quantity) : 0
        ];
    }
}