<?php
namespace App\Filters;
use App\Models\AdminUserModel;
use CodeIgniter\Filters\FilterInterface; use CodeIgniter\HTTP\RequestInterface; use CodeIgniter\HTTP\ResponseInterface;
class AdminAuth implements FilterInterface { public function before(RequestInterface $request, $arguments = null) { $admin=session('admin_logged_in')?(new AdminUserModel())->find(session('admin_id')):null; if (!$admin || $admin['status'] !== 'active') { session()->remove(['admin_id','admin_name','admin_role','admin_logged_in']); return redirect()->to(route_to('admin.login'))->with('warning', 'Please log in to continue.'); } } public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {} }
