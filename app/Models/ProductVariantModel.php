<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductVariantModel extends Model
{
    protected $table='product_variants'; protected $returnType='array'; protected $useSoftDeletes=true; protected $useTimestamps=true;
    protected $allowedFields=['product_id','variant_name','sku','regular_price','sale_price','purchase_cost','stock_quantity','low_stock_threshold','weight','weight_unit','sort_order','status'];
}
