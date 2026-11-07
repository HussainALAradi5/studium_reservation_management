<?php

declare(strict_types=1);

namespace App\Config\App;

/**
 * Stylesheets in load order. Development serves them one by one; production serves the single
 * minified bundle built by bin/build-assets.php (one request instead of ten).
 */
final class Assets
{
    public const STYLES = [
        'css/base/tokens.css',
        'css/base/base.css',
        'css/components/animations.css',
        'css/components/buttons.css',
        'css/components/surfaces.css',
        'css/components/forms.css',
        'css/components/wheel-picker.css',
        'css/components/overlays.css',
        'css/layout/shell.css',
        'css/pages/pages.css',
    ];

    public const BUNDLE = 'build/app.min.css';

    /** @return string[] asset paths to link in <head> */
    public static function stylesheets(): array
    {
        $useBundle = AppConfig::isProduction() && is_file(BASE_DIR . '/assets/' . self::BUNDLE);
        return $useBundle ? [self::BUNDLE] : self::STYLES;
    }

    /** Concatenates the stylesheets and strips comments and extra whitespace. */
    public static function buildBundle(): string
    {
        $css = '';
        foreach (self::STYLES as $path) {
            $css .= file_get_contents(BASE_DIR . '/assets/' . $path) . "\n";
        }
        $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);
        $css = (string) preg_replace('/\s+/', ' ', $css);
        $css = (string) preg_replace('/\s*([{};])\s*/', '$1', $css);
        return trim(str_replace(';}', '}', $css)) . "\n";
    }
}
