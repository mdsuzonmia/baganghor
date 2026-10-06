<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;
class SuperAdminSeeder extends Seeder
{
    public function run(): void { $configuredEmail=env('TAHARAT_ADMIN_EMAIL'); $configuredPassword=env('TAHARAT_ADMIN_PASSWORD'); if(ENVIRONMENT==='production' && (!$configuredEmail || !$configuredPassword))throw new \RuntimeException('Set TAHARAT_ADMIN_EMAIL and TAHARAT_ADMIN_PASSWORD before seeding production.'); $email=(string)($configuredEmail?:'admin@taharatagro.local'); $password=(string)($configuredPassword?:'ChangeMe123!'); $role=$this->db->table('admin_roles')->where('slug','super_admin')->get()->getRowArray(); if(!$role)throw new \RuntimeException('Run AdminRoleSeeder first.'); if(!$this->db->table('admin_users')->where('email',$email)->get()->getRowArray())$this->db->table('admin_users')->insert(['role_id'=>$role['id'],'name'=>(string)(env('TAHARAT_ADMIN_NAME')?:'Taharat Agro Admin'),'email'=>strtolower($email),'password_hash'=>password_hash($password,PASSWORD_DEFAULT),'status'=>'active','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]); echo "Initial admin: {$email}. Change its password immediately.\n"; }
}
