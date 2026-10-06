<?php
namespace Tests\Unit;
use App\Services\MobileNumberService; use CodeIgniter\Test\CIUnitTestCase;
final class MobileNumberServiceTest extends CIUnitTestCase
{
    public function testNormalizesSupportedBangladeshFormats(): void { $service=new MobileNumberService(); $this->assertSame('01712345678',$service->normalize('+8801712345678')); $this->assertSame('01712345678',$service->normalize('8801712345678')); $this->assertSame('01812345678',$service->normalize('01812-345678')); }
    public function testRejectsInvalidNumbers(): void { $service=new MobileNumberService(); $this->assertNull($service->normalize('01212345678')); $this->assertNull($service->normalize('0171234')); }
}
