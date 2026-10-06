<?php
namespace App\Models;
use CodeIgniter\Model;
class LoginAttemptModel extends Model
{
    protected $table = 'login_attempts'; protected $returnType = 'array'; protected $useTimestamps = false;
    protected $allowedFields = ['login_type','identifier','ip_address','attempted_at','success'];
}
