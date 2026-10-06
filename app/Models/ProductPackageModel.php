<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductPackageModel extends Model
{
    protected $table='product_packages'; protected $returnType='array'; protected $useSoftDeletes=true; protected $useTimestamps=true;
    protected $allowedFields=['name','slug','package_code','short_description','description','regular_total','package_price','main_image','featured','status','seo_title','seo_description','sort_order'];
}
