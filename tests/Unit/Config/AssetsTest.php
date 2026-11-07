<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use App\Config\App\Assets;
use Tests\TestCase;

final class AssetsTest extends TestCase
{
    public function testEveryStylesheetExists(): void
    {
        foreach (Assets::STYLES as $path) {
            $this->assertTrue(is_file(BASE_DIR . '/assets/' . $path), "missing $path");
        }
    }

    /** The committed production bundle must match the current CSS (run bin/build-assets.php after CSS changes). */
    public function testProductionBundleIsUpToDate(): void
    {
        $bundle = BASE_DIR . '/assets/' . Assets::BUNDLE;
        $this->assertTrue(is_file($bundle), 'bundle missing: run php bin/build-assets.php');
        $this->assertSame(Assets::buildBundle(), (string) file_get_contents($bundle), 'bundle is stale: run php bin/build-assets.php');
    }

    public function testMinifierKeepsDataUrisAndRemovesComments(): void
    {
        $bundle = Assets::buildBundle();
        $this->assertSame(0, substr_count($bundle, '/*'), 'comments removed');
        $this->assertTrue(str_contains($bundle, "data:image/svg+xml"), 'select arrow data URI kept');
    }
}
