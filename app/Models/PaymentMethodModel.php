<?php
namespace App\Models; use CodeIgniter\Model;
class PaymentMethodModel extends Model{protected $table='payment_methods';protected $returnType='array';protected $useTimestamps=true;protected $allowedFields=['name','code','type','instructions','account_number','account_name','status','sort_order'];}
