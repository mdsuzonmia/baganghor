<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCatalogTables extends Migration
{
    private function timestamps(bool $softDelete = false): array
    {
        $fields = ['created_at' => ['type' => 'DATETIME', 'null' => true], 'updated_at' => ['type' => 'DATETIME', 'null' => true]];
        if ($softDelete) $fields['deleted_at'] = ['type' => 'DATETIME', 'null' => true];
        return $fields;
    }

    public function up(): void
    {
        $this->categories(); $this->products(); $this->images(); $this->variants();
        $this->movements(); $this->packages(); $this->packageItems();
    }

    private function categories(): void
    {
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'parent_id'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],'name'=>['type'=>'VARCHAR','constraint'=>150],'slug'=>['type'=>'VARCHAR','constraint'=>180],'description'=>['type'=>'TEXT','null'=>true],'image'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],'icon'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'sort_order'=>['type'=>'INT','default'=>0],'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'active'],'seo_title'=>['type'=>'VARCHAR','constraint'=>190,'null'=>true],'seo_description'=>['type'=>'TEXT','null'=>true]]+$this->timestamps(true));
        $this->forge->addKey('id',true)->addUniqueKey('slug')->addKey('parent_id')->addKey('status')->addKey('sort_order')->addForeignKey('parent_id','product_categories','id','RESTRICT','CASCADE')->createTable('product_categories',true);
    }

    private function products(): void
    {
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'category_id'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],'product_code'=>['type'=>'VARCHAR','constraint'=>20],'name'=>['type'=>'VARCHAR','constraint'=>190],'slug'=>['type'=>'VARCHAR','constraint'=>220],'short_description'=>['type'=>'TEXT','null'=>true],'description'=>['type'=>'TEXT','null'=>true],'regular_price'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'sale_price'=>['type'=>'DECIMAL','constraint'=>'12,2','null'=>true],'purchase_cost'=>['type'=>'DECIMAL','constraint'=>'12,2','null'=>true],'sku'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'stock_type'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'simple'],'stock_quantity'=>['type'=>'INT','default'=>0],'low_stock_threshold'=>['type'=>'INT','default'=>5],'manage_stock'=>['type'=>'BOOLEAN','default'=>true],'allow_backorder'=>['type'=>'BOOLEAN','default'=>false],'weight'=>['type'=>'DECIMAL','constraint'=>'10,3','null'=>true],'weight_unit'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'kg'],'unit'=>['type'=>'VARCHAR','constraint'=>30,'null'=>true],'featured'=>['type'=>'BOOLEAN','default'=>false],'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'draft'],'seo_title'=>['type'=>'VARCHAR','constraint'=>190,'null'=>true],'seo_description'=>['type'=>'TEXT','null'=>true],'seo_keywords'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],'main_image'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],'sort_order'=>['type'=>'INT','default'=>0]]+$this->timestamps(true));
        $this->forge->addKey('id',true)->addUniqueKey('product_code')->addUniqueKey('slug')->addUniqueKey('sku')->addKey('category_id')->addKey('status')->addKey('featured')->addKey('stock_type')->addForeignKey('category_id','product_categories','id','SET NULL','CASCADE')->createTable('products',true);
    }

    private function images(): void
    {
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'product_id'=>['type'=>'BIGINT','unsigned'=>true],'image_path'=>['type'=>'VARCHAR','constraint'=>255],'alt_text'=>['type'=>'VARCHAR','constraint'=>190,'null'=>true],'sort_order'=>['type'=>'INT','default'=>0],'is_primary'=>['type'=>'BOOLEAN','default'=>false],'created_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true)->addKey('product_id')->addKey(['product_id','sort_order'])->addForeignKey('product_id','products','id','CASCADE','CASCADE')->createTable('product_images',true);
    }

    private function variants(): void
    {
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'product_id'=>['type'=>'BIGINT','unsigned'=>true],'variant_name'=>['type'=>'VARCHAR','constraint'=>150],'sku'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],'regular_price'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'sale_price'=>['type'=>'DECIMAL','constraint'=>'12,2','null'=>true],'purchase_cost'=>['type'=>'DECIMAL','constraint'=>'12,2','null'=>true],'stock_quantity'=>['type'=>'INT','default'=>0],'low_stock_threshold'=>['type'=>'INT','default'=>5],'weight'=>['type'=>'DECIMAL','constraint'=>'10,3','null'=>true],'weight_unit'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'kg'],'sort_order'=>['type'=>'INT','default'=>0],'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'active']]+$this->timestamps(true));
        $this->forge->addKey('id',true)->addUniqueKey('sku')->addKey('product_id')->addKey('status')->addForeignKey('product_id','products','id','CASCADE','CASCADE')->createTable('product_variants',true);
    }

    private function movements(): void
    {
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'product_id'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],'variant_id'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],'movement_type'=>['type'=>'VARCHAR','constraint'=>30],'quantity'=>['type'=>'INT'],'quantity_before'=>['type'=>'INT'],'quantity_after'=>['type'=>'INT'],'reference_type'=>['type'=>'VARCHAR','constraint'=>50,'null'=>true],'reference_id'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],'note'=>['type'=>'TEXT','null'=>true],'admin_user_id'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],'created_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true)->addKey('product_id')->addKey('variant_id')->addKey('created_at')->addForeignKey('product_id','products','id','SET NULL','CASCADE')->addForeignKey('variant_id','product_variants','id','SET NULL','CASCADE')->addForeignKey('admin_user_id','admin_users','id','SET NULL','CASCADE')->createTable('inventory_movements',true);
    }

    private function packages(): void
    {
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'name'=>['type'=>'VARCHAR','constraint'=>190],'slug'=>['type'=>'VARCHAR','constraint'=>220],'package_code'=>['type'=>'VARCHAR','constraint'=>20],'short_description'=>['type'=>'TEXT','null'=>true],'description'=>['type'=>'TEXT','null'=>true],'regular_total'=>['type'=>'DECIMAL','constraint'=>'12,2','null'=>true],'package_price'=>['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],'main_image'=>['type'=>'VARCHAR','constraint'=>255,'null'=>true],'featured'=>['type'=>'BOOLEAN','default'=>false],'status'=>['type'=>'VARCHAR','constraint'=>20,'default'=>'draft'],'seo_title'=>['type'=>'VARCHAR','constraint'=>190,'null'=>true],'seo_description'=>['type'=>'TEXT','null'=>true],'sort_order'=>['type'=>'INT','default'=>0]]+$this->timestamps(true));
        $this->forge->addKey('id',true)->addUniqueKey('slug')->addUniqueKey('package_code')->addKey('status')->addKey('featured')->createTable('product_packages',true);
    }

    private function packageItems(): void
    {
        $this->forge->addField(['id'=>['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],'package_id'=>['type'=>'BIGINT','unsigned'=>true],'product_id'=>['type'=>'BIGINT','unsigned'=>true],'variant_id'=>['type'=>'BIGINT','unsigned'=>true,'null'=>true],'quantity'=>['type'=>'INT','unsigned'=>true],'sort_order'=>['type'=>'INT','default'=>0],'created_at'=>['type'=>'DATETIME','null'=>true]]);
        $this->forge->addKey('id',true)->addUniqueKey(['package_id','product_id','variant_id'])->addKey('package_id')->addKey('product_id')->addForeignKey('package_id','product_packages','id','CASCADE','CASCADE')->addForeignKey('product_id','products','id','RESTRICT','CASCADE')->addForeignKey('variant_id','product_variants','id','RESTRICT','CASCADE')->createTable('product_package_items',true);
    }

    public function down(): void
    {
        foreach(['product_package_items','product_packages','inventory_movements','product_variants','product_images','products','product_categories'] as $table)$this->forge->dropTable($table,true);
    }
}
