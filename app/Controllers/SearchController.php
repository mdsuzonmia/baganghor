<?php

namespace App\Controllers;

use App\Models\ProductCategoryModel;
use App\Models\ProductModel;
use App\Models\GuideModel;
use App\Models\ProductPackageModel;
use App\Services\StorefrontService;

class SearchController extends BaseController
{
    public function index()
    {
        $query = trim((string) $this->request->getGet('q'));
        $products = $packages = $categories = $guides = [];
        if ($query !== '') {
            $productModel = (new ProductModel())->withCategory()->where('products.status', 'active')->groupStart()->like('products.name', $query)->orLike('products.short_description', $query)->orLike('products.product_code', $query)->orLike('product_categories.name', $query)->groupEnd();
            $products = $productModel->orderBy('products.featured', 'DESC')->findAll(24);
            $packages = (new ProductPackageModel())->where('status', 'active')->groupStart()->like('name', $query)->orLike('short_description', $query)->groupEnd()->orderBy('featured', 'DESC')->findAll(12);
            $categories = (new ProductCategoryModel())->where('status', 'active')->groupStart()->like('name', $query)->orLike('description', $query)->groupEnd()->orderBy('sort_order')->findAll(12);
            $guides = (new GuideModel())->published()->groupStart()->like('title',$query)->orLike('excerpt',$query)->groupEnd()->findAll(12);
        }
        $store = new StorefrontService();
        return view('frontend/search/index', ['title' => $query ? 'খোঁজ: '.$query : 'পণ্য খুঁজুন', 'metaDescription' => 'Taharat Agro পণ্য, প্যাক ও গাইড খুঁজুন।', 'query' => $query, 'products' => $store->decorateProducts($products), 'packages' => $store->decoratePackages($packages), 'categories' => $categories, 'guides'=>$guides, 'settings' => $store->settings(), 'robots' => 'noindex,follow']);
    }
}
