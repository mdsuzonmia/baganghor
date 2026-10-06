<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\OrderStatusHistoryModel;
use App\Models\PaymentModel;
use App\Models\CustomerModel;
use App\Models\DistrictModel;
use App\Models\PaymentMethodModel;
use App\Models\ProductModel;
use App\Models\ProductPackageModel;
use App\Models\ProductVariantModel;
use App\Models\ShipmentModel;
use App\Services\ActivityLogService;
use App\Services\CartService;
use App\Services\OrderStatusService;
use App\Services\OrderService;
use App\Services\ProductService;
use App\Services\SettingService;

class OrderController extends BaseController
{
    public function index()
    {
        $model=new OrderModel();$q=trim((string)$this->request->getGet('q'));
        if($q) $model->groupStart()->like('order_no',$q)->orLike('customer_name',$q)->orLike('customer_mobile',$q)->groupEnd();
        foreach(['order_status','payment_status','payment_method_code'] as $field) if($this->request->getGet($field)) $model->where($field,$this->request->getGet($field));
        if($this->request->getGet('from')) $model->where('placed_at >=',$this->request->getGet('from').' 00:00:00');
        if($this->request->getGet('to')) $model->where('placed_at <=',$this->request->getGet('to').' 23:59:59');
        $canManage=session('admin_role')==='super_admin'||db_connect()->table('admin_role_permissions rp')->join('admin_permissions p','p.id=rp.permission_id')->join('admin_users u','u.role_id=rp.role_id')->where(['u.id'=>session('admin_id'),'p.slug'=>'orders.manage'])->countAllResults()>0;
        return view('admin/orders/index',['title'=>'Orders','orders'=>$model->orderBy('id','DESC')->paginate(20),'pager'=>$model->pager,'canManage'=>$canManage]);
    }

    public function create()
    {
        $products=(new ProductModel())->where('status','active')->orderBy('name')->findAll();
        $variants=(new ProductVariantModel())->where('status','active')->orderBy('sort_order')->findAll();
        $byProduct=[];foreach($variants as $variant)$byProduct[$variant['product_id']][]=$variant;
        $choices=[];$pricing=new ProductService();
        foreach($products as $product){
            if($product['stock_type']==='variant')foreach($byProduct[$product['id']]??[] as $variant)$choices[]=['key'=>'product:'.$product['id'].':'.$variant['id'],'label'=>$product['name'].' — '.$variant['variant_name'],'price'=>$pricing->effectivePrice($variant),'stock'=>(int)$variant['stock_quantity']];
            else $choices[]=['key'=>'product:'.$product['id'].':0','label'=>$product['name'],'price'=>$pricing->effectivePrice($product),'stock'=>$product['manage_stock']?(int)$product['stock_quantity']:null];
        }
        foreach((new ProductPackageModel())->where('status','active')->orderBy('name')->findAll() as $package)$choices[]=['key'=>'package:'.$package['id'],'label'=>$package['name'].' (Package)','price'=>(float)$package['package_price'],'stock'=>null];
        return view('admin/orders/create',['title'=>'Create Order','customers'=>(new CustomerModel())->where('status','active')->orderBy('full_name')->findAll(),'districts'=>(new DistrictModel())->where('status','active')->orderBy('sort_order')->orderBy('name_en')->findAll(),'methods'=>(new PaymentMethodModel())->where('status','active')->orderBy('sort_order')->findAll(),'choices'=>$choices,'token'=>bin2hex(random_bytes(32))]);
    }

    public function store()
    {
        try {
            $keys=(array)$this->request->getPost('item_key');$quantities=(array)$this->request->getPost('quantity');$raw=[];
            foreach($keys as $index=>$key){$quantity=max(1,(int)($quantities[$index]??1));$quantity+=(int)($raw[$key]['quantity']??0);if(preg_match('/^product:(\d+):(\d+)$/',(string)$key,$match))$raw[$key]=['item_type'=>'product','product_id'=>(int)$match[1],'variant_id'=>(int)$match[2]?:null,'package_id'=>null,'quantity'=>$quantity];elseif(preg_match('/^package:(\d+)$/',(string)$key,$match))$raw[$key]=['item_type'=>'package','product_id'=>null,'variant_id'=>null,'package_id'=>(int)$match[1],'quantity'=>$quantity];}
            if(!$raw)throw new \InvalidArgumentException('Add at least one product or package.');
            $resolved=(new CartService())->resolve($raw);$customerId=(int)$this->request->getPost('customer_id');
            $context=['mode'=>'admin','resolved'=>$resolved,'source'=>'admin','create_customer'=>(bool)$this->request->getPost('create_customer'),'customer_password'=>(string)$this->request->getPost('customer_password')];if($customerId)$context['customer_id']=$customerId;
            $order=(new OrderService())->place($this->request->getPost(),$context);
            (new ActivityLogService())->log('order.created','orders','Order created by admin.',['order_id'=>$order['id']]);
            return redirect()->to(url_to('admin.orders.show',$order['id']))->with('success','Order created successfully.');
        } catch(\Throwable $e) {
            log_message('error','Admin order creation failed: {message}',['message'=>$e->getMessage()]);
            $message=$e instanceof \InvalidArgumentException||$e instanceof \RuntimeException?$e->getMessage():'The order could not be created.';
            return redirect()->back()->withInput()->with('danger',$message);
        }
    }

    public function show(int $id)
    {
        $order=(new OrderModel())->find($id);
        if(!$order) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $items=$this->items($id);
        return view('admin/orders/show',['title'=>'Order '.$order['order_no'],'order'=>$order,'items'=>$items,
            'payment'=>(new PaymentModel())->where('order_id',$id)->first(),
            'paymentMethod'=>(new PaymentMethodModel())->find($order['payment_method_id']),
            'shipment'=>(new ShipmentModel())->where('order_id',$id)->first(),
            'history'=>(new OrderStatusHistoryModel())->where('order_id',$id)->orderBy('id')->findAll(),
            'transitions'=>(new OrderStatusService())->options($order['order_status'])]);
    }

    public function status(int $id)
    {
        $order=(new OrderModel())->find($id);
        if(!$order)throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        if($this->request->getPost('status')==='confirmed'&&$order['payment_method_code']==='courier_condition'&&empty($order['delivery_charge_paid_at']))return redirect()->back()->with('danger','Verify the advance delivery charge before confirming this Courier Condition order.');
        try {
            (new OrderStatusService())->transition($id,(string)$this->request->getPost('status'),(string)$this->request->getPost('note'));
            (new ActivityLogService())->log('order.status_change','orders','Order status changed.',['order_id'=>$id]);
            return redirect()->back()->with('success','Order status updated.');
        } catch(\Throwable $e) { return redirect()->back()->with('danger',$e->getMessage()); }
    }

    public function payment(int $id)
    {
        $status=(string)$this->request->getPost('status');
        if(!in_array($status,['paid','failed'],true)) return redirect()->back()->with('danger','Invalid payment status.');
        try {
            $db=db_connect();$db->transException(true)->transStart();
            $order=$db->query('SELECT * FROM '.$db->prefixTable('orders').' WHERE id=? FOR UPDATE',[$id])->getRowArray();
            $payment=$db->table('payments')->where('order_id',$id)->get()->getRowArray();
            if(!$order || !$payment) throw new \RuntimeException('Order payment not found.');
            if($order['order_status']==='cancelled' && $status==='paid') throw new \RuntimeException('Cancelled orders cannot be marked paid.');
            $now=date('Y-m-d H:i:s');
            $db->table('payments')->where('id',$payment['id'])->update(['status'=>$status,'verified_by'=>session('admin_id')?:null,'verified_at'=>$now,'updated_at'=>$now]);
            if($order['payment_method_code']==='courier_condition'){$db->table('orders')->where('id',$id)->update(['payment_status'=>'unpaid','delivery_charge_paid_at'=>$status==='paid'?$now:null,'delivery_charge_paid_by'=>$status==='paid'?(session('admin_id')?:null):null,'updated_at'=>$now]);}else{$db->table('orders')->where('id',$id)->update(['payment_status'=>$status,'updated_at'=>$now]);}
            $db->transComplete();
            (new ActivityLogService())->log($status==='paid'?'order.payment_verify':'order.payment_reject','orders','Order payment updated.',['order_id'=>$id]);
            return redirect()->back()->with('success',$order['payment_method_code']==='courier_condition'&&$status==='paid'?'Delivery charge verified. Product amount remains due.':'Payment updated.');
        } catch(\Throwable $e) { if(isset($db)) $db->transRollback(); return redirect()->back()->with('danger',$e->getMessage()); }
    }

    public function note(int $id)
    {
        $model=new OrderModel();if(!$model->find($id)) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $model->update($id,['admin_note'=>trim((string)$this->request->getPost('admin_note'))?:null]);
        (new ActivityLogService())->log('order.admin_note','orders','Order note updated.',['order_id'=>$id]);
        return redirect()->back()->with('success','Note saved.');
    }

    public function deliveryPayment(int $id)
    {
        $model = new OrderModel();
        $order = $model->find($id);
        if (!$order) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        if ($order['order_status'] === 'cancelled') return redirect()->back()->with('danger', 'A cancelled order cannot receive a delivery payment.');
        if ((float) $order['delivery_charge'] <= 0) return redirect()->back()->with('danger', 'This order has no delivery charge.');
        $paid = $this->request->getPost('action') === 'mark_paid';
        $model->update($id, ['delivery_charge_paid_at' => $paid ? date('Y-m-d H:i:s') : null, 'delivery_charge_paid_by' => $paid ? (session('admin_id') ?: null) : null]);
        (new ActivityLogService())->log($paid ? 'order.delivery_charge_paid' : 'order.delivery_charge_payment_reversed', 'orders', $paid ? 'Delivery charge marked paid.' : 'Delivery charge payment reversed.', ['order_id' => $id, 'amount' => (float) $order['delivery_charge']]);
        return redirect()->back()->with('success', $paid ? 'Delivery charge marked paid. Invoice balance updated.' : 'Delivery charge payment reversed.');
    }

    public function print(int $id)
    {
        $order=(new OrderModel())->find($id);
        if(!$order) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('admin/orders/print',['order'=>$order,'items'=>$this->items($id),'settings'=>(new SettingService())->all()]);
    }

    private function items(int $orderId): array
    {
        $items=(new OrderItemModel())->where('order_id',$orderId)->findAll();
        foreach($items as &$item) $item['components']=db_connect()->table('order_item_components')->where('order_item_id',$item['id'])->get()->getResultArray();
        unset($item);
        return $items;
    }
}
