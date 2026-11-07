<?php

declare(strict_types=1);

/**
 * Builds assets/build/app.min.css from the stylesheets listed in config/App/Assets.php.
 * Run after any CSS change; tests/Unit/Config/AssetsTest.php fails while the bundle is stale.
 * Usage: php bin/build-assets.php
 */

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

require __DIR__ . '/../config/App/bootstrap.php';

use App\Config\App\Assets;

$target = BASE_DIR . '/assets/' . Assets::BUNDLE;
if (!is_dir(dirname($target))) {
    mkdir(dirname($target), 0775, true);
}
$bundle = Assets::buildBundle();
file_put_contents($target, $bundle);

$sourceBytes = array_sum(array_map(static fn ($path) => filesize(BASE_DIR . '/assets/' . $path), Assets::STYLES));
printf("Built assets/%s: %d files, %.1f KB → %.1f KB\n", Assets::BUNDLE, count(Assets::STYLES), $sourceBytes / 1024, strlen($bundle) / 1024);
