<?php

namespace App\Services;

class CardImageService
{
    public function url(?string $path, string $type): string
    {
        if (!$path || !in_array($type, ['product', 'package'], true)) {
            return store_image($path);
        }

        $folder = $type === 'product' ? 'products' : 'packages';
        if (!preg_match('~^uploads/' . $folder . '/[a-zA-Z0-9/_-]+\.(?:jpe?g|png|webp)$~i', $path)) {
            return store_image($path);
        }

        $source = realpath(FCPATH . $path);
        $root = realpath(FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $folder);
        if (!$source || !$root || !str_starts_with($source, $root . DIRECTORY_SEPARATOR) || !is_file($source)) {
            return store_image($path);
        }

        [$width, $height] = $type === 'product' ? [480, 480] : [640, 440];
        $relative = preg_replace('/\.[^.]+$/', '-card-contain-' . $width . 'x' . $height . '.webp', $path);
        $target = FCPATH . $relative;

        if (!is_file($target) || filemtime($target) < filemtime($source)) {
            try {
                $temp = dirname($target) . DIRECTORY_SEPARATOR . bin2hex(random_bytes(8)) . '.webp';
                service('image')->withFile($source)->resize($width, $height, true)->save($temp, 82);
                if (!rename($temp, $target)) {
                    @unlink($temp);
                    return store_image($path);
                }
            } catch (\Throwable $e) {
                if (isset($temp) && is_file($temp)) @unlink($temp);
                log_message('error', 'Card image creation failed: {error}', ['error' => $e->getMessage()]);
                return store_image($path);
            }
        }

        return base_url($relative);
    }
}
