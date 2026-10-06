<?php
namespace App\Models;
use CodeIgniter\Model;
class AdminUserModel extends Model
{
    protected $table = 'admin_users'; protected $returnType = 'array'; protected $useSoftDeletes = true; protected $useTimestamps = true;
    protected $allowedFields = ['role_id','name','email','mobile','password_hash','status','profile_image','last_login_at','last_login_ip'];
    public function withRole(): self { return $this->select('admin_users.*, admin_roles.name AS role_name, admin_roles.slug AS role_slug')->join('admin_roles', 'admin_roles.id = admin_users.role_id'); }
}
