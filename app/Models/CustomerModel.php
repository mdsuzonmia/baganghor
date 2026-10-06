<?php
namespace App\Models;
use CodeIgniter\Model;
class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['customer_code','full_name','mobile','email','password_hash','status','email_verified_at','mobile_verified_at','last_login_at','last_login_ip','remember_token'];
}
