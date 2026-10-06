<?php
namespace App\Services\Couriers;

class ManualCourierAdapter implements CourierAdapterInterface
{
    public function book(array $order, array $shipment, array $provider): array
    {
        return ['reference'=>$shipment['courier_reference']?:$shipment['tracking_number']?:$order['order_no'],'tracking_number'=>$shipment['tracking_number']];
    }
    public function tracking(array $shipment, array $provider): ?array { return null; }
}
