<?php
namespace App\Controllers;use App\Models\UpazilaModel;
class LocationController extends BaseController{public function upazilas(int $districtId){return $this->response->setJSON((new UpazilaModel())->select('id,name_bn,name_en')->where(['district_id'=>$districtId,'status'=>'active'])->orderBy('sort_order')->orderBy('name_bn')->findAll());}}
