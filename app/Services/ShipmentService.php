<?php
namespace App\Services;

use App\Models\CourierProviderModel;
use App\Models\OrderModel;
use App\Models\ShipmentModel;
use App\Services\Couriers\CourierAdapterFactory;

class ShipmentService
{
    private array $flow=['prepared'=>['booked','cancelled'],'booked'=>['picked_up','in_transit','failed','cancelled'],'picked_up'=>['in_transit','failed'],'in_transit'=>['delivered','failed'],'failed'=>[],'delivered'=>[],'cancelled'=>[]];
    public function options(string $status): array { return $this->flow[$status]??[]; }

    public function prepare(int $orderId,array $input): array
    {
        $db=db_connect();$db->transException(true)->transStart();
        $order=$db->query('SELECT * FROM '.$db->prefixTable('orders').' WHERE id=? FOR UPDATE',[$orderId])->getRowArray();
        if (!$order || in_array($order['order_status'],['pending','cancelled','delivered'],true)) throw new \RuntimeException('Confirm the order before preparing a shipment.');
        $provider=(new CourierProviderModel())->where(['id'=>(int)($input['courier_provider_id']??0),'status'=>'active'])->first();
        if (!$provider) throw new \InvalidArgumentException('Select an active courier.');
        $existing=(new ShipmentModel())->where('order_id',$orderId)->first();
        if ($existing && $existing['status']!=='prepared') throw new \RuntimeException('This shipment has already been booked.');
        $tracking=trim((string)($input['tracking_number']??''));
        $reference=trim((string)($input['courier_reference']??''));
        if (mb_strlen($tracking)>120 || mb_strlen($reference)>120) throw new \InvalidArgumentException('Tracking or reference is too long.');
        $url=$this->safeTrackingUrl((string)($input['tracking_url']??''),$provider['code']);
        $weight=$input['package_weight_kg']??'';
        if ($weight!=='' && (!is_numeric($weight) || (float)$weight<=0 || (float)$weight>1000)) throw new \InvalidArgumentException('Enter a valid package weight.');
        $deliveryPaid=!empty($order['delivery_charge_paid_at'])?(float)$order['delivery_charge']:0;
        $payOnReceipt=in_array($order['payment_method_code'],['cod','courier_condition'],true);
        $cod=$payOnReceipt && $order['payment_status']!=='paid'?max(0,(float)$order['grand_total']-$deliveryPaid):0;
        $data=['order_id'=>$orderId,'courier_provider_id'=>$provider['id'],'status'=>'prepared','tracking_number'=>$tracking?:null,'tracking_url'=>$url,'courier_reference'=>$reference?:null,'cod_amount'=>$cod,'package_weight_kg'=>$weight===''?null:(float)$weight,'admin_note'=>trim((string)($input['admin_note']??''))?:null,'created_by_admin_id'=>session('admin_id')?:null];
        $model=new ShipmentModel();
        if ($existing) {$model->update($existing['id'],$data);$id=(int)$existing['id'];}
        else {$id=(int)$model->insert($data,true);$db->table('shipment_events')->insert(['shipment_id'=>$id,'to_status'=>'prepared','tracking_note'=>'Shipment prepared','source'=>'admin','changed_by_admin_id'=>session('admin_id')?:null,'created_at'=>date('Y-m-d H:i:s')]);}
        $db->transComplete();return $model->find($id);
    }

    public function book(int $shipmentId): array
    {
        $db=db_connect();$db->transException(true)->transStart();
        $shipment=$db->query('SELECT * FROM '.$db->prefixTable('shipments').' WHERE id=? FOR UPDATE',[$shipmentId])->getRowArray();
        if (!$shipment || $shipment['status']!=='prepared') throw new \RuntimeException('Only a prepared shipment can be booked.');
        $provider=(new CourierProviderModel())->find($shipment['courier_provider_id']);
        $order=(new OrderModel())->find($shipment['order_id']);
        if (!$provider || !$order || in_array($order['order_status'],['pending','cancelled','delivered'],true)) throw new \RuntimeException('Order or courier is unavailable.');
        if (!in_array($order['payment_method_code'],['cod','courier_condition'],true) && $order['payment_status']!=='paid') throw new \RuntimeException('Verify the prepaid payment before booking a courier.');
        if ($provider['code']!=='manual' && !$shipment['tracking_number']) throw new \RuntimeException('Enter the courier tracking number from the merchant panel first.');
        $result=(new CourierAdapterFactory())->forProvider($provider)->book($order,$shipment,$provider);
        $now=date('Y-m-d H:i:s');
        $db->table('shipments')->where('id',$shipmentId)->update(['status'=>'booked','courier_reference'=>$result['reference'],'tracking_number'=>$result['tracking_number'],'booked_at'=>$now,'updated_at'=>$now]);
        $db->table('shipment_events')->insert(['shipment_id'=>$shipmentId,'from_status'=>'prepared','to_status'=>'booked','tracking_note'=>'Courier booking recorded','source'=>'admin','changed_by_admin_id'=>session('admin_id')?:null,'created_at'=>$now]);
        $db->transComplete();
        try{(new SmsService())->trigger((int)$order['id'],'shipment_booked',$shipmentId);}catch(\Throwable $e){log_message('error','Shipment SMS enqueue failed: {error}',['error'=>$e->getMessage()]);}
        return (new ShipmentModel())->find($shipmentId);
    }

    public function transition(int $shipmentId,string $to,?string $note=null): array
    {
        $db=db_connect();$db->transException(true)->transStart();
        $shipment=$db->query('SELECT * FROM '.$db->prefixTable('shipments').' WHERE id=? FOR UPDATE',[$shipmentId])->getRowArray();
        if (!$shipment || !in_array($to,$this->options($shipment['status']),true)) throw new \RuntimeException('This shipment status transition is not allowed.');
        if ($to==='delivered') {
            $order=(new OrderModel())->find($shipment['order_id']);
            if (!$order || $order['order_status']!=='shipped') throw new \RuntimeException('Mark the order shipped before completing delivery.');
        }
        $now=date('Y-m-d H:i:s');$changes=['status'=>$to,'updated_at'=>$now];
        if ($to==='picked_up') $changes['picked_up_at']=$now;
        if ($to==='delivered') $changes['delivered_at']=$now;
        $db->table('shipments')->where('id',$shipmentId)->update($changes);
        $db->table('shipment_events')->insert(['shipment_id'=>$shipmentId,'from_status'=>$shipment['status'],'to_status'=>$to,'tracking_note'=>trim((string)$note)?:null,'source'=>'admin','changed_by_admin_id'=>session('admin_id')?:null,'created_at'=>$now]);
        $db->transComplete();
        if ($to==='delivered') {
            $order=(new OrderModel())->find($shipment['order_id']);
            if ($order && $order['order_status']==='shipped') (new OrderStatusService())->transition((int)$order['id'],'delivered','Courier marked shipment delivered');
        }
        return (new ShipmentModel())->find($shipmentId);
    }

    private function safeTrackingUrl(string $url,string $providerCode): ?string
    {
        $url=trim($url);if($url==='')return null;
        if(mb_strlen($url)>500 || !filter_var($url,FILTER_VALIDATE_URL) || strtolower((string)parse_url($url,PHP_URL_SCHEME))!=='https') throw new \InvalidArgumentException('Tracking URL must be a valid HTTPS link.');
        $host=strtolower((string)parse_url($url,PHP_URL_HOST));
        $allowed=['steadfast'=>['steadfast.com.bd','www.steadfast.com.bd'],'pathao'=>['pathao.com','courier.pathao.com','merchant.pathao.com'],'redx'=>['redx.com.bd','www.redx.com.bd'],'manual'=>[]];
        if(!in_array($host,$allowed[$providerCode]??[],true)) throw new \InvalidArgumentException('Tracking URL must use the official courier domain.');
        return $url;
    }
}
