<?php
namespace App\Models;
use CodeIgniter\Model;
class SmsMessageModel extends Model { protected $table='sms_messages'; protected $returnType='array'; protected $useTimestamps=true; protected $allowedFields=['order_id','event_key','event_type','recipient_mobile','message','status','attempts','provider_request_id','last_error','next_attempt_at','sent_at']; }
