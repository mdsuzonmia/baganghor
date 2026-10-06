<?php
namespace App\Services\Couriers;

interface CourierAdapterInterface
{
    /** Return a normalized booking reference and tracking number. Never mutate the order. */
    public function book(array $order, array $shipment, array $provider): array;
    /** Return normalized status/events, or null when the provider has no polling API. */
    public function tracking(array $shipment, array $provider): ?array;
}
