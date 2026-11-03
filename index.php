<?php

/**
 * Laravel - Root Front Controller for Apache / Shared Hosting Environments
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// If the requested static file exists in public/, serve it with the proper Content-Type
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri) && !is_dir(__DIR__.'/public'.$uri)) {
    $filePath = __DIR__.'/public'.$uri;
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    $mimes = [
        'js'    => 'application/javascript',
        'css'   => 'text/css',
        'json'  => 'application/json',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'map'   => 'application/json',
    ];

    $contentType = $mimes[$ext] ?? (function_exists('mime_content_type') ? mime_content_type($filePath) : 'text/plain');
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
}

require_once __DIR__.'/public/index.php';
