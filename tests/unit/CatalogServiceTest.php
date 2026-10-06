<?php
namespace Tests\Unit;
use App\Services\InventoryService; use App\Services\ProductService; use CodeIgniter\Test\CIUnitTestCase;
final class CatalogServiceTest extends CIUnitTestCase
{
    public function testEffectivePriceUsesValidSalePrice(): void { $service=new ProductService();$this->assertSame(80.0,$service->effectivePrice(['regular_price'=>100,'sale_price'=>80]));$this->assertSame(100.0,$service->effectivePrice(['regular_price'=>100,'sale_price'=>120]));$this->assertSame(100.0,$service->effectivePrice(['regular_price'=>100,'sale_price'=>null])); }
    public function testStockStatusBoundaries(): void { $service=new InventoryService();$this->assertSame('out_of_stock',$service->stockStatus(0,5));$this->assertSame('low_stock',$service->stockStatus(5,5));$this->assertSame('in_stock',$service->stockStatus(6,5)); }
}
