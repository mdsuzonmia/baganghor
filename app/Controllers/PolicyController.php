<?php
namespace App\Controllers;

use App\Services\PolicyPageService;
use App\Services\StorefrontService;

class PolicyController extends BaseController
{
    public function show(string $key)
    {
        $page=(new PolicyPageService())->get($key);
        if (!$page) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('frontend/policies/show', [
            'title'=>$page['title'],
            'metaDescription'=>$page['title'].' — Taharat Agro-এর কেনাকাটা ও সেবা সম্পর্কিত তথ্য।',
            'page'=>$page,
            'settings'=>(new StorefrontService())->settings(),
        ]);
    }
}
