<?php
namespace App\Models;
use CodeIgniter\Model;
class CourierProviderModel extends Model { protected $table='courier_providers'; protected $returnType='array'; protected $useTimestamps=true; protected $allowedFields=['code','name','booking_mode','api_enabled','status']; }
