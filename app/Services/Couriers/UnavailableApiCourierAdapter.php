<?php
namespace App\Services\Couriers;

class UnavailableApiCourierAdapter implements CourierAdapterInterface
{
    public function book(array $order, array $shipment, array $provider): array
    {
        throw new \RuntimeException($provider['name'].' API booking is not configured. Use manual booking after creating the parcel in the merchant panel.');
    }
    public function tracking(array $shipment, array $provider): ?array { return null; }
}
