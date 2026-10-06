<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        if(ENVIRONMENT==='production')throw new \RuntimeException('CatalogSeeder is development/demo data only.');$now=date('Y-m-d H:i:s');$names=['জৈব সার','Grow Bag','Coco Peat','Mulching Paper','Seedling Tray','বীজ ও চারা','Organic Pest Control','Gardening Packages'];
        foreach($names as $i=>$name){$slug=url_title($name,'-',true)?:'category-'.($i+1);if(!$this->db->table('product_categories')->where('slug',$slug)->get()->getRowArray())$this->db->table('product_categories')->insert(['name'=>$name,'slug'=>$slug,'sort_order'=>$i,'status'=>'active','created_at'=>$now,'updated_at'=>$now]);}
    }
}
