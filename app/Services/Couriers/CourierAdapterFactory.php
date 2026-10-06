<?php
namespace App\Services\Couriers;

class CourierAdapterFactory
{
    public function forProvider(array $provider): CourierAdapterInterface
    {
        return $provider['booking_mode']==='manual' ? new ManualCourierAdapter() : new UnavailableApiCourierAdapter();
    }
}
