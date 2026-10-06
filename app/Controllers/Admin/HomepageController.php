<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductPackageModel;
use App\Services\ActivityLogService;
use App\Services\HomepageContentService;
use App\Services\ImageUploadService;

class HomepageController extends BaseController
{
    private array $toggles=['hero_enabled','trust_enabled','categories_enabled','products_enabled','packages_enabled','guides_enabled','support_enabled'];
    private array $fields=['seo_title','seo_description','hero_kicker','hero_heading','hero_subheading','hero_primary_text','hero_secondary_text','trust_1','trust_2','trust_3','trust_4','categories_kicker','categories_heading','products_kicker','products_heading','packages_kicker','packages_heading','packages_subheading','guides_kicker','guides_heading','guide_1_title','guide_1_description','guide_2_title','guide_2_description','guide_3_title','guide_3_description','guides_badge','support_heading','support_subheading','support_call_text','support_facebook_text'];
    public function index()
    {
        $packages = (new ProductPackageModel())->where('status', 'active')->orderBy('sort_order')->orderBy('name')->findAll();
        return view('admin/homepage/index', [
            'title' => 'Homepage Content',
            'content' => (new HomepageContentService())->all(),
            'packages' => $packages,
            'homepagePackageIds' => array_map('intval', array_column(
                array_filter($packages, static fn(array $package): bool => (bool) $package['featured']),
                'id'
            )),
        ]);
    }
    public function update()
    {
        $rules=['seo_title'=>'required|max_length[190]','seo_description'=>'required|max_length[300]','hero_heading'=>'required|max_length[190]','hero_subheading'=>'required|max_length[300]','categories_heading'=>'required|max_length[150]','products_heading'=>'required|max_length[150]','packages_heading'=>'required|max_length[150]','guides_heading'=>'required|max_length[150]','support_heading'=>'required|max_length[190]'];
        if(!$this->validate($rules))return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $packageIds=array_values(array_unique(array_filter(array_map('intval',(array)$this->request->getPost('homepage_package_ids')))));
        if(count($packageIds)>4)return redirect()->back()->withInput()->with('danger','Select no more than 4 packages for the homepage.');
        if($packageIds){$validIds=array_map('intval',array_column((new ProductPackageModel())->select('id')->where('status','active')->whereIn('id',$packageIds)->findAll(),'id'));if(count($validIds)!==count($packageIds))return redirect()->back()->withInput()->with('danger','One or more selected packages are unavailable.');}
        $service=new HomepageContentService();$current=$service->all();$values=[];
        foreach($this->fields as $key)$values[$key]=trim((string)$this->request->getPost($key));
        foreach($this->toggles as $key)$values[$key]=$this->request->getPost($key)?'1':'0';
        $upload=new ImageUploadService();$newPath=null;
        try{$newPath=$upload->store($this->request->getFile('hero_image'),'homepage');$values['hero_image']=$newPath?:($this->request->getPost('remove_hero_image')?'':$current['hero_image']);$service->set($values);}catch(\Throwable $e){if($newPath)$upload->delete($newPath);return redirect()->back()->withInput()->with('danger',$e->getMessage());}
        $packageModel=new ProductPackageModel();$packageModel->where('featured',1)->set(['featured'=>0])->update();if($packageIds)$packageModel->whereIn('id',$packageIds)->set(['featured'=>1])->update();
        if(!empty($current['hero_image'])&&$current['hero_image']!==$values['hero_image'])$upload->delete($current['hero_image']);
        (new ActivityLogService())->log('homepage.update','settings','Homepage content and selected packages updated.',['package_ids'=>$packageIds]);
        return redirect()->back()->with('success','Homepage content updated successfully.');
    }
}
