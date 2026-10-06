<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController; use App\Services\ActivityLogService; use App\Services\Auth\AdminAuthService;
class AuthController extends BaseController
{
    public function loginForm() { return view('admin/login', ['title' => 'Admin Login']); }
    public function login() { if (! $this->validate(['email' => 'required|valid_email', 'password' => 'required'])) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors()); $status = (new AdminAuthService())->attempt((string) $this->request->getPost('email'), (string) $this->request->getPost('password'), $this->request->getIPAddress()); if ($status === 'success') return redirect()->to(route_to('admin.dashboard')); $messages = ['blocked' => 'This admin account is blocked.', 'inactive' => 'This admin account is inactive.', 'throttled' => 'Too many login attempts. Please try again after 15 minutes.']; return redirect()->back()->withInput()->with('danger', $messages[$status] ?? 'Invalid email or password.'); }
    public function logout() { (new ActivityLogService())->log('admin.logout', 'authentication', 'Admin logged out.'); session()->remove(['admin_id','admin_name','admin_role','admin_logged_in']); session()->regenerate(true); return redirect()->to(route_to('admin.login'))->with('success', 'You have been logged out.'); }
}
