<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController; use App\Models\CustomerModel; use App\Services\ActivityLogService;
class CustomerController extends BaseController
{
    public function index() { $model = new CustomerModel(); $q = trim((string)$this->request->getGet('q')); $status = (string)$this->request->getGet('status'); if ($q !== '') $model->groupStart()->like('full_name',$q)->orLike('mobile',$q)->orLike('customer_code',$q)->groupEnd(); if (in_array($status,['active','inactive','blocked'],true)) $model->where('status',$status); return view('admin/customers/index',['title'=>'Customers','customers'=>$model->orderBy('id','DESC')->paginate(20),'pager'=>$model->pager,'q'=>$q,'status'=>$status]); }
    public function show(int $id) { $customer = (new CustomerModel())->find($id); if (!$customer) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); return view('admin/customers/show',['title'=>'Customer Details','customer'=>$customer]); }
    public function status(int $id) { $status=(string)$this->request->getPost('status'); if (!in_array($status,['active','inactive','blocked'],true)) return redirect()->back()->with('danger','Invalid account status.'); $model=new CustomerModel(); if(!$model->find($id)) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(); $model->update($id,['status'=>$status]); (new ActivityLogService())->log('customer.status_changed','customers','Customer status changed.',['customer_id'=>$id,'status'=>$status]); return redirect()->back()->with('success','Customer status updated successfully.'); }
}
