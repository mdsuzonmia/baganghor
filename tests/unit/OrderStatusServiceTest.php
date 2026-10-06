<?php

namespace Tests\Unit;

use App\Services\OrderStatusService;
use CodeIgniter\Test\CIUnitTestCase;

class OrderStatusServiceTest extends CIUnitTestCase
{
    public function testTransitionsOnlyMoveForwardOrCancelBeforeShipping(): void
    {
        $service = new OrderStatusService();
        $this->assertTrue($service->can('pending', 'confirmed'));
        $this->assertTrue($service->can('pending', 'cancelled'));
        $this->assertTrue($service->can('confirmed', 'processing'));
        $this->assertTrue($service->can('processing', 'shipped'));
        $this->assertTrue($service->can('shipped', 'delivered'));
        $this->assertFalse($service->can('shipped', 'cancelled'));
        $this->assertFalse($service->can('delivered', 'pending'));
        $this->assertFalse($service->can('cancelled', 'confirmed'));
        $this->assertSame([], $service->options('delivered'));
    }
}
