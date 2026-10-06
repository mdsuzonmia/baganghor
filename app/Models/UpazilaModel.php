<?php
namespace App\Models; use CodeIgniter\Model;
class UpazilaModel extends Model{protected $table='bd_upazilas';protected $returnType='array';protected $allowedFields=['district_id','name_en','name_bn','sort_order','status'];}
