<?php
namespace App\Controllers;

use App\Models\ProductReviewModel;
use App\Services\ReviewService;
use App\Services\StorefrontService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ReviewController extends BaseController
{
    public function create(string $token, int $orderItemId)
    {
        $reviewService = new ReviewService();
        $item=$reviewService->eligibleItem($orderItemId, session('customer_id') ? (int)session('customer_id') : null, $token);
        if(!$item) throw PageNotFoundException::forPageNotFound();
        $existing=(new ProductReviewModel())->where('order_item_id',$orderItemId)->first();
        return view('frontend/reviews/form',['title'=>'রিভিউ দিন','item'=>$item,'token'=>$token,'existing'=>$existing,'reviewStats'=>$reviewService->productStats((int)$item['product_id']),'settings'=>(new StorefrontService())->settings()]);
    }

    public function store(string $token, int $orderItemId)
    {
        $service=new ReviewService();
        $item=$service->eligibleItem($orderItemId,session('customer_id')?(int)session('customer_id'):null,$token);
        if(!$item) throw PageNotFoundException::forPageNotFound();
        try {
            $service->submit($item,(int)$this->request->getPost('rating'),(string)$this->request->getPost('review_text'),$this->request->getFileMultiple('images')?:[]);
            return redirect()->to(url_to('catalog.product',$item['product_slug']).'#reviews')->with('success','আপনার রিভিউ গ্রহণ করা হয়েছে। অনুমোদনের পর এটি প্রকাশিত হবে।');
        } catch (\Throwable $e) {
            $message=$e instanceof \InvalidArgumentException || $e instanceof \DomainException ? $e->getMessage() : 'রিভিউ জমা দেওয়া যায়নি। আবার চেষ্টা করুন।';
            return redirect()->back()->withInput()->with('danger',$message);
        }
    }
}
