<?php
namespace App\Controllers\Auth;
use App\Controllers\BaseController; use App\Models\CustomerModel; use App\Services\Auth\CustomerAuthService; use App\Services\MobileNumberService;

class CustomerAuthController extends BaseController
{
    public function registerForm() { return view('auth/register', ['title' => 'Create Account']); }
    public function register()
    {
        $mobile = (new MobileNumberService())->normalize($this->request->getPost('mobile'));
        $email = trim((string) $this->request->getPost('email')) ?: null;
        $data = ['full_name' => trim((string) $this->request->getPost('full_name')), 'mobile' => $mobile, 'email' => $email, 'password' => (string) $this->request->getPost('password'), 'password_confirm' => (string) $this->request->getPost('password_confirm')];
        $rules = ['full_name' => 'required|min_length[2]|max_length[150]', 'mobile' => 'required|exact_length[11]|is_unique[customers.mobile]', 'email' => 'permit_empty|valid_email|max_length[190]|is_unique[customers.email]', 'password' => 'required|min_length[6]|max_length[255]', 'password_confirm' => 'required|matches[password]'];
        $messages = ['mobile' => ['required' => 'Please enter a valid Bangladesh mobile number.', 'exact_length' => 'Please enter a valid Bangladesh mobile number.', 'is_unique' => 'This mobile number is already registered.'], 'password' => ['min_length' => 'Password must contain at least 6 characters.']];
        if (! $mobile || ! $this->validateData($data, $rules, $messages)) return redirect()->back()->withInput()->with('errors', $this->validator?->getErrors() ?: ['mobile' => 'Please enter a valid Bangladesh mobile number.']);
        (new CustomerAuthService())->register($data);
        return redirect()->to(route_to('customer.login'))->with('success', 'Account created successfully. You can now log in.');
    }
    public function loginForm() { return view('auth/login', ['title' => 'Customer Login']); }
    public function login()
    {
        if (! $this->validate(['mobile' => 'required', 'password' => 'required'])) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        $status = (new CustomerAuthService())->attempt((string) $this->request->getPost('mobile'), (string) $this->request->getPost('password'), $this->request->getIPAddress(), (bool) $this->request->getPost('remember'));
        if ($status === 'success') return redirect()->to(route_to('customer.account'));
        $messages = ['blocked' => 'Your account is blocked. Please contact Taharat Agro support.', 'inactive' => 'Your account is inactive. Please contact Taharat Agro support.', 'throttled' => 'Too many login attempts. Please try again after 15 minutes.'];
        return redirect()->back()->withInput()->with('danger', $messages[$status] ?? 'Invalid mobile number or password.');
    }
    public function logout() { if(session('customer_id'))(new CustomerModel())->update(session('customer_id'),['remember_token'=>null]); service('response')->deleteCookie('taharat_remember'); session()->remove(['customer_id','customer_name','customer_logged_in']); session()->regenerate(true); return redirect()->to(route_to('customer.login'))->with('success', 'You have been logged out.'); }
}
