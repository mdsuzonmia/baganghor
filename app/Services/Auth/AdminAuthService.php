<?php
namespace App\Services\Auth;
use App\Models\AdminUserModel;
use App\Services\ActivityLogService;
use App\Services\LoginThrottleService;

class AdminAuthService
{
    public function attempt(string $email, string $password, string $ip): string
    {
        $email = strtolower(trim($email)); $throttle = new LoginThrottleService();
        if ($throttle->tooMany('admin', $email, $ip)) return 'throttled';
        $model = new AdminUserModel(); $admin = $model->withRole()->where('admin_users.email', $email)->first();
        if (! $admin || ! password_verify($password, $admin['password_hash'])) { $throttle->record('admin', $email, $ip, false); return 'invalid'; }
        if ($admin['status'] !== 'active') return $admin['status'];
        $throttle->record('admin', $email, $ip, true); session()->regenerate(true);
        session()->set(['admin_id' => $admin['id'], 'admin_name' => $admin['name'], 'admin_role' => $admin['role_slug'], 'admin_logged_in' => true]);
        $model->update($admin['id'], ['last_login_at' => date('Y-m-d H:i:s'), 'last_login_ip' => $ip]);
        (new ActivityLogService())->log('admin.login', 'authentication', 'Admin logged in.');
        return 'success';
    }
}
