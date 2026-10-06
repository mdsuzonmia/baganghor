<?php
namespace App\Services;
use CodeIgniter\HTTP\Files\UploadedFile;
class ImageUploadService
{
    private const MIME=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    public function store(?UploadedFile $file,string $folder): ?string
    {
        if(!$file || $file->getError()===UPLOAD_ERR_NO_FILE)return null;
        if(!$file->isValid() || $file->hasMoved() || $file->getSize()>5*1024*1024)throw new \RuntimeException('Please upload a valid image no larger than 5 MB.');
        $mime=$file->getMimeType(); if(!isset(self::MIME[$mime]) || @getimagesize($file->getTempName())===false)throw new \RuntimeException('Only valid JPG, PNG, and WebP images are allowed.');
        $relative='uploads/'.$folder.'/'.date('Y/m'); $directory=FCPATH.$relative;
        if(!is_dir($directory) && !mkdir($directory,0755,true) && !is_dir($directory))throw new \RuntimeException('Could not create the upload directory.');
        $name=bin2hex(random_bytes(16)).'.'.self::MIME[$mime]; $file->move($directory,$name);
        return $relative.'/'.$name;
    }
    public function delete(?string $path): void
    {
        if(!$path || !str_starts_with($path,'uploads/'))return; $full=realpath(FCPATH.$path); $root=realpath(FCPATH.'uploads');
        if($full && $root && str_starts_with($full,$root.DIRECTORY_SEPARATOR) && is_file($full))unlink($full);
    }
}
