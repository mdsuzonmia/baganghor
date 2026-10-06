<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductReviewModel;
use App\Services\ActivityLogService;
use App\Services\ImageUploadService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ReviewController extends BaseController
{
    public function index()
    {
        $model=(new ProductReviewModel())->select('product_reviews.*, products.name product_name, products.slug product_slug, orders.order_no')
            ->join('products','products.id=product_reviews.product_id')->join('orders','orders.id=product_reviews.order_id');
        $status=(string)$this->request->getGet('status'); if(in_array($status,['pending','approved','rejected'],true))$model->where('product_reviews.status',$status);
        $rating=(int)$this->request->getGet('rating'); if($rating>=1&&$rating<=5)$model->where('product_reviews.rating',$rating);
        $q=trim((string)$this->request->getGet('q')); if($q!=='')$model->groupStart()->like('product_reviews.customer_name',$q)->orLike('products.name',$q)->orLike('orders.order_no',$q)->groupEnd();
        return view('admin/reviews/index',['title'=>'Reviews','reviews'=>$model->orderBy('product_reviews.id','DESC')->paginate(20),'pager'=>$model->pager,'filters'=>compact('status','rating','q')]);
    }

    public function show(int $id)
    {
        $review=$this->review($id); $review['images']=db_connect()->table('product_review_images')->where('review_id',$id)->orderBy('sort_order')->get()->getResultArray();
        return view('admin/reviews/show',['title'=>'Review #'.$id,'review'=>$review]);
    }

    public function moderate(int $id)
    {
        $review=$this->review($id);$status=(string)$this->request->getPost('status');
        if(!in_array($status,['approved','rejected'],true))return redirect()->back()->with('danger','Invalid review status.');
        (new ProductReviewModel())->update($id,['status'=>$status]);
        (new ActivityLogService())->log('moderate','reviews','Review #'.$id.' marked '.$status,['review_id'=>$id,'from'=>$review['status'],'to'=>$status]);
        return redirect()->back()->with('success','Review status updated.');
    }

    public function reply(int $id)
    {
        $this->review($id);$reply=trim((string)$this->request->getPost('admin_reply'));
        if(mb_strlen($reply)>3000)return redirect()->back()->with('danger','Reply must be 3,000 characters or fewer.');
        (new ProductReviewModel())->update($id,['admin_reply'=>$reply?:null,'replied_at'=>$reply?date('Y-m-d H:i:s'):null,'replied_by_admin_id'=>$reply?session('admin_id'):null]);
        (new ActivityLogService())->log('reply','reviews','Updated reply for review #'.$id,['review_id'=>$id]);
        return redirect()->back()->with('success','Reply saved.');
    }

    public function delete(int $id)
    {
        $this->review($id);$images=db_connect()->table('product_review_images')->where('review_id',$id)->get()->getResultArray();
        db_connect()->table('product_reviews')->where('id',$id)->delete();$upload=new ImageUploadService();foreach($images as $image)$upload->delete($image['image_path']);
        (new ActivityLogService())->log('delete','reviews','Deleted review #'.$id,['review_id'=>$id]);
        return redirect()->to(url_to('admin.reviews'))->with('success','Review deleted.');
    }

    private function review(int $id): array
    {
        $row=(new ProductReviewModel())->select('product_reviews.*, products.name product_name, products.slug product_slug, orders.order_no')->join('products','products.id=product_reviews.product_id')->join('orders','orders.id=product_reviews.order_id')->find($id);
        if(!$row)throw PageNotFoundException::forPageNotFound();return $row;
    }
}
