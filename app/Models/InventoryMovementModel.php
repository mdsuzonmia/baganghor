<?php
namespace App\Models;
use CodeIgniter\Model;
class InventoryMovementModel extends Model
{
    protected $table='inventory_movements'; protected $returnType='array'; protected $useTimestamps=false;
    protected $allowedFields=['product_id','variant_id','movement_type','quantity','quantity_before','quantity_after','reference_type','reference_id','note','admin_user_id','created_at'];
}
