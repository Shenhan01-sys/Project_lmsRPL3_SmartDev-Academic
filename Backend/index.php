<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Ini agar file asset (gambar/css) tetap bisa diakses langsung
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

// Panggil file index.php yang ASLI di dalam folder public
require_once __DIR__.'/public/index.php';