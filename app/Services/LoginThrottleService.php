<?php
namespace App\Services;
use App\Models\LoginAttemptModel;

class LoginThrottleService
{
    public function __construct(private ?LoginAttemptModel $model = null) { $this->model ??= new LoginAttemptModel(); }
    public function tooMany(string $type, string $identifier, string $ip): bool
    {
        $since = date('Y-m-d H:i:s', time() - 900);
        return $this->model->where(['login_type' => $type, 'identifier' => strtolower($identifier), 'ip_address' => $ip, 'success' => 0])->where('attempted_at >=', $since)->countAllResults() >= 5;
    }
    public function record(string $type, string $identifier, string $ip, bool $success): void
    {
        $this->model->insert(['login_type' => $type, 'identifier' => strtolower($identifier), 'ip_address' => $ip, 'attempted_at' => date('Y-m-d H:i:s'), 'success' => $success]);
        if ($success) $this->model->where(['login_type' => $type, 'identifier' => strtolower($identifier), 'ip_address' => $ip, 'success' => 0])->delete();
    }
}
