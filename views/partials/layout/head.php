<?php
/**
 * Shared <head>: meta, fonts, stylesheets, early theme script (avoids a light flash in dark mode).
 *
 * @var string $title
 * @var bool|null $needsMap  load Leaflet CSS for map pages
 */
use App\Config\App\AppConfig;
use App\Config\App\Assets;

$styles = Assets::stylesheets();
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="app-base" content="<?= e(AppConfig::basePath()) ?>">
<meta name="currency" content="<?= e(AppConfig::currency()) ?>">
<title><?= e(($title ?? '') !== '' ? $title . ' · ' . AppConfig::name() : AppConfig::name()) ?></title>
<script nonce="<?= e(csp_nonce()) ?>">
    (function () {
        try {
            var theme = localStorage.getItem('studium-theme');
            if (theme === 'light' || theme === 'dark') document.documentElement.setAttribute('data-theme', theme);
        } catch (e) {}
    })();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<?php if (!empty($needsMap)): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<?php endif; ?>
<?php foreach ($styles as $style): ?>
    <link rel="stylesheet" href="<?= e(asset($style)) ?>">
<?php endforeach; ?>
<script type="module" src="<?= e(asset('javascripts/app.js')) ?>"></script>
