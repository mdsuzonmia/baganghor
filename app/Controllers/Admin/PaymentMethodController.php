<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\PaymentMethodModel;
use App\Services\ActivityLogService;
class PaymentMethodController extends BaseController
{
    public function index(){
        $methods=(new PaymentMethodModel())->orderBy('sort_order')->findAll();$allowed=[];
        foreach(db_connect()->table('payment_method_upazilas')->get()->getResultArray() as $row)$allowed[(int)$row['payment_method_id']][]=(int)$row['upazila_id'];
        return view('admin/payment_methods/index',['title'=>'Payment Methods','methods'=>$methods,'allowedUpazilas'=>$allowed,'upazilas'=>db_connect()->table('bd_upazilas u')->select('u.id,u.name_en,u.name_bn,d.name_en AS district_name')->join('bd_districts d','d.id=u.district_id')->where('u.status','active')->orderBy('d.name_en')->orderBy('u.name_en')->get()->getResultArray()]);
    }
    public function update(int $id)
    {
        $model=new PaymentMethodModel();$method=$model->find($id);
        if(!$method)throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $status=$this->request->getPost('status')==='active'?'active':'inactive';$name=trim((string)$this->request->getPost('name'));
        if($name==='')return redirect()->back()->with('danger','Payment method title is required.');
        if(in_array($method['type'],['manual_mobile_banking','manual_bank_transfer'],true)&&$status==='active'&&!trim((string)$this->request->getPost('account_number')))return redirect()->back()->with('danger','Add an account number before enabling this method.');
        $model->update($id,['name'=>$name,'instructions'=>trim((string)$this->request->getPost('instructions'))?:null,'account_number'=>trim((string)$this->request->getPost('account_number'))?:null,'account_name'=>trim((string)$this->request->getPost('account_name'))?:null,'status'=>$status,'sort_order'=>(int)$this->request->getPost('sort_order')]);
        if($method['code']==='cod'){$ids=array_values(array_unique(array_filter(array_map('intval',(array)$this->request->getPost('allowed_upazila_ids')))));$db=db_connect();$db->table('payment_method_upazilas')->where('payment_method_id',$id)->delete();foreach($ids as $upazilaId)if($db->table('bd_upazilas')->where(['id'=>$upazilaId,'status'=>'active'])->countAllResults())$db->table('payment_method_upazilas')->insert(['payment_method_id'=>$id,'upazila_id'=>$upazilaId,'created_at'=>date('Y-m-d H:i:s')]);}
        (new ActivityLogService())->log('payment_method.update','payments','Payment method updated.',['id'=>$id]);return redirect()->back()->with('success','Payment method updated.');
    }
}
