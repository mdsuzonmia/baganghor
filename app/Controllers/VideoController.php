<?php

namespace App\Controllers;

use App\Models\VideoCategoryModel;
use App\Models\VideoModel;
use App\Services\GuideContentService;
use App\Services\StorefrontService;
use CodeIgniter\Exceptions\PageNotFoundException;

class VideoController extends BaseController
{
    public function index()
    {
        $categorySlug = trim((string) $this->request->getGet('category'));
        $category = null;
        $model = (new VideoModel())->published();
        if ($categorySlug !== '') {
            $category = (new VideoCategoryModel())->where(['slug' => $categorySlug, 'status' => 'active'])->first();
            if (!$category) throw PageNotFoundException::forPageNotFound();
            $model->where('category_id', $category['id']);
        }
        $rows = $model->orderBy('featured', 'DESC')->orderBy('sort_order')->orderBy('created_at', 'DESC')->paginate(12);
        $featured = $category ? [] : (new VideoModel())->published()->where('featured', 1)->orderBy('sort_order')->orderBy('created_at', 'DESC')->findAll(3);
        $store = new StorefrontService();
        return view('frontend/videos/index', [
            'title' => $category ? ($category['seo_title'] ?: $category['name']) : 'ভিডিও গাইড',
            'metaDescription' => $category ? ($category['seo_description'] ?: $category['description']) : 'বাগান, কৃষি ও পণ্য ব্যবহারের বাংলা ভিডিও গাইড।',
            'canonical' => $category ? site_url('videos?category=' . rawurlencode($category['slug'])) : url_to('videos.index'),
            'rows' => $rows, 'featured' => $featured, 'pager' => $model->pager, 'category' => $category,
            'categories' => (new VideoCategoryModel())->where('status', 'active')->orderBy('sort_order')->orderBy('name')->findAll(),
            'settings' => $store->settings(),
        ]);
    }

    public function show(string $slug)
    {
        $video = (new VideoModel())->published()->where('slug', $slug)->first();
        if (!$video) throw PageNotFoundException::forPageNotFound();
        $category = $video['category_id'] ? (new VideoCategoryModel())->where(['id' => $video['category_id'], 'status' => 'active'])->first() : null;
        $db = db_connect();
        $products = $db->table('video_products vp')->select('p.*')->join('products p', 'p.id=vp.product_id')->where('vp.video_id', $video['id'])->where('p.status', 'active')->where('p.deleted_at', null)->get()->getResultArray();
        $relatedModel = (new VideoModel())->published()->where('id !=', $video['id']);
        if ($video['category_id']) $relatedModel->where('category_id', $video['category_id']);
        $related = $relatedModel->orderBy('featured', 'DESC')->orderBy('sort_order')->orderBy('created_at', 'DESC')->findAll(3);
        [$content, $toc] = (new GuideContentService())->outline($video['content']);
        $db->table('videos')->where('id', $video['id'])->set('views', 'views+1', false)->update();
        $store = new StorefrontService();
        return view('frontend/videos/show', [
            'title' => $video['seo_title'] ?: $video['title'], 'metaDescription' => $video['seo_description'] ?: $video['short_description'],
            'canonical' => url_to('videos.show', $slug), 'ogImage' => $video['thumbnail'] ?: 'https://i.ytimg.com/vi/' . $video['youtube_id'] . '/hqdefault.jpg', 'ogType' => 'video.other',
            'video' => $video, 'content' => $content, 'category' => $category, 'toc' => $toc, 'products' => $store->decorateProducts($products), 'related' => $related, 'settings' => $store->settings(),
        ]);
    }
}
