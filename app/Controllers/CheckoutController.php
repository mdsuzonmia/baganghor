<?php
namespace App\Controllers;

use App\Models\CustomerAddressModel;
use App\Models\CustomerModel;
use App\Models\DistrictModel;
use App\Models\PaymentMethodModel;
use App\Models\UpazilaModel;
use App\Services\CartService;
use App\Services\DeliveryService;
use App\Services\OrderService;
use App\Services\StorefrontService;

class CheckoutController extends BaseController
{
    private function context(): array
    {
        $mode = session('checkout_mode') === 'buy_now' ? 'buy_now' : 'cart';
        return ['mode' => $mode, 'item' => $mode === 'buy_now' ? session('checkout_item') : null];
    }

    private function cart(array $context): array
    {
        $cart = new CartService();
        return $context['mode'] === 'buy_now' ? $cart->one((array) $context['item']) : $cart->resolve();
    }

    public function index()
    {
        $context = $this->context();
        $cart = $this->cart($context);
        if (!$cart['items'] || $cart['errors']) return redirect()->to(url_to('cart.index'))->with('danger', $cart['errors'][0] ?? 'Cart is empty.');
        $token = bin2hex(random_bytes(32));
        session()->set('checkout_token', $token);
        $customer = session('customer_logged_in') ? (new CustomerModel())->find(session('customer_id')) : null;
        $address = $customer ? (new CustomerAddressModel())->where('customer_id', $customer['id'])->orderBy('is_default', 'DESC')->orderBy('id', 'DESC')->first() : null;
        $districtId = 0; $upazilaId = 0;
        if ($address) {
            $district = (new DistrictModel())->groupStart()->where('name_bn', $address['district'])->orWhere('name_en', $address['district'])->groupEnd()->first();
            $districtId = (int) ($district['id'] ?? 0);
            if ($districtId) {
                $upazila = (new UpazilaModel())->where('district_id', $districtId)->groupStart()->where('name_bn', $address['upazila'])->orWhere('name_en', $address['upazila'])->groupEnd()->first();
                $upazilaId = (int) ($upazila['id'] ?? 0);
            }
        }
        $methods=(new PaymentMethodModel())->where('status','active')->orderBy('sort_order')->findAll();$allowed=[];
        foreach(db_connect()->table('payment_method_upazilas')->get()->getResultArray() as $row)$allowed[(int)$row['payment_method_id']][]=(int)$row['upazila_id'];
        foreach($methods as &$method)$method['allowed_upazila_ids']=$allowed[(int)$method['id']]??[];unset($method);
        return view('frontend/checkout/index', [
            'title' => 'Checkout', 'cart' => $cart, 'mode' => $context['mode'], 'token' => $token,
            'customer' => $customer, 'address' => $address, 'selectedDistrictId' => $districtId, 'selectedUpazilaId' => $upazilaId,
            'districts' => (new DistrictModel())->where('status', 'active')->orderBy('sort_order')->findAll(),
            'methods' => $methods,
            'settings' => (new StorefrontService())->settings(),
        ]);
    }

    public function deliveryCharge()
    {
        try {
            $cart = $this->cart($this->context());
            if ($cart['errors'] || !$cart['items']) throw new \RuntimeException($cart['errors'][0] ?? 'Cart is empty.');
            $districtId = (int) $this->request->getPost('district_id'); $upazilaId = (int) $this->request->getPost('upazila_id');
            if (!(new UpazilaModel())->where(['id' => $upazilaId, 'district_id' => $districtId, 'status' => 'active'])->first()) throw new \InvalidArgumentException('Select a valid district and upazila.');
            $fee = (new DeliveryService())->calculate($districtId, $upazilaId, $cart['subtotal']);
            return $this->response->setJSON(['ok' => true, 'delivery_charge' => $fee['charge'], 'grand_total' => $cart['subtotal'] + $fee['charge'], 'estimate' => $fee['estimate'], 'csrf_hash' => csrf_hash()]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => $e->getMessage(), 'csrf_hash' => csrf_hash()]);
        }
    }

    public function placeOrder()
    {
        if (!hash_equals((string) session('checkout_token'), (string) $this->request->getPost('checkout_token'))) return redirect()->back()->withInput()->with('danger', 'Checkout session expired. Please try again.');
        try {
            $context = $this->context(); $order = (new OrderService())->place($this->request->getPost(), $context);
            if (session('customer_logged_in') && $this->request->getPost('save_address')) {
                try { $this->saveAddress($order); } catch (\Throwable $e) { log_message('error', 'Saved-address update failed after order: {message}', ['message' => $e->getMessage()]); }
            }
            if ($context['mode'] === 'buy_now') session()->remove(['checkout_mode', 'checkout_item']); else (new CartService())->clear();
            session()->remove('checkout_token');
            return redirect()->to(site_url('order-success/' . $order['public_token']));
        } catch (\Throwable $e) {
            log_message('error', 'Order placement failed: {message}', ['message' => $e->getMessage()]);
            $message = ($e instanceof \InvalidArgumentException || $e instanceof \RuntimeException) ? $e->getMessage() : 'The order could not be placed. Please try again.';
            return redirect()->back()->withInput()->with('danger', $message);
        }
    }

    private function saveAddress(array $order): void
    {
        $customerId = (int) session('customer_id'); $db = db_connect();
        $db->transException(true)->transStart();
        $db->table('customer_addresses')->where('customer_id', $customerId)->update(['is_default' => 0]);
        $existing = $db->table('customer_addresses')->where(['customer_id' => $customerId, 'district' => $order['district_name'], 'upazila' => $order['upazila_name'], 'address_line' => $order['address_line']])->get()->getRowArray();
        $data = ['customer_id' => $customerId, 'label' => 'Home', 'recipient_name' => $order['customer_name'], 'mobile' => $order['customer_mobile'], 'district' => $order['district_name'], 'upazila' => $order['upazila_name'], 'area' => $order['area'], 'address_line' => $order['address_line'], 'landmark' => $order['landmark'], 'is_default' => 1, 'updated_at' => date('Y-m-d H:i:s')];
        if ($existing) $db->table('customer_addresses')->where('id', $existing['id'])->update($data); else $db->table('customer_addresses')->insert($data + ['created_at' => date('Y-m-d H:i:s')]);
        $db->transComplete();
    }
}
