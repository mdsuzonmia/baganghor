<?php
namespace App\Services;
use App\Models\ProductImageModel; use App\Models\ProductModel; use App\Models\ProductVariantModel;
class ProductService
{
    public function save(?int $id,array $data,array $variants=[],array $files=[]): int
    {
        $db=db_connect(); $db->transException(true)->transStart(); $model=new ProductModel();
        $data['slug']=(new SlugService())->unique($model,$data['slug']?:$data['name'],$id);
        if($data['stock_type']==='variant'){ $data['sku']=null; $data['stock_quantity']=0; }
        if($id)$model->update($id,$data); else { $data['product_code']='PENDING-'.bin2hex(random_bytes(6)); $id=(int)$model->insert($data,true); $model->update($id,['product_code'=>sprintf('TAH-PRO-%06d',$id)]); }
        $this->syncVariants($id,$data['stock_type'],$variants);
        $this->storeImages($id,$files,$data['name']); $db->transComplete(); return $id;
    }
    private function syncVariants(int $productId,string $type,array $rows): void
    {
        $model=new ProductVariantModel(); if($type!=='variant'){ $model->where('product_id',$productId)->delete(); return; }
        $kept=[]; $submittedSkus=[];
        foreach($rows as $i=>$row){ if(trim((string)($row['variant_name']??''))==='')continue; $payload=['product_id'=>$productId,'variant_name'=>trim($row['variant_name']),'sku'=>trim((string)($row['sku']??''))?:null,'regular_price'=>(float)($row['regular_price']??0),'sale_price'=>$this->nullableMoney($row['sale_price']??null),'purchase_cost'=>$this->nullableMoney($row['purchase_cost']??null),'stock_quantity'=>max(0,(int)($row['stock_quantity']??0)),'low_stock_threshold'=>max(0,(int)($row['low_stock_threshold']??5)),'weight'=>$this->nullableMoney($row['weight']??null),'weight_unit'=>$row['weight_unit']??'kg','sort_order'=>$i,'status'=>in_array($row['status']??'', ['active','inactive'],true)?$row['status']:'active']; $variantId=(int)($row['id']??0); if($payload['sale_price']!==null && $payload['sale_price']>$payload['regular_price'])throw new \RuntimeException('Variant sale price cannot exceed its regular price.'); if($payload['sku']){if(isset($submittedSkus[strtolower($payload['sku'])])||(new ProductModel())->where('sku',$payload['sku'])->first())throw new \RuntimeException('A variant SKU is duplicated or already used by a product.');$duplicate=(new ProductVariantModel())->where('sku',$payload['sku']);if($variantId)$duplicate->where('id !=',$variantId);if($duplicate->first())throw new \RuntimeException('A variant SKU is already in use.');$submittedSkus[strtolower($payload['sku'])]=true;} if($variantId && $model->where(['id'=>$variantId,'product_id'=>$productId])->first())$model->update($variantId,$payload); else $variantId=(int)$model->insert($payload,true); $kept[]=$variantId; }
        $query=$model->where('product_id',$productId); if($kept)$query->whereNotIn('id',$kept); $query->delete();
    }
    private function storeImages(int $productId,array $files,string $name): void
    {
        $upload=new ImageUploadService(); $images=new ProductImageModel(); $sort=(int)$images->where('product_id',$productId)->countAllResults();
        foreach($files as $file){ $path=$upload->store($file,'products'); if(!$path)continue; $primary=$images->where('product_id',$productId)->countAllResults()===0; $images->insert(['product_id'=>$productId,'image_path'=>$path,'alt_text'=>$name,'sort_order'=>$sort++,'is_primary'=>$primary,'created_at'=>date('Y-m-d H:i:s')]); if($primary)(new ProductModel())->update($productId,['main_image'=>$path]); }
    }
    private function nullableMoney(mixed $value): ?float { return $value===''||$value===null?null:(float)$value; }
    public function effectivePrice(array $item): float { $sale=$item['sale_price']??null; return $sale!==null && (float)$sale<=(float)$item['regular_price']?(float)$sale:(float)$item['regular_price']; }
}
