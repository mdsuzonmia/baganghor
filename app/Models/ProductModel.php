<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductModel extends Model
{
    protected $table='products'; protected $returnType='array'; protected $useSoftDeletes=true; protected $useTimestamps=true;
    protected $allowedFields=['category_id','product_code','name','slug','short_description','description','regular_price','sale_price','purchase_cost','sku','stock_type','stock_quantity','low_stock_threshold','manage_stock','allow_backorder','weight','weight_unit','unit','featured','status','seo_title','seo_description','seo_keywords','main_image','sort_order'];
    public function withCategory(): self { return $this->select('products.*, product_categories.name AS category_name, product_categories.slug AS category_slug')->join('product_categories','product_categories.id=products.category_id','left'); }
}
