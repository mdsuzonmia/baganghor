<?php
namespace App\Services\Auth;
use App\Models\CustomerModel;
use App\Services\LoginThrottleService;
use App\Services\MobileNumberService;

class CustomerAuthService
{
    public function register(array $data): int
    {
        $model = new CustomerModel();
        $db = db_connect(); $db->transStart();
        $id = $model->insert(['customer_code' => 'PENDING-' . bin2hex(random_bytes(6)), 'full_name' => trim($data['full_name']), 'mobile' => $data['mobile'], 'email' => $data['email'] ?: null, 'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT), 'status' => 'active'], true);
        $model->update($id, ['customer_code' => sprintf('TAH-CUS-%06d', $id)]);
        $db->transComplete();
        if (! $db->transStatus()) throw new \RuntimeException('Could not create account.');
        return (int) $id;
    }
    public function attempt(string $mobile, string $password, string $ip, bool $remember = false): string
    {
        $normalized = (new MobileNumberService())->normalize($mobile);
        if (! $normalized) return 'invalid';
        $throttle = new LoginThrottleService();
        if ($throttle->tooMany('customer', $normalized, $ip)) return 'throttled';
        $model = new CustomerModel(); $customer = $model->where('mobile', $normalized)->first();
        if (! $customer || ! password_verify($password, $customer['password_hash'])) { $throttle->record('customer', $normalized, $ip, false); return 'invalid'; }
        if ($customer['status'] !== 'active') return $customer['status'];
        $throttle->record('customer', $normalized, $ip, true); session()->regenerate(true);
        session()->set(['customer_id' => $customer['id'], 'customer_name' => $customer['full_name'], 'customer_logged_in' => true]);
        $update = ['last_login_at' => date('Y-m-d H:i:s'), 'last_login_ip' => $ip];
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $update['remember_token'] = hash('sha256', $token);
            service('response')->setCookie('taharat_remember', $customer['id'] . '.' . $token, 60 * 60 * 24 * 30, '', '/', '', (bool) service('request')->isSecure(), true, 'Lax');
        }
        $model->update($customer['id'], $update);
        return 'success';
    }
}
