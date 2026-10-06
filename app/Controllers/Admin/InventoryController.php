<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductCategoryModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Services\InventoryService;

class InventoryController extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $prefix = $db->getPrefix();
        $q = trim((string) $this->request->getGet('q'));
        $category = (int) $this->request->getGet('category_id');
        $stock = (string) $this->request->getGet('stock');
        $type = (string) $this->request->getGet('type');
        $simple = $db->query("SELECT p.id product_id,NULL variant_id,p.name product_name,NULL variant_name,p.sku,p.stock_quantity,p.low_stock_threshold,p.status,p.stock_type,p.category_id FROM {$prefix}products p WHERE p.deleted_at IS NULL AND p.stock_type='simple'")->getResultArray();
        $variants = $db->query("SELECT p.id product_id,v.id variant_id,p.name product_name,v.variant_name,v.sku,v.stock_quantity,v.low_stock_threshold,v.status,p.stock_type,p.category_id FROM {$prefix}product_variants v JOIN {$prefix}products p ON p.id=v.product_id WHERE p.deleted_at IS NULL AND v.deleted_at IS NULL")->getResultArray();
        $rows = array_values(array_filter(array_merge($simple, $variants), static function (array $row) use ($q, $category, $stock, $type): bool {
            if ($q && stripos($row['product_name'] . ' ' . $row['variant_name'] . ' ' . $row['sku'], $q) === false) return false;
            if ($category && (int) $row['category_id'] !== $category) return false;
            if ($type && $row['stock_type'] !== $type) return false;
            if ($stock === 'low' && ! ($row['stock_quantity'] > 0 && $row['stock_quantity'] <= $row['low_stock_threshold'])) return false;
            if ($stock === 'out' && $row['stock_quantity'] > 0) return false;
            return true;
        }));
        $page = max(1, (int) $this->request->getGet('page_inventory'));
        $perPage = 30;
        $pager = service('pager');
        $pagerLinks = $pager->makeLinks($page, $perPage, count($rows), 'default_full', 0, 'inventory');
        $rows = array_slice($rows, ($page - 1) * $perPage, $perPage);
        return view('admin/inventory/index', ['title' => 'Inventory', 'items' => $rows, 'categories' => (new ProductCategoryModel())->orderBy('name')->findAll(), 'filters' => compact('q', 'category', 'stock', 'type'), 'stockService' => new InventoryService(), 'pagerLinks' => $pagerLinks]);
    }

    public function adjustForm(string $kind, int $id)
    {
        $item = $kind === 'variant' ? (new ProductVariantModel())->find($id) : (new ProductModel())->find($id);
        if (! $item) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('admin/inventory/adjust', ['title' => 'Adjust Stock', 'kind' => $kind, 'item' => $item]);
    }

    public function adjust(string $kind, int $id)
    {
        if (! in_array($kind, ['product', 'variant'], true) || ! $this->validate(['mode' => 'required|in_list[add,remove,set]', 'quantity' => 'required|integer|greater_than_equal_to[0]', 'note' => 'required|min_length[3]|max_length[500]'])) return redirect()->back()->withInput()->with('errors', $this->validator?->getErrors() ?? ['item' => 'Invalid inventory item.']);
        try {
            $service = new InventoryService();
            $kind === 'variant' ? $service->adjustVariantStock($id, $this->request->getPost('mode'), (int) $this->request->getPost('quantity'), $this->request->getPost('note')) : $service->adjustProductStock($id, $this->request->getPost('mode'), (int) $this->request->getPost('quantity'), $this->request->getPost('note'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('danger', $e->getMessage());
        }
        return redirect()->to(url_to('admin.inventory'))->with('success', 'Stock adjusted successfully.');
    }

    public function history(string $kind, int $id)
    {
        $query = db_connect()->table('inventory_movements')->select('inventory_movements.*,admin_users.name AS admin_name')->join('admin_users', 'admin_users.id=inventory_movements.admin_user_id', 'left');
        $kind === 'variant' ? $query->where('inventory_movements.variant_id', $id) : $query->where('inventory_movements.product_id', $id)->where('inventory_movements.variant_id', null);
        return view('admin/inventory/history', ['title' => 'Stock History', 'movements' => $query->orderBy('inventory_movements.id', 'DESC')->get()->getResultArray()]);
    }
}
