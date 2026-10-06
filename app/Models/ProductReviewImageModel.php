<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductReviewImageModel extends Model
{
    protected $table = 'product_review_images';
    protected $returnType = 'array';
    protected $allowedFields = ['review_id','image_path','sort_order','created_at'];
}
