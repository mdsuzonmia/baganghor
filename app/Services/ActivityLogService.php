<?php
namespace App\Services;
use App\Models\ActivityLogModel;

class ActivityLogService
{
    public function log(string $action, string $module, ?string $description = null, array $metadata = []): void
    {
        $request = service('request');
        (new ActivityLogModel())->insert(['admin_user_id' => session('admin_id'), 'action' => $action, 'module' => $module, 'description' => $description, 'ip_address' => $request->getIPAddress(), 'user_agent' => substr($request->getUserAgent()->getAgentString(), 0, 255), 'metadata' => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null, 'created_at' => date('Y-m-d H:i:s')]);
    }
}
