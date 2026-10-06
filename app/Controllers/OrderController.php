<?php
namespace App\Controllers;

use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\OrderStatusHistoryModel;
use App\Models\ShipmentModel;
use App\Services\MobileNumberService;
use App\Services\StorefrontService;

class OrderController extends BaseController
{
    public function success(string $token)
    {
        if(!preg_match('/^[a-f0-9]{64}$/',$token)) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $order=(new OrderModel())->where('public_token',$token)->first();
        if(!$order) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('frontend/orders/success',$this->data($order)+['title'=>'অর্ডার সম্পন্ন']);
    }

    public function track()
    {
        return view('frontend/orders/track',['title'=>'অর্ডার ট্র্যাক করুন','settings'=>(new StorefrontService())->settings()]);
    }

    public function lookup()
    {
        $mobile=(new MobileNumberService())->normalize($this->request->getPost('mobile'));
        $number=strtoupper(trim((string)$this->request->getPost('order_no')));
        $order=(new OrderModel())->where(['order_no'=>$number,'customer_mobile'=>$mobile?:''])->first();
        if(!$order) return redirect()->back()->withInput()->with('danger','অর্ডার নম্বর ও মোবাইল নম্বর মেলেনি।');
        return view('frontend/orders/track',$this->data($order)+['title'=>'অর্ডার '.$order['order_no']]);
    }

    private function data(array $order): array
    {
        $shipment=(new ShipmentModel())->where('order_id',$order['id'])->first();
        $items=(new OrderItemModel())->where('order_id',$order['id'])->findAll();$reviews=[];if($items)foreach(db_connect()->table('product_reviews')->whereIn('order_item_id',array_column($items,'id'))->get()->getResultArray() as $review)$reviews[(int)$review['order_item_id']]=$review;
        return ['order'=>$order,'items'=>$items,'reviews'=>$reviews,
            'history'=>(new OrderStatusHistoryModel())->where('order_id',$order['id'])->orderBy('id')->findAll(),
            'shipment'=>$shipment,'shipmentEvents'=>$shipment?db_connect()->table('shipment_events')->where('shipment_id',$shipment['id'])->orderBy('id')->get()->getResultArray():[],
            'settings'=>(new StorefrontService())->settings()];
    }
}
