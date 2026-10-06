<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface; use CodeIgniter\HTTP\RequestInterface; use CodeIgniter\HTTP\ResponseInterface;
class AdminGuest implements FilterInterface { public function before(RequestInterface $request, $arguments = null) { if (session('admin_logged_in')) return redirect()->to(route_to('admin.dashboard')); } public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {} }
