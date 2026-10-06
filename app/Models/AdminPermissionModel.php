<?php
namespace App\Models;
use CodeIgniter\Model;
class AdminPermissionModel extends Model
{
    protected $table = 'admin_permissions'; protected $returnType = 'array'; protected $useTimestamps = true;
    protected $allowedFields = ['name','slug','module','description'];
}
