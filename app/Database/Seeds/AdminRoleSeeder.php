<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;
class AdminRoleSeeder extends Seeder
{
    public function run(): void { $now=date('Y-m-d H:i:s'); foreach([['Super Admin','super_admin'],['Admin','admin'],['Manager','manager'],['Staff','staff']] as [$name,$slug]) { $exists=$this->db->table('admin_roles')->where('slug',$slug)->get()->getRowArray(); if(!$exists)$this->db->table('admin_roles')->insert(['name'=>$name,'slug'=>$slug,'description'=>$name.' role','is_system'=>1,'created_at'=>$now,'updated_at'=>$now]); } }
}
