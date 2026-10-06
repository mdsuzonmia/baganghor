<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface; use CodeIgniter\HTTP\RequestInterface; use CodeIgniter\HTTP\ResponseInterface;
class AdminRole implements FilterInterface { public function before(RequestInterface $request, $arguments = null) { if (! session('admin_logged_in')) return redirect()->to(route_to('admin.login')); if ($arguments && ! in_array(session('admin_role'), $arguments, true)) return redirect()->to(route_to('admin.dashboard'))->with('danger', 'You do not have permission to access that page.'); } public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {} }
