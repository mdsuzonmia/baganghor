<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreatePaymentMethodUpazilas extends Migration
{
    public function up(): void
    {
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'payment_method_id'=>['type'=>'BIGINT','unsigned'=>true],'upazila_id'=>['type'=>'INT','unsigned'=>true],'created_at'=>['type'=>'DATETIME','null'=>true]])->addKey('id',true)->addUniqueKey(['payment_method_id','upazila_id'])->addKey('upazila_id')->addForeignKey('payment_method_id','payment_methods','id','CASCADE','CASCADE')->addForeignKey('upazila_id','bd_upazilas','id','CASCADE','CASCADE')->createTable('payment_method_upazilas',true);
        $cod=$this->db->table('payment_methods')->where('code','cod')->get()->getRowArray();
        $bogura=$this->db->table('bd_districts')->where('name_en','Bogura')->get()->getRowArray();
        $sadar=$bogura?$this->db->table('bd_upazilas')->where(['district_id'=>$bogura['id'],'name_en'=>'Bogura Sadar'])->get()->getRowArray():null;
        if($cod&&$sadar)$this->db->table('payment_method_upazilas')->insert(['payment_method_id'=>$cod['id'],'upazila_id'=>$sadar['id'],'created_at'=>date('Y-m-d H:i:s')]);
    }
    public function down(): void {$this->forge->dropTable('payment_method_upazilas',true);}
}
