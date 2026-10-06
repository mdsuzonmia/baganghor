<?php
namespace App\Models; use CodeIgniter\Model;
class OrderStatusHistoryModel extends Model{protected $table='order_status_history';protected $returnType='array';protected $allowedFields=['order_id','from_status','to_status','note','changed_by_admin_id','changed_by_customer_id','created_at'];}
