<?php

namespace App\Controllers\Catalog;

use App\Controllers\BaseController;
use App\Models\ProductCategoryModel;
use App\Services\StorefrontService;

class CategoryController extends BaseController
{
    public function index()
    {
        $categories = (new ProductCategoryModel())
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->findAll();

        return view('frontend/categories/index', [
            'title' => 'পণ্যের ক্যাটাগরি',
            'metaDescription' => 'Taharat Agro-এর কৃষি ও বাগান পণ্যের সব ক্যাটাগরি দেখুন।',
            'categories' => $categories,
            'settings' => (new StorefrontService())->settings(),
        ]);
    }
}
