<?php
namespace App\Models;
use CodeIgniter\Model;
class ShipmentModel extends Model { protected $table='shipments'; protected $returnType='array'; protected $useTimestamps=true; protected $allowedFields=['order_id','courier_provider_id','status','tracking_number','tracking_url','courier_reference','cod_amount','package_weight_kg','admin_note','booked_at','picked_up_at','delivered_at','created_by_admin_id']; }
