<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CourierProviderModel;
use App\Models\OrderModel;
use App\Models\ShipmentModel;
use App\Services\ActivityLogService;
use App\Services\ShipmentService;

class ShipmentController extends BaseController
{
    public function index()
    {
        $model=new ShipmentModel();
        $status=trim((string)$this->request->getGet('status'));
        if($status) $model->where('status',$status);
        $shipments=$model->orderBy('id','DESC')->paginate(20);
        $orderIds=array_column($shipments,'order_id');
        $orders=$orderIds?array_column((new OrderModel())->whereIn('id',$orderIds)->findAll(),null,'id'):[];
        $providers=array_column((new CourierProviderModel())->findAll(),null,'id');
        return view('admin/shipments/index',['title'=>'Shipments','shipments'=>$shipments,'pager'=>$model->pager,'orders'=>$orders,'providers'=>$providers]);
    }

    public function order(int $orderId)
    {
        $order=(new OrderModel())->find($orderId);
        if(!$order) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $shipment=(new ShipmentModel())->where('order_id',$orderId)->first();
        $events=$shipment?db_connect()->table('shipment_events')->where('shipment_id',$shipment['id'])->orderBy('id')->get()->getResultArray():[];
        return view('admin/shipments/order',['title'=>'Fulfillment '.$order['order_no'],'order'=>$order,'shipment'=>$shipment,'events'=>$events,'providers'=>(new CourierProviderModel())->where('status','active')->findAll(),'options'=>$shipment?(new ShipmentService())->options($shipment['status']):[]]);
    }

    public function prepare(int $orderId)
    {
        try {
            $shipment=(new ShipmentService())->prepare($orderId,$this->request->getPost());
            (new ActivityLogService())->log('shipment.prepare','shipments','Shipment prepared.',['shipment_id'=>$shipment['id'],'order_id'=>$orderId]);
            return redirect()->to(url_to('admin.shipments.order',$orderId))->with('success','Shipment saved.');
        } catch(\Throwable $e) { return redirect()->back()->withInput()->with('danger',$e->getMessage()); }
    }

    public function book(int $id)
    {
        try {
            $shipment=(new ShipmentService())->book($id);
            (new ActivityLogService())->log('shipment.book','shipments','Courier booking recorded.',['shipment_id'=>$id]);
            return redirect()->to(url_to('admin.shipments.order',$shipment['order_id']))->with('success','Shipment booked. You can now mark the order shipped.');
        } catch(\Throwable $e) { return redirect()->back()->with('danger',$e->getMessage()); }
    }

    public function status(int $id)
    {
        try {
            $shipment=(new ShipmentService())->transition($id,(string)$this->request->getPost('status'),(string)$this->request->getPost('note'));
            (new ActivityLogService())->log('shipment.status','shipments','Shipment status updated.',['shipment_id'=>$id,'status'=>$shipment['status']]);
            return redirect()->to(url_to('admin.shipments.order',$shipment['order_id']))->with('success','Shipment status updated.');
        } catch(\Throwable $e) { return redirect()->back()->with('danger',$e->getMessage()); }
    }
}
