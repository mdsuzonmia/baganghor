<?php
namespace App\Controllers\Catalog;

use App\Controllers\BaseController;
use App\Models\ProductCategoryModel;
use App\Models\ProductImageModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Models\ProductReviewModel;
use App\Services\ReviewService;
use App\Services\StorefrontService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ProductController extends BaseController
{
    public function index(){ return $this->listing(); }
    public function category(string $slug){$category=(new ProductCategoryModel())->where(['slug'=>$slug,'status'=>'active'])->first();if(!$category)throw PageNotFoundException::forPageNotFound();return $this->listing($category);}

    private function listing(?array $category=null)
    {
        $model=(new ProductModel())->withCategory()->where('products.status','active');
        $categorySlug=trim((string)$this->request->getGet('category'));
        if(!$category && $categorySlug!=='')$category=(new ProductCategoryModel())->where(['slug'=>$categorySlug,'status'=>'active'])->first();
        if($category)$model->where('products.category_id',$category['id']);
        $db=db_connect();$variantTable=$db->prefixTable('product_variants');$productTable=$db->prefixTable('products');
        $stock=(string)$this->request->getGet('stock');
        if($stock==='in')$model->where("(({$productTable}.stock_type='simple' AND ({$productTable}.manage_stock=0 OR {$productTable}.allow_backorder=1 OR {$productTable}.stock_quantity>0)) OR ({$productTable}.stock_type='variant' AND EXISTS (SELECT 1 FROM {$variantTable} pv WHERE pv.product_id={$productTable}.id AND pv.status='active' AND pv.deleted_at IS NULL AND pv.stock_quantity>0)))",null,false);
        if($stock==='out')$model->where("(({$productTable}.stock_type='simple' AND {$productTable}.manage_stock=1 AND {$productTable}.allow_backorder=0 AND {$productTable}.stock_quantity<=0) OR ({$productTable}.stock_type='variant' AND NOT EXISTS (SELECT 1 FROM {$variantTable} pv WHERE pv.product_id={$productTable}.id AND pv.status='active' AND pv.deleted_at IS NULL AND pv.stock_quantity>0)))",null,false);
        if($this->request->getGet('sale')==='1')$model->where("(({$productTable}.stock_type='simple' AND {$productTable}.sale_price IS NOT NULL AND {$productTable}.sale_price < {$productTable}.regular_price) OR ({$productTable}.stock_type='variant' AND EXISTS (SELECT 1 FROM {$variantTable} pvs WHERE pvs.product_id={$productTable}.id AND pvs.status='active' AND pvs.deleted_at IS NULL AND pvs.sale_price IS NOT NULL AND pvs.sale_price < pvs.regular_price)))",null,false);
        if($this->request->getGet('featured')==='1')$model->where('products.featured',1);
        $type=(string)$this->request->getGet('type');if(in_array($type,['simple','variant'],true))$model->where('products.stock_type',$type);
        $sort=(string)$this->request->getGet('sort');
        $price="CASE WHEN {$productTable}.stock_type='variant' THEN COALESCE((SELECT MIN(CASE WHEN pv2.sale_price IS NOT NULL AND pv2.sale_price <= pv2.regular_price THEN pv2.sale_price ELSE pv2.regular_price END) FROM {$variantTable} pv2 WHERE pv2.product_id={$productTable}.id AND pv2.status='active' AND pv2.deleted_at IS NULL),0) ELSE CASE WHEN {$productTable}.sale_price IS NOT NULL AND {$productTable}.sale_price <= {$productTable}.regular_price THEN {$productTable}.sale_price ELSE {$productTable}.regular_price END END";
        match($sort){'price_low'=>$model->orderBy($price,'ASC',false),'price_high'=>$model->orderBy($price,'DESC',false),'name'=>$model->orderBy('products.name','ASC'),'newest'=>$model->orderBy('products.id','DESC'),default=>$model->orderBy('products.featured','DESC')->orderBy('products.sort_order')->orderBy('products.id','DESC')};
        $products=$model->paginate(12);$store=new StorefrontService();$title=$category?($category['seo_title']?:$category['name']):'সকল পণ্য';
        return view('frontend/products/index',['title'=>$title,'metaDescription'=>$category?($category['seo_description']?:$category['description']):'Taharat Agro-এর জৈব সার, গ্রো ব্যাগ ও বাগানের প্রয়োজনীয় পণ্য দেখুন।','category'=>$category,'children'=>$category?$store->categories(null,(int)$category['id']):[],'categories'=>(new ProductCategoryModel())->where('status','active')->orderBy('sort_order')->findAll(),'products'=>$store->decorateProducts($products),'pager'=>$model->pager,'filters'=>['category'=>$category['slug']??$categorySlug,'stock'=>$stock,'sort'=>$sort,'sale'=>$this->request->getGet('sale'),'featured'=>$this->request->getGet('featured'),'type'=>$type],'settings'=>$store->settings()]);
    }

    public function show(string $slug)
    {
        $product=(new ProductModel())->withCategory()->where(['products.slug'=>$slug,'products.status'=>'active'])->first();if(!$product)throw PageNotFoundException::forPageNotFound();
        $variants=(new ProductVariantModel())->where(['product_id'=>$product['id'],'status'=>'active'])->orderBy('sort_order')->findAll();
        $images=(new ProductImageModel())->where('product_id',$product['id'])->orderBy('is_primary','DESC')->orderBy('sort_order')->findAll();
        $relatedModel=(new ProductModel())->withCategory()->where('products.status','active')->where('products.id !=',$product['id']);if($product['category_id'])$relatedModel->where('products.category_id',$product['category_id']);
        $store=new StorefrontService();$decorated=$store->decorateProducts([$product])[0];$guides=db_connect()->table('guide_products gp')->select('g.*')->join('guides g','g.id=gp.guide_id')->where('gp.product_id',$product['id'])->whereIn('g.status',['published','scheduled'])->where('g.published_at <=',date('Y-m-d H:i:s'))->orderBy('g.published_at','DESC')->limit(3)->get()->getResultArray();
        $reviewModel=(new ProductReviewModel())->where(['product_id'=>$product['id'],'status'=>'approved']);$reviewSort=(string)$this->request->getGet('review_sort');
        match($reviewSort){'highest'=>$reviewModel->orderBy('rating','DESC')->orderBy('id','DESC'),'lowest'=>$reviewModel->orderBy('rating','ASC')->orderBy('id','DESC'),default=>$reviewModel->orderBy('id','DESC')};
        $reviews=$reviewModel->paginate(8,'reviews');$reviewIds=array_column($reviews,'id');$reviewImages=[];
        if($reviewIds)foreach(db_connect()->table('product_review_images')->whereIn('review_id',$reviewIds)->orderBy('sort_order')->get()->getResultArray() as $image)$reviewImages[(int)$image['review_id']][]=$image;
        $eligibleReviewItem=null;
        if(session('customer_id'))$eligibleReviewItem=db_connect()->table('order_items oi')->select('oi.id, o.public_token, o.order_no')->join('orders o','o.id=oi.order_id')->join('product_reviews pr','pr.order_item_id=oi.id','left')->where(['oi.product_id'=>$product['id'],'oi.item_type'=>'product','o.customer_id'=>session('customer_id'),'pr.id'=>null])->whereIn('o.order_status',ReviewService::REVIEWABLE_ORDER_STATUSES)->orderBy('o.id','DESC')->get()->getRowArray();
        return view('frontend/products/show',['title'=>$product['seo_title']?:$product['name'],'metaDescription'=>$product['seo_description']?:$product['short_description'],'ogImage'=>$product['main_image']?:null,'product'=>$decorated,'guides'=>$guides,'variants'=>$variants,'images'=>$images,'related'=>$store->decorateProducts($relatedModel->orderBy('products.stock_quantity','DESC')->orderBy('products.featured','DESC')->findAll(4)),'reviews'=>$reviews,'reviewImages'=>$reviewImages,'reviewStats'=>(new ReviewService())->productStats((int)$product['id']),'reviewPager'=>$reviewModel->pager,'reviewSort'=>$reviewSort,'eligibleReviewItem'=>$eligibleReviewItem,'settings'=>$store->settings()]);
    }
}
