<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductCategoryModel extends Model
{
    protected $table='product_categories'; protected $returnType='array'; protected $useSoftDeletes=true; protected $useTimestamps=true;
    protected $allowedFields=['parent_id','name','slug','description','image','icon','sort_order','status','seo_title','seo_description'];
}
