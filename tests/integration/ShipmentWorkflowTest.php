<?php
namespace Tests\Integration;

use App\Models\OrderModel;
use App\Models\ShipmentModel;
use App\Services\OrderStatusService;
use App\Services\ShipmentService;
use App\Services\SmsService;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
class ShipmentWorkflowTest extends CIUnitTestCase
{
    public function testManualBookingShipmentDeliveryAndIdempotentSmsOutbox(): void
    {
        try { $db=db_connect(); $db->initialize(); }
        catch(\Throwable $e) { $this->markTestSkipped('Integration database is unavailable.'); }
        foreach(['orders','shipments','courier_providers','sms_messages'] as $table) if(!$db->tableExists($table)) $this->markTestSkipped('Phase 5 migrations are not applied.');
        $provider=$db->table('courier_providers')->where('code','manual')->get()->getRowArray();
        $district=$db->table('bd_districts')->get()->getRowArray();
        $upazila=$district?$db->table('bd_upazilas')->where('district_id',$district['id'])->get()->getRowArray():null;
        $payment=$db->table('payment_methods')->where('code','cod')->get()->getRowArray();
        if(!$provider || !$upazila || !$payment) $this->markTestSkipped('Phase 4/5 seed data is missing.');
        $db->transException(true)->transBegin();
        try {
            $db->table('settings')->where(['group_name'=>'sms','setting_key'=>'enabled'])->update(['setting_value'=>'0']);
            $now=date('Y-m-d H:i:s');
            $db->table('orders')->insert(['order_no'=>'TEST-'.strtoupper(bin2hex(random_bytes(6))),'public_token'=>bin2hex(random_bytes(32)),'checkout_token'=>bin2hex(random_bytes(32)),'customer_name'=>'Integration Test','customer_mobile'=>'01712345678','district_id'=>$district['id'],'district_name'=>$district['name_bn'],'upazila_id'=>$upazila['id'],'upazila_name'=>$upazila['name_bn'],'address_line'=>'Test address only','subtotal'=>100,'discount'=>0,'delivery_charge'=>50,'grand_total'=>150,'payment_method_id'=>$payment['id'],'payment_method_code'=>'cod','payment_status'=>'unpaid','order_status'=>'confirmed','source'=>'test','placed_at'=>$now,'created_at'=>$now,'updated_at'=>$now]);
            $orderId=(int)$db->insertID();
            $sms=new SmsService();$sms->trigger($orderId,'placed');$sms->trigger($orderId,'placed');
            $this->assertSame(1,$db->table('sms_messages')->where(['order_id'=>$orderId,'event_type'=>'placed'])->countAllResults());
            $this->assertSame('suppressed',$db->table('sms_messages')->where('order_id',$orderId)->get()->getRow('status'));
            $shipment=(new ShipmentService())->prepare($orderId,['courier_provider_id'=>$provider['id'],'tracking_number'=>'TEST-TRACK-1']);
            $this->assertSame('prepared',$shipment['status']);
            $shipment=(new ShipmentService())->book((int)$shipment['id']);
            $this->assertSame('booked',$shipment['status']);
            $this->assertSame('150.00',$shipment['cod_amount']);
            (new OrderStatusService())->transition($orderId,'processing');
            (new OrderStatusService())->transition($orderId,'shipped');
            (new ShipmentService())->transition((int)$shipment['id'],'in_transit','On the way');
            (new ShipmentService())->transition((int)$shipment['id'],'delivered','Received by customer');
            $this->assertSame('delivered',(new OrderModel())->find($orderId)['order_status']);
            $this->assertSame('delivered',(new ShipmentModel())->find($shipment['id'])['status']);
            $this->assertSame(4,$db->table('shipment_events')->where('shipment_id',$shipment['id'])->countAllResults());
        } finally { $db->transRollback(); }
    }
}
