<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;
class CatalogPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $definitions=['categories.view'=>['View Categories','categories'],'categories.manage'=>['Manage Categories','categories'],'products.view'=>['View Products','products'],'products.manage'=>['Manage Products','products'],'inventory.view'=>['View Inventory','inventory'],'inventory.adjust'=>['Adjust Inventory','inventory'],'packages.view'=>['View Packages','packages'],'packages.manage'=>['Manage Packages','packages']];$now=date('Y-m-d H:i:s');
        foreach($definitions as $slug=>[$name,$module])if(!$this->db->table('admin_permissions')->where('slug',$slug)->get()->getRowArray())$this->db->table('admin_permissions')->insert(compact('name','slug','module')+['description'=>$name,'created_at'=>$now,'updated_at'=>$now]);
        $roleRules=['admin'=>array_keys($definitions),'manager'=>array_keys($definitions),'staff'=>['categories.view','products.view','inventory.view','packages.view']];
        foreach($roleRules as $roleSlug=>$slugs){$role=$this->db->table('admin_roles')->where('slug',$roleSlug)->get()->getRowArray();if(!$role)continue;foreach($slugs as $slug){$permission=$this->db->table('admin_permissions')->where('slug',$slug)->get()->getRowArray();if(!$this->db->table('admin_role_permissions')->where(['role_id'=>$role['id'],'permission_id'=>$permission['id']])->get()->getRowArray())$this->db->table('admin_role_permissions')->insert(['role_id'=>$role['id'],'permission_id'=>$permission['id'],'created_at'=>$now]);}}
    }
}
