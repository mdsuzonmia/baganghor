<?php
namespace App\Models;
use CodeIgniter\Model;
class GuideModel extends Model {
 protected $table='guides'; protected $returnType='array'; protected $useTimestamps=true;
 protected $allowedFields=['category_id','title','slug','excerpt','content','featured_image','status','featured','published_at','views','reading_time','seo_title','seo_description'];
 public function published(): self {return $this->whereIn('guides.status',['published','scheduled'])->where('guides.published_at <=',date('Y-m-d H:i:s'));}
}
