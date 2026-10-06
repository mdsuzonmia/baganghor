<?php
namespace App\Models; use CodeIgniter\Model;
class OrderItemComponentModel extends Model{protected $table='order_item_components';protected $returnType='array';protected $allowedFields=['order_item_id','product_id','variant_id','product_name','variant_name','sku','quantity_per_package','total_quantity','purchase_cost_snapshot','stock_deducted','created_at'];}
