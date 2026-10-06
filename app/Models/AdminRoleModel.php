<?php
namespace App\Models;
use CodeIgniter\Model;
class AdminRoleModel extends Model
{
    protected $table = 'admin_roles'; protected $returnType = 'array'; protected $useTimestamps = true;
    protected $allowedFields = ['name','slug','description','is_system'];
}
