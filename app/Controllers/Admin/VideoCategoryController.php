<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VideoCategoryModel;
use App\Models\VideoModel;
use App\Services\ActivityLogService;
use App\Services\SlugService;
use CodeIgniter\Exceptions\PageNotFoundException;

class VideoCategoryController extends BaseController
{
    public function index()
    {
        $model = new VideoCategoryModel();
        $q = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('status');
        if ($q !== '') $model->like('name', $q);
        if (in_array($status, ['active', 'inactive'], true)) $model->where('status', $status);
        return view('admin/videos/categories/index', ['title' => 'Video Categories', 'rows' => $model->orderBy('sort_order')->orderBy('name')->paginate(20), 'pager' => $model->pager, 'q' => $q, 'status' => $status]);
    }

    public function create() { return $this->form(); }
    public function edit(int $id) { return $this->form($id); }

    private function form(?int $id = null)
    {
        $row = $id ? (new VideoCategoryModel())->find($id) : null;
        if ($id && !$row) throw PageNotFoundException::forPageNotFound();
        return view('admin/videos/categories/form', ['title' => $id ? 'Edit Video Category' : 'New Video Category', 'row' => $row]);
    }

    public function store() { return $this->save(); }
    public function update(int $id) { return $this->save($id); }

    private function save(?int $id = null)
    {
        $model = new VideoCategoryModel();
        if ($id && !$model->find($id)) throw PageNotFoundException::forPageNotFound();
        if (!$this->validate(['name' => 'required|min_length[2]|max_length[150]', 'status' => 'required|in_list[active,inactive]', 'sort_order' => 'permit_empty|integer'])) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        $data = [
            'name' => trim((string) $this->request->getPost('name')),
            'slug' => (new SlugService())->unique($model, (string) ($this->request->getPost('slug') ?: $this->request->getPost('name')), $id),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'status' => (string) $this->request->getPost('status'),
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'seo_title' => trim((string) $this->request->getPost('seo_title')) ?: null,
            'seo_description' => trim((string) $this->request->getPost('seo_description')) ?: null,
        ];
        $id ? $model->update($id, $data) : $id = (int) $model->insert($data, true);
        (new ActivityLogService())->log('video_category.save', 'videos', 'Video category saved.', ['id' => $id]);
        return redirect()->to(url_to('admin.video.categories'))->with('success', 'Saved.');
    }

    public function delete(int $id)
    {
        if ((new VideoModel())->where('category_id', $id)->countAllResults()) return redirect()->back()->with('danger', 'Move videos from this category first.');
        (new VideoCategoryModel())->delete($id);
        (new ActivityLogService())->log('video_category.delete', 'videos', 'Video category deleted.', ['id' => $id]);
        return redirect()->back()->with('success', 'Deleted.');
    }
}
