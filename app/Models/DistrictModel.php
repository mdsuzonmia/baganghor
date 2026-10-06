<?php
namespace App\Models; use CodeIgniter\Model;
class DistrictModel extends Model{protected $table='bd_districts';protected $returnType='array';protected $allowedFields=['division_name','name_en','name_bn','sort_order','status'];}
