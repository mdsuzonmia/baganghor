<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface; use CodeIgniter\HTTP\RequestInterface; use CodeIgniter\HTTP\ResponseInterface;
class CustomerGuest implements FilterInterface { public function before(RequestInterface $request, $arguments = null) { if (session('customer_logged_in')) return redirect()->to(route_to('customer.account')); } public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {} }
