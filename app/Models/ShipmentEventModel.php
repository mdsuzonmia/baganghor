<?php
namespace App\Models;
use CodeIgniter\Model;
class ShipmentEventModel extends Model { protected $table='shipment_events'; protected $returnType='array'; protected $allowedFields=['shipment_id','from_status','to_status','tracking_note','source','changed_by_admin_id','created_at']; }
