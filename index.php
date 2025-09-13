<?php
/**
 * Project Demo (ionCube-ready) — single-file entry
 * - If ionCube Loader is present and encoded build exists -> include dist/app_encoded.php
 * - Else fallback to runtime obfuscated build -> runtime/app_runtime.php
 * - Else load plain source -> app_plain.php
 */

declare(strict_types=1);

function has_ioncube(): bool {
    return extension_loaded('ionCube Loader') || extension_loaded('ionCube Loader' . PHP_MAJOR_VERSION);
}

$dist = __DIR__ . '/dist/app_encoded.php';
$rt   = __DIR__ . '/runtime/app_runtime.php';
$src  = __DIR__ . '/app_plain.php';

if (has_ioncube() && is_file($dist)) {
    require $dist; // <- real ionCube-encoded payload (build with build.sh)
    exit;
}

if (is_file($rt)) {
    require $rt;   // <- fallback: simple encoded payload (base64+zlib)
    exit;
}

require $src;      // <- development/plain mode
