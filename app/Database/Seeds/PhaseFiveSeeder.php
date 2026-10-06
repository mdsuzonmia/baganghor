<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PhaseFiveSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        foreach ([
            ['code'=>'manual','name'=>'Manual / Local Delivery','status'=>'active'],
            ['code'=>'steadfast','name'=>'Steadfast','status'=>'active'],
            ['code'=>'pathao','name'=>'Pathao','status'=>'active'],
            ['code'=>'redx','name'=>'RedX','status'=>'active'],
        ] as $provider) {
            if (!$this->db->table('courier_providers')->where('code',$provider['code'])->get()->getRowArray())
                $this->db->table('courier_providers')->insert($provider+['booking_mode'=>'manual','api_enabled'=>0,'created_at'=>$now,'updated_at'=>$now]);
        }
        foreach ([
            ['name'=>'CellFin','code'=>'cellfin','type'=>'manual_mobile_banking','sort_order'=>3],
            ['name'=>'DBBL Bank Transfer','code'=>'dbbl_bank','type'=>'manual_bank_transfer','sort_order'=>4],
        ] as $method) {
            if (!$this->db->table('payment_methods')->where('code',$method['code'])->get()->getRowArray())
                $this->db->table('payment_methods')->insert($method+['status'=>'inactive','created_at'=>$now,'updated_at'=>$now]);
        }
        foreach (['shipments.view'=>'View Shipments','shipments.manage'=>'Manage Shipments','sms.manage'=>'Manage SMS'] as $slug=>$name) {
            $permission=$this->db->table('admin_permissions')->where('slug',$slug)->get()->getRowArray();
            if (!$permission) {
                $this->db->table('admin_permissions')->insert(['name'=>$name,'slug'=>$slug,'module'=>explode('.',$slug)[0],'description'=>$name,'created_at'=>$now,'updated_at'=>$now]);
                $permission=$this->db->table('admin_permissions')->where('slug',$slug)->get()->getRowArray();
            }
            foreach (['admin','manager'] as $roleSlug) {
                $role=$this->db->table('admin_roles')->where('slug',$roleSlug)->get()->getRowArray();
                if ($role && !$this->db->table('admin_role_permissions')->where(['role_id'=>$role['id'],'permission_id'=>$permission['id']])->get()->getRowArray())
                    $this->db->table('admin_role_permissions')->insert(['role_id'=>$role['id'],'permission_id'=>$permission['id'],'created_at'=>$now]);
            }
        }
        foreach (['enabled'=>'0','placed'=>'আপনার অর্ডার {order_no} গ্রহণ করা হয়েছে। ট্র্যাক: {track_url}','confirmed'=>'আপনার অর্ডার {order_no} নিশ্চিত করা হয়েছে।','shipped'=>'আপনার অর্ডার {order_no} পাঠানো হয়েছে। ট্র্যাক: {track_url}','delivered'=>'আপনার অর্ডার {order_no} ডেলিভারি সম্পন্ন হয়েছে।','cancelled'=>'আপনার অর্ডার {order_no} বাতিল করা হয়েছে।','shipment_booked'=>'আপনার অর্ডার {order_no} কুরিয়ারে বুক হয়েছে। ট্র্যাক: {track_url}'] as $key=>$value) {
            if (!$this->db->table('settings')->where(['group_name'=>'sms','setting_key'=>$key])->get()->getRowArray())
                $this->db->table('settings')->insert(['group_name'=>'sms','setting_key'=>$key,'setting_value'=>$value,'setting_type'=>'string','is_public'=>0,'created_at'=>$now,'updated_at'=>$now]);
        }
    }
}
