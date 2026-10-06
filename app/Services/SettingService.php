<?php
namespace App\Services;
use App\Models\SettingModel;

class SettingService
{
    public function all(string $group = 'general'): array
    {
        $rows = (new SettingModel())->where('group_name', $group)->findAll(); $result = [];
        foreach ($rows as $row) $result[$row['setting_key']] = $row['setting_value'];
        return $result;
    }
    public function setMany(string $group, array $values): void
    {
        $model = new SettingModel();
        foreach ($values as $key => $value) {
            $existing = $model->where(['group_name' => $group, 'setting_key' => $key])->first();
            $data = ['group_name' => $group, 'setting_key' => $key, 'setting_value' => $value, 'setting_type' => 'string'];
            $existing ? $model->update($existing['id'], $data) : $model->insert($data);
        }
    }
}
