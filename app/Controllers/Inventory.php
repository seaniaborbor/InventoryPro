<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\UnitModel;
use App\Models\SupplierModel;
use App\Models\StockMovementModel;
use App\Models\AuditLogModel;

class Inventory extends BaseController
{
    protected $productModel;
    protected $categoryModel;
    protected $unitModel;
    protected $supplierModel;
    protected $stockMovementModel;
    protected $auditLogModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->unitModel = new UnitModel();
        $this->supplierModel = new SupplierModel();
        $this->stockMovementModel = new StockMovementModel();
        $this->auditLogModel = new AuditLogModel();

        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }
    }

    /**
     * Products List
     */
    public function products()
    {
        $data = [
            'title' => 'Products',
            'products' => $this->productModel->getAllProductsWithRelations(),
            'categories' => $this->categoryModel->findAll(),
            'units' => $this->unitModel->findAll(),
            'suppliers' => $this->supplierModel->findAll(),
            'activePage' => 'inventory',
            'activeSubPage' => 'products'
        ];

        return view('inventory/products', $data);
    }

    /**
     * Create Product Form
     */
    public function createProduct()
    {
        if ($this->request->isAJAX()) {
            $data = [
                'categories' => $this->categoryModel->findAll(),
                'units' => $this->unitModel->findAll(),
                'suppliers' => $this->supplierModel->findAll()
            ];
            return $this->response->setJSON($data);
        }

        return redirect()->to('/inventory/products');
    }

    /**
     * Store Product
     */
    /**
     * Store Product
     */
    public function storeProduct()
    {
        // Debug: Log incoming request
        log_message('debug', 'storeProduct called');
        log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));

        $rules = [
            'product_name' => 'required|min_length[2]|max_length[255]',
            'sku' => 'required|is_unique[products.sku]',
            'category_id' => 'required|is_not_unique[categories.id]',
            'unit_id' => 'required|is_not_unique[units.id]',
            'purchase_price' => 'required|decimal',
            'selling_price' => 'required|decimal',
            'quantity' => 'permit_empty|decimal',
            'minimum_stock' => 'permit_empty|decimal'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            log_message('debug', 'Validation errors: ' . json_encode($errors));

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data = [
            'product_name' => $this->request->getPost('product_name'),
            'sku' => $this->request->getPost('sku'),
            'barcode' => $this->request->getPost('barcode'),
            'description' => $this->request->getPost('description'),
            'category_id' => $this->request->getPost('category_id'),
            'unit_id' => $this->request->getPost('unit_id'),
            'supplier_id' => $this->request->getPost('supplier_id') ?: null,
            'purchase_price' => $this->request->getPost('purchase_price'),
            'selling_price' => $this->request->getPost('selling_price'),
            'quantity' => $this->request->getPost('quantity') ?: 0,
            'minimum_stock' => $this->request->getPost('minimum_stock') ?: 0,
            'created_by' => session()->get('user_id')
        ];

        log_message('debug', 'Data to insert: ' . json_encode($data));

        // Handle image upload
        $image = $this->request->getFile('product_image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            if (!is_dir('uploads/products')) {
                mkdir('uploads/products', 0777, true);
            }
            $newName = $image->getRandomName();
            $image->move('uploads/products', $newName);
            $data['product_image'] = 'uploads/products/' . $newName;
            log_message('debug', 'Image uploaded: ' . $data['product_image']);
        }

        $insertId = $this->productModel->insert($data);

        if ($insertId) {
            $productId = $insertId;

            // Log audit
            $this->auditLogModel->log(
                session()->get('user_id'),
                'product_create',
                'Product',
                $productId,
                null,
                $data
            );

            // Log initial stock movement if quantity > 0
            if ($data['quantity'] > 0) {
                $this->stockMovementModel->logMovement([
                    'product_id' => $productId,
                    'movement_type' => 'Adjustment',
                    'reference_type' => 'initial_stock',
                    'reference_id' => $productId,
                    'quantity' => $data['quantity'],
                    'previous_quantity' => 0,
                    'new_quantity' => $data['quantity'],
                    'created_by' => session()->get('user_id')
                ]);
            }

            log_message('debug', 'Product created successfully with ID: ' . $productId);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Product created successfully',
                    'product_id' => $productId
                ]);
            }
            return redirect()->to('/inventory/products')->with('success', 'Product created successfully');
        }

        log_message('error', 'Failed to insert product. Model errors: ' . json_encode($this->productModel->errors()));

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to save product. Please check your input.'
            ]);
        }
        return redirect()->back()->with('error', 'Failed to create product');
    }

    /**
     * Edit Product Form
     */
    public function editProduct($id)
    {
        $product = $this->productModel->getProductWithRelations($id);

        if (!$product) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found']);
            }
            return redirect()->to('/inventory/products')->with('error', 'Product not found');
        }

        if ($this->request->isAJAX()) {
            $data = [
                'product' => $product,
                'categories' => $this->categoryModel->findAll(),
                'units' => $this->unitModel->findAll(),
                'suppliers' => $this->supplierModel->findAll()
            ];
            return $this->response->setJSON($data);
        }

        $data = [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => $this->categoryModel->findAll(),
            'units' => $this->unitModel->findAll(),
            'suppliers' => $this->supplierModel->findAll(),
            'activePage' => 'inventory'
        ];

        return view('inventory/edit_product', $data);
    }

    /**
     * Update Product
     */
    public function updateProduct($id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found']);
        }

        $rules = [
            'product_name' => 'required|min_length[2]|max_length[255]',
            'sku' => 'required|is_unique[products.sku,id,' . $id . ']',
            'category_id' => 'required|is_not_unique[categories.id]',
            'unit_id' => 'required|is_not_unique[units.id]',
            'purchase_price' => 'required|decimal',
            'selling_price' => 'required|decimal',
            'minimum_stock' => 'permit_empty|decimal'
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $oldData = $product;

        $data = [
            'product_name' => $this->request->getPost('product_name'),
            'sku' => $this->request->getPost('sku'),
            'barcode' => $this->request->getPost('barcode'),
            'description' => $this->request->getPost('description'),
            'category_id' => $this->request->getPost('category_id'),
            'unit_id' => $this->request->getPost('unit_id'),
            'supplier_id' => $this->request->getPost('supplier_id') ?: null,
            'purchase_price' => $this->request->getPost('purchase_price'),
            'selling_price' => $this->request->getPost('selling_price'),
            'minimum_stock' => $this->request->getPost('minimum_stock') ?: 0,
            'updated_by' => session()->get('user_id')
        ];

        // Handle image upload
        $image = $this->request->getFile('product_image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            if (!is_dir('uploads/products')) {
                mkdir('uploads/products', 0777, true);
            }
            $newName = $image->getRandomName();
            $image->move('uploads/products', $newName);
            $data['product_image'] = 'uploads/products/' . $newName;

            // Delete old image
            if ($product['product_image'] && file_exists($product['product_image'])) {
                unlink($product['product_image']);
            }
        }

        if ($this->productModel->update($id, $data)) {
            // Log audit
            $this->auditLogModel->log(
                session()->get('user_id'),
                'product_update',
                'Product',
                $id,
                $oldData,
                $data
            );

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Product updated successfully']);
            }
            return redirect()->to('/inventory/products')->with('success', 'Product updated successfully');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update product']);
        }
        return redirect()->back()->with('error', 'Failed to update product');
    }

    /**
     * View Product Details
     */
    public function view($id)
    {
        $product = $this->productModel->getProductWithRelations($id);

        if (!$product) {
            return redirect()->to('/inventory/products')->with('error', 'Product not found');
        }

        $data = [
            'title' => 'Product Details - ' . $product['product_name'],
            'product' => $product,
            'activePage' => 'inventory'
        ];

        return view('inventory/view_product', $data);
    }

    /**
     * Delete Product
     */
    public function deleteProduct($id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found']);
        }

        // Check if product has sales or purchases
        $saleItemModel = new \App\Models\SaleItemModel();
        $purchaseItemModel = new \App\Models\PurchaseItemModel();

        $hasSales = $saleItemModel->where('product_id', $id)->countAllResults() > 0;
        $hasPurchases = $purchaseItemModel->where('product_id', $id)->countAllResults() > 0;

        if ($hasSales || $hasPurchases) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cannot delete product with existing sales or purchases. You can deactivate it instead.'
            ]);
        }

        $oldData = $product;

        // Delete image
        if ($product['product_image'] && file_exists($product['product_image'])) {
            unlink($product['product_image']);
        }

        if ($this->productModel->delete($id)) {
            // Log audit
            $this->auditLogModel->log(
                session()->get('user_id'),
                'product_delete',
                'Product',
                $id,
                $oldData
            );

            return $this->response->setJSON(['status' => 'success', 'message' => 'Product deleted successfully']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete product']);
    }

    /**
     * Get Product by Barcode (AJAX)
     */
    public function getProductByBarcode($barcode)
    {
        $product = $this->productModel->getByBarcode($barcode);

        if ($product) {
            $product = $this->productModel->getProductWithRelations($product['id']);
            return $this->response->setJSON(['status' => 'success', 'product' => $product]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found']);
    }

    /**
     * Categories List
     */
    public function categories()
    {
        $data = [
            'title' => 'Categories',
            'categories' => $this->categoryModel->getAllWithProductCounts(),
            'activePage' => 'inventory',
            'activeSubPage' => 'categories'
        ];

        return view('inventory/categories', $data);
    }

    /**
     * Store Category
     */
    public function storeCategory()
    {
        $rules = [
            'category_name' => 'required|min_length[2]|max_length[100]|is_unique[categories.category_name]'
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()->with('error', 'Category name is required and must be unique');
        }

        $data = [
            'category_name' => $this->request->getPost('category_name'),
            'description' => $this->request->getPost('description'),
            // 'parent_id' => $this->request->getPost('parent_id') ?: null
        ];

        if ($this->categoryModel->insert($data)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'category_create',
                'Category',
                $this->categoryModel->getInsertID(),
                null,
                $data
            );

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Category created successfully']);
            }
            return redirect()->to('/inventory/categories')->with('success', 'Category created successfully');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create category']);
        }
        return redirect()->back()->with('error', 'Failed to create category');
    }

    /**
     * Update Category
     */
    public function updateCategory($id)
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Category not found']);
        }

        $rules = [
            'category_name' => 'required|min_length[2]|max_length[100]|is_unique[categories.category_name,id,' . $id . ']'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $oldData = $category;
        $data = [
            'category_name' => $this->request->getPost('category_name'),
            'description' => $this->request->getPost('description')
        ];

        if ($this->categoryModel->update($id, $data)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'category_update',
                'Category',
                $id,
                $oldData,
                $data
            );

            return $this->response->setJSON(['status' => 'success', 'message' => 'Category updated successfully']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update category']);
    }

    /**
     * Delete Category
     */
    public function deleteCategory($id)
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Category not found']);
        }

        // Check if category has products
        $productCount = $this->productModel->where('category_id', $id)->countAllResults();

        if ($productCount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cannot delete category that has ' . $productCount . ' product(s)'
            ]);
        }

        $oldData = $category;

        if ($this->categoryModel->delete($id)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'category_delete',
                'Category',
                $id,
                $oldData
            );

            return $this->response->setJSON(['status' => 'success', 'message' => 'Category deleted successfully']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete category']);
    }

    /**
     * Stock Adjustments View
     */
    public function stockAdjustments()
    {
        $data = [
            'title' => 'Stock Adjustments',
            'products' => $this->productModel->findAll(),
            'activePage' => 'inventory',
            'activeSubPage' => 'stock-adjustments'
        ];

        return view('inventory/stock_adjustments', $data);
    }

  
    
    /**
     * Get Stock Adjustments (AJAX)
     */
    public function getStockAdjustments()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        try {
            $adjustments = $this->stockMovementModel
                ->select('stock_movements.*, products.product_name, users.full_name as created_by_name')
                ->join('products', 'products.id = stock_movements.product_id', 'left')
                ->join('users', 'users.id = stock_movements.created_by', 'left')
                ->where('stock_movements.movement_type', 'Adjustment')
                ->orderBy('stock_movements.created_at', 'DESC')
                ->findAll();

            // Format the data for display
            $formattedData = [];
            foreach ($adjustments as $adj) {
                $formattedData[] = [
                    'created_at' => $adj['created_at'],
                    'product_name' => $adj['product_name'] ?? 'Unknown Product',
                    'adjustment_type' => $adj['quantity'] > 0 ? 'increase' : 'decrease',
                    'quantity' => abs($adj['quantity']),
                    'previous_quantity' => $adj['previous_quantity'],
                    'new_quantity' => $adj['new_quantity'],
                    'reason' => $adj['reference_type'] ?? 'Stock Adjustment',
                    'created_by_name' => $adj['created_by_name'] ?? 'System'
                ];
            }

            return $this->response->setJSON(['status' => 'success', 'data' => $formattedData]);

        } catch (\Exception $e) {
            log_message('error', 'getStockAdjustments error: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Adjust Stock
     */
    public function adjustStock()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $productId = $this->request->getPost('product_id');
        $adjustmentType = $this->request->getPost('adjustment_type');
        $quantity = $this->request->getPost('quantity');
        $reason = $this->request->getPost('reason');
        $notes = $this->request->getPost('notes');

        $product = $this->productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found']);
        }

        if ($quantity <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Quantity must be greater than zero']);
        }

        $oldQuantity = $product['quantity'];

        if ($adjustmentType === 'increase') {
            $newQuantity = $oldQuantity + $quantity;
        } else {
            if ($oldQuantity < $quantity) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Insufficient stock to decrease']);
            }
            $newQuantity = $oldQuantity - $quantity;
        }

        // Update product quantity
        $this->productModel->update($productId, [
            'quantity' => $newQuantity,
            'updated_by' => session()->get('user_id')
        ]);

        // Log adjustment
        $this->stockMovementModel->logMovement([
            'product_id' => $productId,
            'movement_type' => 'Adjustment',
            'reference_type' => 'stock_adjustment',
            'reference_id' => $productId,
            'quantity' => $adjustmentType === 'increase' ? $quantity : -$quantity,
            'previous_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'unit_price' => $product['purchase_price'],
            'total_value' => $product['purchase_price'] * $quantity,
            'created_by' => session()->get('user_id')
        ]);

        // Log audit
        $this->auditLogModel->log(
            session()->get('user_id'),
            'stock_adjustment',
            'Product',
            $productId,
            ['quantity' => $oldQuantity, 'reason' => $reason],
            ['quantity' => $newQuantity, 'adjustment' => $quantity, 'type' => $adjustmentType, 'reason' => $reason, 'notes' => $notes]
        );

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Stock adjusted successfully',
            'data' => [
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'adjustment' => $quantity,
                'type' => $adjustmentType
            ]
        ]);
    }

    /**
     * Low Stock Items
     */
    public function lowStock()
    {
        $data = [
            'title' => 'Low Stock Items',
            'products' => $this->productModel->getLowStockItems(),
            'activePage' => 'inventory',
            'activeSubPage' => 'low-stock'
        ];

        return view('inventory/low_stock', $data);
    }

    /**
     * Units List
     */
    public function units()
    {
        $data = [
            'title' => 'Units',
            'units' => $this->unitModel->getAllWithProductCount(),
            'activePage' => 'inventory'
        ];

        return view('inventory/units', $data);
    }

    /**
     * Store Unit
     */
    public function storeUnit()
    {
        $rules = [
            'unit_name' => 'required|min_length[1]|max_length[50]|is_unique[units.unit_name]',
            'unit_symbol' => 'required|max_length[10]'
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()->with('error', 'Invalid unit data');
        }

        $data = [
            'unit_name' => $this->request->getPost('unit_name'),
            'unit_symbol' => $this->request->getPost('unit_symbol')
        ];

        if ($this->unitModel->insert($data)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'unit_create',
                'Unit',
                $this->unitModel->getInsertID(),
                null,
                $data
            );

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'message' => 'Unit created successfully']);
            }
            return redirect()->to('/inventory/units')->with('success', 'Unit created successfully');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create unit']);
        }
        return redirect()->back()->with('error', 'Failed to create unit');
    }

    





    

    /**
     * Update Unit
     */
    public function updateUnit($id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unit not found']);
        }

        $rules = [
            'unit_name' => 'required|min_length[1]|max_length[50]|is_unique[units.unit_name,id,' . $id . ']',
            'unit_symbol' => 'required|max_length[10]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $oldData = $unit;
        $data = [
            'unit_name' => $this->request->getPost('unit_name'),
            'unit_symbol' => $this->request->getPost('unit_symbol')
        ];

        if ($this->unitModel->update($id, $data)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'unit_update',
                'Unit',
                $id,
                $oldData,
                $data
            );

            return $this->response->setJSON(['status' => 'success', 'message' => 'Unit updated successfully']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update unit']);
    }

    /**
     * Delete Unit
     */
    public function deleteUnit($id)
    {
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unit not found']);
        }

        // Check if unit is used in products
        $productCount = $this->productModel->where('unit_id', $id)->countAllResults();

        if ($productCount > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cannot delete unit that is used by ' . $productCount . ' product(s)'
            ]);
        }

        $oldData = $unit;

        if ($this->unitModel->delete($id)) {
            $this->auditLogModel->log(
                session()->get('user_id'),
                'unit_delete',
                'Unit',
                $id,
                $oldData
            );

            return $this->response->setJSON(['status' => 'success', 'message' => 'Unit deleted successfully']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete unit']);
    }
}