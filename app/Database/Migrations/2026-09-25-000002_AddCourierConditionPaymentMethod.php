<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class AddCourierConditionPaymentMethod extends Migration
{
    public function up(): void
    {
        if (!$this->db->table('payment_methods')->where('code','courier_condition')->get()->getRowArray()) {
            $now=date('Y-m-d H:i:s');
            $this->db->table('payment_methods')->where('sort_order >=',1)->set('sort_order','sort_order + 1',false)->update();
            $this->db->table('payment_methods')->insert(['name'=>'Courier Condition','code'=>'courier_condition','type'=>'courier_condition','instructions'=>'আপনার অর্ডারটি কুরিয়ার Condition-এর মাধ্যমে পাঠানো হবে। পণ্য নিকটস্থ কুরিয়ার শাখায় পৌঁছালে কুরিয়ার থেকে যোগাযোগ করা হবে। পণ্য গ্রহণের সময় পণ্যের মূল্য ও প্রযোজ্য কুরিয়ার/Condition চার্জ পরিশোধ করতে হবে।','status'=>'active','sort_order'=>1,'created_at'=>$now,'updated_at'=>$now]);
        }
    }
    public function down(): void { $this->db->table('payment_methods')->where('code','courier_condition')->delete(); }
}
