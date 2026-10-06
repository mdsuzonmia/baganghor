<?php
namespace App\Models; use CodeIgniter\Model;
class DeliveryZoneModel extends Model{protected $table='delivery_zones';protected $returnType='array';protected $useTimestamps=true;protected $allowedFields=['name','zone_type','district_id','upazila_id','delivery_charge','free_delivery_minimum','estimated_delivery_text','priority','status'];}
