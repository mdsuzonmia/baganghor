<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController; use App\Services\ActivityLogService; use App\Services\ImageUploadService; use App\Services\SettingService;
class SettingsController extends BaseController
{
    private array $keys=['site_name','site_tagline','site_logo','favicon','support_mobile','support_email','facebook_url','youtube_url','currency','currency_symbol','timezone','default_language'];
    public function index(){return view('admin/settings/index',['title'=>'Settings','settings'=>(new SettingService())->all()]);}
    public function update()
    {
        $rules=['site_name'=>'required|max_length[150]','support_email'=>'permit_empty|valid_email|max_length[190]','facebook_url'=>'permit_empty|valid_url_strict','youtube_url'=>'permit_empty|valid_url_strict','timezone'=>'required|max_length[80]'];
        if(!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());

        $settings=new SettingService();
        $current=$settings->all();
        $values=[];
        foreach($this->keys as $key) {
            if(in_array($key,['site_logo','favicon'],true)) continue;
            $values[$key]=trim((string)$this->request->getPost($key));
        }

        $upload=new ImageUploadService();
        $newFiles=[];
        try {
            foreach(['site_logo'=>'branding/logo','favicon'=>'branding/favicon'] as $key=>$folder) {
                $newPath=$upload->store($this->request->getFile($key),$folder);
                $remove=(bool)$this->request->getPost('remove_'.$key);
                $values[$key]=$newPath ?: ($remove ? '' : ($current[$key]??''));
                if($newPath) $newFiles[]=$newPath;
            }
            $settings->setMany('general',$values);
        } catch(\Throwable $e) {
            foreach($newFiles as $path) $upload->delete($path);
            return redirect()->back()->withInput()->with('danger',$e->getMessage());
        }

        foreach(['site_logo','favicon'] as $key) {
            $old=$current[$key]??'';
            if($old && $old!==$values[$key]) $upload->delete($old);
        }
        (new ActivityLogService())->log('settings.update','settings','General settings updated.');
        return redirect()->back()->with('success','Settings updated successfully.');
    }
}
