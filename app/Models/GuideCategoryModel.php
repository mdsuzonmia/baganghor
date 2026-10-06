<?php
namespace App\Models;
use CodeIgniter\Model;
class GuideCategoryModel extends Model {
 protected $table='guide_categories'; protected $returnType='array'; protected $useTimestamps=true;
 protected $allowedFields=['parent_id','name','slug','description','image','status','sort_order','seo_title','seo_description'];
}
