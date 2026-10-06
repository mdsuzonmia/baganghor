<?php

namespace App\Models;

use CodeIgniter\Model;

class VideoCategoryModel extends Model
{
    protected $table = 'video_categories';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'slug', 'description', 'status', 'sort_order', 'seo_title', 'seo_description'];
}
