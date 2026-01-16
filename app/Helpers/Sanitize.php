<?php
namespace App\Helpers;
use HTMLPurifier;
use HTMLPurifier_Config;

function cleanLimitedHtml(?string $html): string
{
    // pick a cache path under storage
    $cachePath = storage_path('app/purifier');

    // ensure directory exists (works on Windows & Unix)
    if (!is_dir($cachePath)) {
        @mkdir($cachePath, 0775, true);
    }
    // fallback to an existing cache folder if creation failed
    if (!is_dir($cachePath) || !is_writable($cachePath)) {
        $cachePath = storage_path('framework/cache'); // usually exists
    }

    $config = \HTMLPurifier_Config::createDefault();
    $config->set('Cache.SerializerPath', $cachePath);
    $config->set('HTML.Allowed', 'b,strong,i,em,u,ul,ol,li,a[href]');

    return (new \HTMLPurifier($config))->purify($html ?? '');
}


