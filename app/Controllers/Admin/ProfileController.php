<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController; use App\Models\AdminUserModel; use App\Services\ActivityLogService;
class ProfileController extends BaseController
{
    public function index(){return view('admin/profile/index',['title'=>'My Profile','admin'=>(new AdminUserModel())->find(session('admin_id'))]);}
    public function update(){ $rules=['name'=>'required|min_length[2]|max_length[150]','mobile'=>'permit_empty|max_length[20]']; $password=(string)$this->request->getPost('password'); if($password!=='')$rules['password']='min_length[8]|max_length[255]'; if(!$this->validate($rules))return redirect()->back()->withInput()->with('errors',$this->validator->getErrors()); $data=['name'=>trim((string)$this->request->getPost('name')),'mobile'=>trim((string)$this->request->getPost('mobile'))?:null]; if($password!=='')$data['password_hash']=password_hash($password,PASSWORD_DEFAULT); (new AdminUserModel())->update(session('admin_id'),$data); session()->set('admin_name',$data['name']); (new ActivityLogService())->log('admin.profile_update','admin_users','Profile updated.'); return redirect()->back()->with('success','Profile updated successfully.');}
}
