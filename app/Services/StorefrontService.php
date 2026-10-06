<?php

namespace App\Services;

use App\Models\ProductCategoryModel;
use App\Models\ProductModel;
use App\Models\ProductPackageModel;

class StorefrontService
{
    public function settings(): array
    {
        return (new SettingService())->all();
    }

    public function categories(?int $limit = null, ?int $parentId = null): array
    {
        $model = (new ProductCategoryModel())->where('status', 'active');
        $parentId === null ? $model->where('parent_id', null) : $model->where('parent_id', $parentId);
        $model->orderBy('sort_order')->orderBy('name');
        return $limit ? $model->findAll($limit) : $model->findAll();
    }

    public function decorateProducts(array $products): array
    {
        if (!$products) return [];
        $ids = array_map(static fn(array $p): int => (int) $p['id'], $products);
        $variants = db_connect()->table('product_variants')
            ->select('id,product_id,variant_name,regular_price,sale_price,stock_quantity,low_stock_threshold,weight,weight_unit,sort_order')
            ->whereIn('product_id', $ids)->where('status', 'active')->where('deleted_at', null)
            ->orderBy('sort_order')->get()->getResultArray();
        $grouped = [];
        foreach ($variants as $variant) $grouped[(int) $variant['product_id']][] = $variant;
        $pricing = new ProductService();
        $reviewStats = (new ReviewService())->statsForProducts($ids);
        foreach ($products as &$product) {
            $productVariants = $grouped[(int) $product['id']] ?? [];
            if ($product['stock_type'] === 'variant') {
                $priced = array_filter($productVariants, static fn(array $v): bool => (float) $v['regular_price'] >= 0);
                $prices = array_map(static fn(array $v): float => $pricing->effectivePrice($v), $priced);
                $product['display_price'] = $prices ? min($prices) : 0;
                $product['display_regular_price'] = $product['display_price'];
                foreach ($priced as $variant) if ($pricing->effectivePrice($variant) === $product['display_price']) {$product['display_regular_price'] = (float) $variant['regular_price']; break;}
                $product['available'] = (bool) array_filter($productVariants, static fn(array $v): bool => (int) $v['stock_quantity'] > 0);
                $availableVariants = array_filter($productVariants, static fn(array $v): bool => (int) $v['stock_quantity'] > 0);
                $product['low_stock'] = $product['available'] && !array_filter($availableVariants, static fn(array $v): bool => (int) $v['stock_quantity'] > (int) $v['low_stock_threshold']);
                $product['price_from'] = count(array_unique($prices)) > 1;
            } else {
                $product['display_price'] = $pricing->effectivePrice($product);
                $product['display_regular_price'] = (float) $product['regular_price'];
                $product['available'] = !(bool) $product['manage_stock'] || (int) $product['stock_quantity'] > 0 || (bool) $product['allow_backorder'];
                $product['low_stock'] = (bool) $product['manage_stock'] && (int) $product['stock_quantity'] > 0 && (int) $product['stock_quantity'] <= (int) $product['low_stock_threshold'];
                $product['price_from'] = false;
            }
            $product['variants'] = $productVariants;
            $product['review_count'] = $reviewStats[(int) $product['id']]['review_count'] ?? 0;
            $product['average_rating'] = $reviewStats[(int) $product['id']]['average_rating'] ?? 0.0;
        }
        unset($product);
        return $products;
    }

    public function decoratePackages(array $packages): array
    {
        $service = new PackageService();
        foreach ($packages as &$package) {
            $package['available'] = $service->getPackageAvailableQuantity((int) $package['id']);
            $package['savings'] = max(0, (float) $package['regular_total'] - (float) $package['package_price']);
        }
        unset($package);
        return $packages;
    }

    public function homepage(): array
    {
        $products = (new ProductModel())->withCategory()->where(['products.status' => 'active', 'products.featured' => 1])->orderBy('products.sort_order')->orderBy('products.id', 'DESC')->findAll(8);
        $packages = (new ProductPackageModel())->where(['status' => 'active', 'featured' => 1])->orderBy('sort_order')->orderBy('id', 'DESC')->findAll(4);
        return ['settings' => $this->settings(), 'categories' => $this->categories(8), 'products' => $this->decorateProducts($products), 'packages' => $this->decoratePackages($packages)];
    }
}
