<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductReviewModel extends Model
{
    protected $table = 'product_reviews';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['customer_id','order_id','order_item_id','product_id','customer_name','rating','review_text','status','is_verified_purchase','admin_reply','replied_at','replied_by_admin_id'];
}
