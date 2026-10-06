<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;
class SettingsSeeder extends Seeder
{
    public function run(): void { $values=['site_name'=>'Taharat Agro','site_tagline'=>'','site_logo'=>'','favicon'=>'','support_mobile'=>'','support_email'=>'','facebook_url'=>'','youtube_url'=>'','currency'=>'BDT','currency_symbol'=>'৳','timezone'=>'Asia/Dhaka','default_language'=>'bn']; foreach($values as $key=>$value)if(!$this->db->table('settings')->where(['group_name'=>'general','setting_key'=>$key])->get()->getRowArray())$this->db->table('settings')->insert(['group_name'=>'general','setting_key'=>$key,'setting_value'=>$value,'setting_type'=>'string','is_public'=>in_array($key,['site_name','site_tagline','currency','currency_symbol','timezone','default_language'],true),'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]); }
}
