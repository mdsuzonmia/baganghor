<?php
namespace App\Services;
use App\Models\InventoryMovementModel; use App\Models\ProductModel; use App\Models\ProductVariantModel;
class InventoryService
{
    public function moveOrderStock($db, string $table, int $id, int $delta, int $productId, ?int $variantId, string $movementType, string $referenceType, ?int $referenceId, string $note, ?int $adminId = null): void
    {
        if (!in_array($table, ['products', 'product_variants'], true) || $delta === 0) throw new \InvalidArgumentException('Invalid order stock movement.');
        $row = $db->query('SELECT stock_quantity FROM ' . $db->prefixTable($table) . ' WHERE id=? FOR UPDATE', [$id])->getRowArray();
        if (!$row) throw new \RuntimeException('Stock item is missing; order stock movement was not completed.');
        $before = (int) $row['stock_quantity'];
        $after = $before + $delta;
        $now = date('Y-m-d H:i:s');
        $db->table($table)->where('id', $id)->update(['stock_quantity' => $after, 'updated_at' => $now]);
        $db->table('inventory_movements')->insert([
            'product_id' => $productId, 'variant_id' => $variantId,
            'movement_type' => $movementType, 'quantity' => $delta,
            'quantity_before' => $before, 'quantity_after' => $after,
            'reference_type' => $referenceType, 'reference_id' => $referenceId,
            'note' => $note, 'admin_user_id' => $adminId, 'created_at' => $now,
        ]);
    }
    public function adjustProductStock(int $id,string $mode,int $quantity,string $note): int { return $this->adjust('product',$id,$mode,$quantity,$note); }
    public function adjustVariantStock(int $id,string $mode,int $quantity,string $note): int { return $this->adjust('variant',$id,$mode,$quantity,$note); }
    private function adjust(string $kind,int $id,string $mode,int $quantity,string $note): int
    {
        if(trim($note)==='')throw new \InvalidArgumentException('A reason is required.'); if($quantity<0)throw new \InvalidArgumentException('Quantity cannot be negative.');
        $db=db_connect(); $db->transException(true)->transStart(); $table=$kind==='variant'?'product_variants':'products'; $row=$db->table($table)->where('id',$id)->get()->getRowArray(); if(!$row)throw new \RuntimeException('Inventory item not found.');
        if($kind==='product' && $row['stock_type']!=='simple')throw new \RuntimeException('Stock must be adjusted on a variant.'); $before=(int)$row['stock_quantity']; $after=match($mode){'add'=>$before+$quantity,'remove'=>$before-$quantity,'set'=>$quantity,default=>throw new \InvalidArgumentException('Invalid adjustment type.')};
        if($after<0 && !($kind==='product' && (bool)$row['allow_backorder']))throw new \RuntimeException('This adjustment would make stock negative.'); $db->table($table)->where('id',$id)->update(['stock_quantity'=>$after,'updated_at'=>date('Y-m-d H:i:s')]);
        (new InventoryMovementModel())->insert(['product_id'=>$kind==='product'?$id:$row['product_id'],'variant_id'=>$kind==='variant'?$id:null,'movement_type'=>'manual','quantity'=>$after-$before,'quantity_before'=>$before,'quantity_after'=>$after,'note'=>trim($note),'admin_user_id'=>session('admin_id'),'created_at'=>date('Y-m-d H:i:s')]); $db->transComplete();
        (new ActivityLogService())->log('inventory.adjust','inventory','Stock adjusted.',['kind'=>$kind,'id'=>$id]); return $after;
    }
    public function stockStatus(int $quantity,int $threshold): string { return $quantity<=0?'out_of_stock':($quantity<=$threshold?'low_stock':'in_stock'); }
}
