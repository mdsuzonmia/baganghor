<?php
namespace App\Services;
use CodeIgniter\Model;
class SlugService
{
    public function unique(Model $model, string $value, ?int $ignoreId=null): string
    {
        $base=url_title(trim($value),'-',true) ?: 'item'; $slug=$base; $suffix=2;
        while($this->exists($model,$slug,$ignoreId))$slug=$base.'-'.$suffix++;
        return $slug;
    }
    private function exists(Model $model,string $slug,?int $ignoreId): bool { $query=clone $model; $query->withDeleted()->where('slug',$slug); if($ignoreId)$query->where('id !=',$ignoreId); return (bool)$query->first(); }
}
