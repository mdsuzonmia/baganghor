<?php

namespace App\Services;

class YouTubeService
{
    public function videoId(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') return null;
        if (preg_match('~^[A-Za-z0-9_-]{11}$~', $url)) return $url;

        $parts = parse_url($url);
        if (!$parts || empty($parts['host'])) return null;
        $host = strtolower(preg_replace('/^www\./', '', $parts['host']));
        $id = null;
        if ($host === 'youtu.be') $id = trim($parts['path'] ?? '', '/');
        elseif (in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com'], true)) {
            if (($parts['path'] ?? '') === '/watch') {
                parse_str($parts['query'] ?? '', $query);
                $id = $query['v'] ?? null;
            } elseif (preg_match('~^/(?:embed|shorts|live)/([^/?]+)~', $parts['path'] ?? '', $match)) $id = $match[1];
        }
        return is_string($id) && preg_match('~^[A-Za-z0-9_-]{11}$~', $id) ? $id : null;
    }

    public function thumbnail(string $id): string
    {
        return 'https://i.ytimg.com/vi/' . rawurlencode($id) . '/hqdefault.jpg';
    }
}
