<?php
namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\OrderStatusHistoryModel;
use App\Models\PaymentMethodModel;
use App\Models\ShipmentModel;
use App\Services\StorefrontService;

class AccountController extends BaseController
{
    private function customer(): array
    {
        $customer=(new CustomerModel())->find(session('customer_id'));
        if(!$customer || $customer['status']!=='active') throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return $customer;
    }
    public function index() { return view('account/index',['title'=>'My Account','customer'=>$this->customer()]); }
    public function orders()
    {
        $model=(new OrderModel())->where('customer_id',session('customer_id'));
        return view('frontend/account/orders/index',['title'=>'আমার অর্ডার','orders'=>$model->orderBy('id','DESC')->paginate(15),'pager'=>$model->pager,'settings'=>(new StorefrontService())->settings()]);
    }
    public function order(string $number)
    {
        $order=(new OrderModel())->where(['order_no'=>$number,'customer_id'=>session('customer_id')])->first();
        if(!$order) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $shipment=(new ShipmentModel())->where('order_id',$order['id'])->first();
        $items=(new OrderItemModel())->where('order_id',$order['id'])->findAll();$reviews=[];if($items)foreach(db_connect()->table('product_reviews')->whereIn('order_item_id',array_column($items,'id'))->get()->getResultArray() as $review)$reviews[(int)$review['order_item_id']]=$review;
        return view('frontend/account/orders/show',['title'=>$order['order_no'],'order'=>$order,
            'paymentMethod'=>(new PaymentMethodModel())->find($order['payment_method_id']),
            'items'=>$items,'reviews'=>$reviews,
            'history'=>(new OrderStatusHistoryModel())->where('order_id',$order['id'])->orderBy('id')->findAll(),
            'shipment'=>$shipment,'shipmentEvents'=>$shipment?db_connect()->table('shipment_events')->where('shipment_id',$shipment['id'])->orderBy('id')->get()->getResultArray():[],
            'settings'=>(new StorefrontService())->settings()]);
    }
}
