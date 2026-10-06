<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ActivityLogService;
use App\Services\PolicyPageService;

class PolicyController extends BaseController
{
    public function index()
    {
        return view('admin/policies/index', [
            'title'=>'Policy Pages',
            'pages'=>PolicyPageService::PAGES,
            'content'=>(new PolicyPageService())->all(),
        ]);
    }

    public function update()
    {
        $values=[];
        foreach (PolicyPageService::PAGES as $key=>$page) {
            $body=trim((string)$this->request->getPost($key));
            if (mb_strlen($body)<50 || mb_strlen($body)>30000) {
                return redirect()->back()->withInput()->with('danger',$page['title'].'-এর লেখা ৫০ থেকে ৩০,০০০ অক্ষরের মধ্যে রাখুন।');
            }
            $values[$key]=$body;
        }
        (new PolicyPageService())->save($values);
        (new ActivityLogService())->log('policies.update','settings','Storefront policy pages updated.');
        return redirect()->back()->with('success','নীতির পেজগুলো সংরক্ষণ করা হয়েছে।');
    }
}
