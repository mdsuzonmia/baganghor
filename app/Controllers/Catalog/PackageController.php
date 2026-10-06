<?php
namespace App\Controllers\Catalog;

use App\Controllers\BaseController;
use App\Models\ProductPackageModel;
use App\Services\PackageService;
use App\Services\StorefrontService;
use CodeIgniter\Exceptions\PageNotFoundException;

class PackageController extends BaseController
{
    public function index()
    {
        $model=(new ProductPackageModel())->where('status','active');$sort=(string)$this->request->getGet('sort');
        match($sort){'price_low'=>$model->orderBy('package_price','ASC'),'price_high'=>$model->orderBy('package_price','DESC'),'newest'=>$model->orderBy('id','DESC'),default=>$model->orderBy('featured','DESC')->orderBy('sort_order')->orderBy('id','DESC')};
        $packages=$model->paginate(12);$store=new StorefrontService();
        return view('frontend/packages/index',['title'=>'গার্ডেনিং প্যাক','metaDescription'=>'চাষ শুরু করার প্রয়োজনীয় পণ্য একসাথে—Taharat Agro গার্ডেনিং প্যাক।','packages'=>$store->decoratePackages($packages),'pager'=>$model->pager,'sort'=>$sort,'settings'=>$store->settings()]);
    }
    public function show(string $slug)
    {
        $package=(new ProductPackageModel())->where(['slug'=>$slug,'status'=>'active'])->first();if(!$package)throw PageNotFoundException::forPageNotFound();
        $items=db_connect()->table('product_package_items')->select('product_package_items.quantity,products.name AS product_name,products.slug AS product_slug,products.main_image,product_variants.variant_name')->join('products',"products.id=product_package_items.product_id AND products.status='active' AND products.deleted_at IS NULL")->join('product_variants','product_variants.id=product_package_items.variant_id','left')->where('product_package_items.package_id',$package['id'])->orderBy('product_package_items.sort_order')->get()->getResultArray();
        $package['available']=(new PackageService())->getPackageAvailableQuantity((int)$package['id']);$package['savings']=max(0,(float)$package['regular_total']-(float)$package['package_price']);$store=new StorefrontService();
        return view('frontend/packages/show',['title'=>$package['seo_title']?:$package['name'],'metaDescription'=>$package['seo_description']?:$package['short_description'],'ogImage'=>$package['main_image']?:null,'package'=>$package,'items'=>$items,'settings'=>$store->settings()]);
    }
}
