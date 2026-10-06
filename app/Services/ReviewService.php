<?php
namespace App\Services;

use App\Models\ProductReviewModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class ReviewService
{
    public const REVIEWABLE_ORDER_STATUSES = ['confirmed', 'processing', 'shipped', 'delivered'];

    public function eligibleItem(int $orderItemId, ?int $customerId = null, ?string $token = null): ?array
    {
        $builder = db_connect()->table('order_items oi')
            ->select('oi.*, o.order_no, o.order_status, o.customer_id, o.customer_name, o.public_token, p.slug product_slug')
            ->join('orders o', 'o.id=oi.order_id')->join('products p', 'p.id=oi.product_id')
            ->where('oi.id', $orderItemId)->where('oi.item_type', 'product')
            ->whereIn('o.order_status', self::REVIEWABLE_ORDER_STATUSES);
        $validToken = $token && preg_match('/^[a-f0-9]{64}$/', $token);
        if ($customerId && $validToken) $builder->groupStart()->where('o.customer_id', $customerId)->orWhere('o.public_token', $token)->groupEnd();
        elseif ($customerId) $builder->where('o.customer_id', $customerId);
        elseif ($validToken) $builder->where('o.public_token', $token);
        else return null;
        return $builder->get()->getRowArray() ?: null;
    }

    public function submit(array $item, int $rating, string $text, array $files): int
    {
        if ($rating < 1 || $rating > 5) throw new \InvalidArgumentException('রেটিং ১ থেকে ৫ তারকার মধ্যে হতে হবে।');
        $text = trim($text);
        if (mb_strlen($text) < 5 || mb_strlen($text) > 3000) throw new \InvalidArgumentException('রিভিউ ৫ থেকে ৩০০০ অক্ষরের মধ্যে লিখুন।');
        $files = array_values(array_filter($files, static fn($file) => $file instanceof UploadedFile && $file->getError() !== UPLOAD_ERR_NO_FILE));
        if (count($files) > 3) throw new \InvalidArgumentException('সর্বোচ্চ ৩টি ছবি আপলোড করা যাবে।');
        $db = db_connect(); $stored = []; $db->transBegin();
        try {
            if ($db->table('product_reviews')->where('order_item_id', $item['id'])->countAllResults()) throw new \DomainException('এই অর্ডার আইটেমের রিভিউ ইতিমধ্যে দেওয়া হয়েছে।');
            $model = new ProductReviewModel();
            $id = (int) $model->insert(['customer_id'=>$item['customer_id'] ?: null,'order_id'=>$item['order_id'],'order_item_id'=>$item['id'],'product_id'=>$item['product_id'],'customer_name'=>$item['customer_name'],'rating'=>$rating,'review_text'=>$text,'status'=>'pending','is_verified_purchase'=>1], true);
            $upload = new ImageUploadService();
            foreach ($files as $index => $file) {
                $path = $upload->store($file, 'reviews'); $stored[] = $path;
                $db->table('product_review_images')->insert(['review_id'=>$id,'image_path'=>$path,'sort_order'=>$index,'created_at'=>date('Y-m-d H:i:s')]);
            }
            if (!$db->transStatus()) throw new \RuntimeException('রিভিউ সংরক্ষণ করা যায়নি।');
            $db->transCommit(); return $id;
        } catch (\Throwable $e) {
            $db->transRollback(); $upload = new ImageUploadService(); foreach ($stored as $path) $upload->delete($path); throw $e;
        }
    }

    public function statsForProducts(array $productIds): array
    {
        if (!$productIds) return [];
        $rows = db_connect()->table('product_reviews')->select('product_id, COUNT(*) review_count, AVG(rating) average_rating')->where('status','approved')->whereIn('product_id',$productIds)->groupBy('product_id')->get()->getResultArray();
        $result=[]; foreach($rows as $row)$result[(int)$row['product_id']]=['review_count'=>(int)$row['review_count'],'average_rating'=>round((float)$row['average_rating'],1)]; return $result;
    }

    public function productStats(int $productId): array
    {
        $base=['review_count'=>0,'average_rating'=>0.0,'breakdown'=>[5=>0,4=>0,3=>0,2=>0,1=>0]];
        $rows=db_connect()->table('product_reviews')->select('rating, COUNT(*) total')->where(['product_id'=>$productId,'status'=>'approved'])->groupBy('rating')->get()->getResultArray();
        $sum=0; foreach($rows as $row){$rating=(int)$row['rating'];$count=(int)$row['total'];$base['breakdown'][$rating]=$count;$base['review_count']+=$count;$sum+=$rating*$count;}
        if($base['review_count'])$base['average_rating']=round($sum/$base['review_count'],1); return $base;
    }
}
