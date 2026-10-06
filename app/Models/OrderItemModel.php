<?php
namespace App\Models; use CodeIgniter\Model;
class OrderItemModel extends Model{protected $table='order_items';protected $returnType='array';protected $allowedFields=['order_id','item_type','product_id','variant_id','package_id','product_name','variant_name','sku','image_path','quantity','unit_price','subtotal','purchase_cost_snapshot','stock_deducted','item_metadata','created_at'];}
