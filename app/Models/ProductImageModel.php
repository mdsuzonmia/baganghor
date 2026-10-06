<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductImageModel extends Model
{
    protected $table='product_images'; protected $returnType='array'; protected $useTimestamps=false;
    protected $allowedFields=['product_id','image_path','alt_text','sort_order','is_primary','created_at'];
}
