<?php

if (! function_exists('format_bdt')) {
    function format_bdt(float|int|string|null $amount): string
    {
        $number = (float) ($amount ?? 0);
        $decimals = abs($number - round($number)) < 0.001 ? 0 : 2;
        return '৳' . number_format($number, $decimals);
    }
}

if (! function_exists('store_image')) {
    function store_image(?string $path): string
    {
        return $path ? base_url($path) : base_url('assets/frontend/images/placeholder.svg');
    }
}

if (! function_exists('store_card_image')) {
    function store_card_image(?string $path, string $type): string
    {
        return (new \App\Services\CardImageService())->url($path, $type);
    }
}
