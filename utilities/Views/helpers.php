<?php

declare(strict_types=1);

use App\Config\App\AppConfig;
use App\Enums\Users\Permission;
use App\Globals\Auth\CurrentUser;
use App\Middlewares\Security\SecurityHeaders;
use App\Services\Users\PermissionService;
use App\Models\Users\User;
use App\Utilities\Formatting\Money;
use App\Utilities\Security\Csrf;
use App\Utilities\Views\View;

/** Escape for HTML output. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** App-relative URL with an optional query string (empty values dropped). */
function url(string $path = '/', array $query = []): string
{
    $query = array_filter($query, static fn ($v) => $v !== null && $v !== '');
    $url = AppConfig::basePath() . '/' . ltrim($path, '/');
    return $query === [] ? $url : $url . '?' . http_build_query($query);
}

/** Asset URL with a modification-time version for cache busting. */
function asset(string $path): string
{
    $file = BASE_DIR . '/assets/' . ltrim($path, '/');
    $version = is_file($file) ? filemtime($file) : 0;
    return url('assets/' . ltrim($path, '/')) . '?v=' . $version;
}

function partial(string $name, array $data = []): string
{
    return View::capture('partials/' . $name, $data);
}

/** Inline SVG icon from the sprite in views/partials/icons.php. */
function icon(string $name, string $class = 'icon'): string
{
    return '<svg class="' . e($class) . '" aria-hidden="true"><use href="#i-' . e($name) . '"></use></svg>';
}

function money(float|string|null $amount): string
{
    return Money::format((float) $amount);
}

function format_date(?string $value, string $format = 'D, j M Y'): string
{
    return $value ? date($format, strtotime($value)) : '—';
}

function format_time(?string $value): string
{
    return $value ? date('H:i', strtotime($value)) : '—';
}

function csrf_token(): string
{
    return Csrf::token();
}

function current_user(): ?User
{
    return CurrentUser::get();
}

/** True when the signed-in user's role holds the permission (used to show or hide UI). */
function can(Permission $permission): bool
{
    $user = CurrentUser::get();
    return $user !== null && (new PermissionService())->can($user, $permission);
}

/** Renders views/partials/ui/customButton.php. */
function button(string $text, array $options = []): string
{
    return partial('ui/customButton', ['text' => $text, ...$options]);
}

/** Renders views/partials/ui/badge.php. */
function badge(string $text, string $tone = 'neutral', array $options = []): string
{
    return partial('ui/badge', ['text' => $text, 'tone' => $tone, ...$options]);
}

function csp_nonce(): string
{
    return SecurityHeaders::nonce();
}

/** Builds HTML attributes from an array; true renders a bare attribute, null/false is skipped. */
function attrs(array $attributes): string
{
    $html = '';
    foreach ($attributes as $name => $value) {
        if ($value === null || $value === false) {
            continue;
        }
        $html .= $value === true ? ' ' . e($name) : ' ' . e($name) . '="' . e($value) . '"';
    }
    return $html;
}

/** Marks a navigation link active when the current path starts with $prefix. */
function nav_active(string $current, string $prefix): string
{
    $isActive = $prefix === '/' ? $current === '/' : str_starts_with($current, $prefix);
    return $isActive ? ' is-active' : '';
}
