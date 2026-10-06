<?php
namespace App\Services;
use App\Models\ProductModel; use App\Models\ProductPackageItemModel; use App\Models\ProductPackageModel; use App\Models\ProductVariantModel;
class PackageService
{
    public function save(?int $id,array $data,array $items): int
    {
        $clean=$this->validateItems($items); if(!$clean)throw new \RuntimeException('Add at least one valid package item.'); $db=db_connect(); $db->transException(true)->transStart(); $model=new ProductPackageModel(); $data['slug']=(new SlugService())->unique($model,$data['slug']?:$data['name'],$id);
        if($id)$model->update($id,$data); else{$data['package_code']='PENDING-'.bin2hex(random_bytes(6));$id=(int)$model->insert($data,true);$model->update($id,['package_code'=>sprintf('TAH-PKG-%06d',$id)]);}
        $itemModel=new ProductPackageItemModel(); $itemModel->where('package_id',$id)->delete(); foreach($clean as $i=>$item)$itemModel->insert($item+['package_id'=>$id,'sort_order'=>$i,'created_at'=>date('Y-m-d H:i:s')]); $total=$this->calculateRegularTotalFromItems($clean); $model->update($id,['regular_total'=>$total]); $db->transComplete(); return $id;
    }
    private function validateItems(array $items): array
    {
        $clean=[];$seen=[];$products=new ProductModel();$variants=new ProductVariantModel(); foreach($items as $row){$pid=(int)($row['product_id']??0);$vid=(int)($row['variant_id']??0);$qty=(int)($row['quantity']??0);if(!$pid||$qty<=0||!$products->find($pid))continue;if($vid && !$variants->where(['id'=>$vid,'product_id'=>$pid])->first())throw new \RuntimeException('A selected variant does not belong to its product.');$key=$pid.':'.$vid;if(isset($seen[$key]))throw new \RuntimeException('Duplicate package components are not allowed.');$seen[$key]=true;$clean[]=['product_id'=>$pid,'variant_id'=>$vid?:null,'quantity'=>$qty];}return $clean;
    }
    private function calculateRegularTotalFromItems(array $items): float { $total=0;$ps=new ProductService();foreach($items as $item){$row=$item['variant_id']?(new ProductVariantModel())->find($item['variant_id']):(new ProductModel())->find($item['product_id']);$total+=$ps->effectivePrice($row)*(int)$item['quantity'];}return round($total,2); }
    public function getPackageAvailableQuantity(int $id): int { $items=(new ProductPackageItemModel())->where('package_id',$id)->findAll();if(!$items)return 0;$available=PHP_INT_MAX;foreach($items as $item){$product=(new ProductModel())->find($item['product_id']);if(!$product||$product['status']!=='active')return 0;$row=$item['variant_id']?(new ProductVariantModel())->find($item['variant_id']):$product;if(!$row||($item['variant_id']&&$row['status']!=='active'))return 0;if(!$item['variant_id'] && !(bool)$row['manage_stock'])continue;$available=min($available,(int)floor((int)$row['stock_quantity']/(int)$item['quantity']));}return $available===PHP_INT_MAX?999999:max(0,$available);}
    public function calculateRegularTotal(int $id): float { return $this->calculateRegularTotalFromItems((new ProductPackageItemModel())->where('package_id',$id)->findAll()); }
}
