<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface; use CodeIgniter\HTTP\RequestInterface; use CodeIgniter\HTTP\ResponseInterface;
class AdminPermission implements FilterInterface
{
    public function before(RequestInterface $request,$arguments=null){if(session('admin_role')==='super_admin')return null;$permission=$arguments[0]??'';$allowed=db_connect()->table('admin_role_permissions rp')->join('admin_permissions p','p.id=rp.permission_id')->join('admin_users u','u.role_id=rp.role_id')->where(['u.id'=>session('admin_id'),'p.slug'=>$permission])->countAllResults()>0;if(!$allowed)return redirect()->to(url_to('admin.dashboard'))->with('danger','You do not have permission to perform that action.');}
    public function after(RequestInterface $request,ResponseInterface $response,$arguments=null){}
}
