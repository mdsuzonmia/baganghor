<?php
namespace App\Models;
use CodeIgniter\Model;
class SettingModel extends Model
{
    protected $table = 'settings'; protected $returnType = 'array'; protected $useTimestamps = true;
    protected $allowedFields = ['group_name','setting_key','setting_value','setting_type','is_public'];
}
