<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class AddDeliveryChargePaymentToOrders extends Migration
{
    public function up(): void { $this->forge->addColumn('orders', ['delivery_charge_paid_at'=>['type'=>'DATETIME','null'=>true,'after'=>'delivery_charge'],'delivery_charge_paid_by'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true,'after'=>'delivery_charge_paid_at']]); }
    public function down(): void { $this->forge->dropColumn('orders', ['delivery_charge_paid_at','delivery_charge_paid_by']); }
}
