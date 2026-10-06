<?php
namespace App\Models;
use CodeIgniter\Model;
class ActivityLogModel extends Model
{
    protected $table = 'activity_logs'; protected $returnType = 'array'; protected $useTimestamps = false;
    protected $allowedFields = ['admin_user_id','action','module','description','ip_address','user_agent','metadata','created_at'];
}
