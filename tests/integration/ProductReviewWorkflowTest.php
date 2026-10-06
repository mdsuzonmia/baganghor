<?php
namespace Tests\Integration;

use App\Services\ImageUploadService;
use App\Services\ReviewService;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
class ProductReviewWorkflowTest extends CIUnitTestCase
{
    public function testVerifiedGuestReviewDuplicateModerationRatingAndImage(): void
    {
        try { $db=db_connect();$db->initialize(); } catch(\Throwable $e) { $this->markTestSkipped('Integration database unavailable.'); }
        foreach(['product_reviews','product_review_images'] as $table)if(!$db->tableExists($table))$this->markTestSkipped('Phase 6 migration is not applied.');
        $product=$db->table('products')->where('status','active')->get()->getRowArray();$district=$db->table('bd_districts')->get()->getRowArray();$upazila=$district?$db->table('bd_upazilas')->where('district_id',$district['id'])->get()->getRowArray():null;$payment=$db->table('payment_methods')->get()->getRowArray();
        if(!$product||!$upazila||!$payment)$this->markTestSkipped('Catalog/order reference data missing.');
        $token=bin2hex(random_bytes(32));$orderId=0;$reviewId=0;$uploaded=null;$source=tempnam(sys_get_temp_dir(),'review-test-');
        copy(FCPATH.'uploads/products/2026/09/162a5169eb896b34bd48ba8e7fa6fed4.png',$source);
        try {
            $now=date('Y-m-d H:i:s');$db->table('orders')->insert(['order_no'=>'REVIEW-'.strtoupper(bin2hex(random_bytes(5))),'public_token'=>$token,'checkout_token'=>bin2hex(random_bytes(32)),'customer_name'=>'Verified Reviewer','customer_mobile'=>'01712345678','district_id'=>$district['id'],'district_name'=>$district['name_en'],'upazila_id'=>$upazila['id'],'upazila_name'=>$upazila['name_en'],'address_line'=>'Review test address','subtotal'=>100,'discount'=>0,'delivery_charge'=>0,'grand_total'=>100,'payment_method_id'=>$payment['id'],'payment_method_code'=>$payment['code'],'payment_status'=>'paid','order_status'=>'confirmed','source'=>'test','placed_at'=>$now,'created_at'=>$now,'updated_at'=>$now]);$orderId=(int)$db->insertID();
            $db->table('order_items')->insert(['order_id'=>$orderId,'item_type'=>'product','product_id'=>$product['id'],'product_name'=>$product['name'],'quantity'=>1,'unit_price'=>100,'subtotal'=>100,'created_at'=>$now]);$itemId=(int)$db->insertID();
            $service=new ReviewService();$this->assertNull($service->eligibleItem($itemId,null,str_repeat('0',64)));$item=$service->eligibleItem($itemId,null,$token);$this->assertNotNull($item);
            $file=new UploadedFile($source,'review.png','image/png',UPLOAD_ERR_OK,true);$reviewId=$service->submit($item,5,'Excellent verified purchase review.',[$file]);
            $review=$db->table('product_reviews')->where('id',$reviewId)->get()->getRowArray();$this->assertSame('pending',$review['status']);$this->assertSame('1',$review['is_verified_purchase']);
            $image=$db->table('product_review_images')->where('review_id',$reviewId)->get()->getRowArray();$this->assertNotNull($image);$uploaded=$image['image_path'];$this->assertFileExists(FCPATH.$uploaded);
            $this->expectException(\DomainException::class);$service->submit($item,4,'Duplicate review attempt.',[]);
        } finally {
            if($reviewId){$db->table('product_reviews')->where('id',$reviewId)->update(['status'=>'approved']);$stats=(new ReviewService())->productStats((int)$product['id']);$this->assertGreaterThanOrEqual(1,$stats['review_count']);$db->table('product_reviews')->where('id',$reviewId)->delete();}
            if($orderId)$db->table('orders')->where('id',$orderId)->delete();if($uploaded)(new ImageUploadService())->delete($uploaded);if(is_file($source))unlink($source);
        }
    }
}
