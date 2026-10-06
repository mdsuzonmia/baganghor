<?php
namespace Tests\Unit;

use App\Services\Couriers\CourierAdapterFactory;
use App\Services\Couriers\ManualCourierAdapter;
use App\Services\Couriers\UnavailableApiCourierAdapter;
use App\Services\ShipmentService;
use CodeIgniter\Test\CIUnitTestCase;

class FulfillmentTest extends CIUnitTestCase
{
    public function testManualAdapterUsesRecordedTrackingWithoutNetwork(): void
    {
        $adapter=(new CourierAdapterFactory())->forProvider(['booking_mode'=>'manual','name'=>'Steadfast']);
        $this->assertInstanceOf(ManualCourierAdapter::class,$adapter);
        $this->assertSame(['reference'=>'SF-123','tracking_number'=>'SF-123'],$adapter->book(['order_no'=>'TAH-1'],['courier_reference'=>null,'tracking_number'=>'SF-123'],['name'=>'Steadfast']));
        $this->assertNull($adapter->tracking([],[]));
    }

    public function testUnconfiguredApiFailsClosed(): void
    {
        $adapter=(new CourierAdapterFactory())->forProvider(['booking_mode'=>'api','name'=>'Pathao']);
        $this->assertInstanceOf(UnavailableApiCourierAdapter::class,$adapter);
        $this->expectException(\RuntimeException::class);
        $adapter->book([],[],['name'=>'Pathao']);
    }

    public function testShipmentStatusTransitionsAreRestricted(): void
    {
        $service=new ShipmentService();
        $this->assertSame(['booked','cancelled'],$service->options('prepared'));
        $this->assertContains('delivered',$service->options('in_transit'));
        $this->assertNotContains('delivered',$service->options('prepared'));
        $this->assertSame([],$service->options('delivered'));
    }
}
