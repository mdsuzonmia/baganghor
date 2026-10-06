<?php
namespace App\Models; use CodeIgniter\Model;
class PaymentModel extends Model{protected $table='payments';protected $returnType='array';protected $useTimestamps=true;protected $allowedFields=['order_id','payment_method_id','amount','currency','status','transaction_id','sender_mobile','admin_note','verified_by','verified_at'];}
