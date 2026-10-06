<?php
namespace App\Services;

class MobileNumberService
{
    public function normalize(?string $mobile): ?string
    {
        $mobile = preg_replace('/[\s\-().]/', '', trim((string) $mobile));
        if (str_starts_with($mobile, '+880')) $mobile = '0' . substr($mobile, 4);
        elseif (str_starts_with($mobile, '880')) $mobile = '0' . substr($mobile, 3);
        return preg_match('/^01[3-9]\d{8}$/', $mobile) ? $mobile : null;
    }
}
