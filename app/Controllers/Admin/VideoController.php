<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\VideoCategoryModel;
use App\Models\VideoModel;
use App\Services\ActivityLogService;
use App\Services\GuideContentService;
use App\Services\ImageUploadService;
use App\Services\SlugService;
use App\Services\YouTubeService;
use CodeIgniter\Exceptions\PageNotFoundException;

class VideoController extends BaseController
{
    public function index()
    {
        $model = new VideoModel();
        $q = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('status');
        $category = (int) $this->request->getGet('category_id');
        if ($q !== '') $model->groupStart()->like('title', $q)->orLike('short_description', $q)->groupEnd();
        if (in_array($status, ['draft', 'published'], true)) $model->where('status', $status);
        if ($category) $model->where('category_id', $category);
        return view('admin/videos/index', ['title' => 'Videos', 'rows' => $model->orderBy('sort_order')->orderBy('created_at', 'DESC')->paginate(20), 'pager' => $model->pager, 'q' => $q, 'status' => $status, 'category' => $category, 'categories' => (new VideoCategoryModel())->orderBy('name')->findAll()]);
    }

    public function create() { return $this->form(); }
    public function edit(int $id) { return $this->form($id); }

    private function form(?int $id = null)
    {
        $row = $id ? (new VideoModel())->find($id) : null;
        if ($id && !$row) throw PageNotFoundException::forPageNotFound();
        $productIds = $id ? array_column(db_connect()->table('video_products')->select('product_id')->where('video_id', $id)->get()->getResultArray(), 'product_id') : [];
        return view('admin/videos/form', ['title' => $id ? 'Edit Video' : 'New Video', 'row' => $row, 'categories' => (new VideoCategoryModel())->orderBy('name')->findAll(), 'products' => (new ProductModel())->where('status', 'active')->orderBy('name')->findAll(), 'productIds' => $productIds]);
    }

    public function store() { return $this->save(); }
    public function update(int $id) { return $this->save($id); }

    private function save(?int $id = null)
    {
        $model = new VideoModel();
        $old = $id ? $model->find($id) : null;
        if ($id && !$old) throw PageNotFoundException::forPageNotFound();
        if (!$this->validate(['title' => 'required|min_length[3]|max_length[190]', 'youtube_url' => 'required|max_length[500]', 'content' => 'required', 'duration' => 'permit_empty|max_length[20]', 'status' => 'required|in_list[draft,published]', 'sort_order' => 'permit_empty|integer'])) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());

        $youtubeUrl = trim((string) $this->request->getPost('youtube_url'));
        $youtubeId = (new YouTubeService())->videoId($youtubeUrl);
        if (!$youtubeId) return redirect()->back()->withInput()->with('danger', 'Enter a valid YouTube video URL.');
        $category = (int) $this->request->getPost('category_id') ?: null;
        if ($category && !(new VideoCategoryModel())->find($category)) return redirect()->back()->withInput()->with('danger', 'Invalid category.');
        $content = (new GuideContentService())->clean((string) $this->request->getPost('content'));
        if (trim(strip_tags($content)) === '') return redirect()->back()->withInput()->with('danger', 'Full guide content is required.');
        $data = [
            'category_id' => $category,
            'title' => trim((string) $this->request->getPost('title')),
            'slug' => (new SlugService())->unique($model, (string) ($this->request->getPost('slug') ?: $this->request->getPost('title')), $id),
            'youtube_url' => $youtubeUrl,
            'youtube_id' => $youtubeId,
            'short_description' => trim((string) $this->request->getPost('short_description')) ?: null,
            'content' => $content,
            'duration' => trim((string) $this->request->getPost('duration')) ?: null,
            'status' => (string) $this->request->getPost('status'),
            'featured' => $this->request->getPost('featured') ? 1 : 0,
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'seo_title' => trim((string) $this->request->getPost('seo_title')) ?: null,
            'seo_description' => trim((string) $this->request->getPost('seo_description')) ?: null,
        ];
        try {
            $thumbnail = (new ImageUploadService())->store($this->request->getFile('thumbnail'), 'videos');
            if ($thumbnail) $data['thumbnail'] = $thumbnail;
        } catch (\RuntimeException $exception) {
            return redirect()->back()->withInput()->with('danger', $exception->getMessage());
        }
        $db = db_connect();
        $db->transStart();
        $id ? $model->update($id, $data) : $id = (int) $model->insert($data, true);
        $db->table('video_products')->where('video_id', $id)->delete();
        $requested = array_unique(array_filter(array_map('intval', (array) $this->request->getPost('product_id'))));
        if ($requested) {
            $valid = array_column($db->table('products')->select('id')->whereIn('id', $requested)->get()->getResultArray(), 'id');
            foreach ($valid as $productId) $db->table('video_products')->insert(['video_id' => $id, 'product_id' => $productId]);
        }
        $db->transComplete();
        if (!$db->transStatus()) return redirect()->back()->withInput()->with('danger', 'Could not save video.');
        (new ActivityLogService())->log($old ? 'video.update' : 'video.create', 'videos', 'Video saved.', ['id' => $id]);
        return redirect()->to(url_to('admin.videos'))->with('success', 'Saved.');
    }

    public function delete(int $id)
    {
        (new VideoModel())->delete($id);
        (new ActivityLogService())->log('video.delete', 'videos', 'Video deleted.', ['id' => $id]);
        return redirect()->back()->with('success', 'Deleted.');
    }
}
