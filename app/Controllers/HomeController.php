<?php

namespace App\Controllers;

use App\Services\HomepageContentService;
use App\Services\StorefrontService;
use App\Models\GuideModel;

class HomeController extends BaseController
{
    public function index()
    {
        $data = (new StorefrontService())->homepage();
        $content = (new HomepageContentService())->all();
        return view('frontend/home', $data + ['homepage'=>$content,'guides'=>(new GuideModel())->published()->orderBy('featured','DESC')->orderBy('published_at','DESC')->findAll(3),'title'=>$content['seo_title'],'metaDescription'=>$content['seo_description'],'ogImage'=>$content['hero_image']?:null]);
    }
}
