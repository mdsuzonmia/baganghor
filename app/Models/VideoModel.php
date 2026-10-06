<?php

namespace App\Models;

use CodeIgniter\Model;

class VideoModel extends Model
{
    protected $table = 'videos';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['category_id', 'title', 'slug', 'youtube_url', 'youtube_id', 'thumbnail', 'short_description', 'content', 'duration', 'status', 'featured', 'sort_order', 'views', 'seo_title', 'seo_description'];

    public function published(): self
    {
        return $this->where('videos.status', 'published');
    }
}
