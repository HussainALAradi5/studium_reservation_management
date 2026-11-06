<?php

declare(strict_types=1);

namespace App\Utilities\Views;

/** Renders a view inside a layout. Views only render; data is prepared by controllers. */
final class View
{
    private const VIEWS_DIR = BASE_DIR . '/views/';

    public static function render(string $template, array $data = [], string $layout = 'layouts/app'): void
    {
        $content = self::capture($template, $data);
        echo self::capture($layout, [...$data, 'content' => $content]);
    }

    public static function capture(string $template, array $data = []): string
    {
        $file = self::VIEWS_DIR . $template . '.php';
        if (!is_file($file)) {
            throw new \LogicException("View not found: $template");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            include $file;
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }
}
