<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductPackageItemModel extends Model
{
    protected $table='product_package_items'; protected $returnType='array'; protected $useTimestamps=false;
    protected $allowedFields=['package_id','product_id','variant_id','quantity','sort_order','created_at'];
}
