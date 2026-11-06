<?php

declare(strict_types=1);

/**
 * Unit test runner: finds tests/Unit/**\/*Test.php, runs every public test* method, prints a summary,
 * and exits non-zero on failure. Usage: C:\xampp\php\php.exe tests/run.php
 */

if (PHP_SAPI !== 'cli') {
    exit('CLI only.');
}

require __DIR__ . '/../config/App/bootstrap.php';
require __DIR__ . '/AssertionFailed.php';
require __DIR__ . '/TestCase.php';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/Unit'));
$passed = 0;
$failed = 0;
$assertions = 0;

foreach ($files as $file) {
    if (!str_ends_with($file->getFilename(), 'Test.php')) {
        continue;
    }
    $before = get_declared_classes();
    require $file->getPathname();
    foreach (array_diff(get_declared_classes(), $before) as $class) {
        if (!is_subclass_of($class, Tests\TestCase::class)) {
            continue;
        }
        foreach (get_class_methods($class) as $method) {
            if (!str_starts_with($method, 'test')) {
                continue;
            }
            $test = new $class();
            try {
                $test->$method();
                $passed++;
                echo "  ok   $class::$method\n";
            } catch (Throwable $error) {
                $failed++;
                echo "  FAIL $class::$method — " . $error->getMessage() . "\n";
            }
            $assertions += $test->assertions;
        }
    }
}

echo "\n$passed passed, $failed failed, $assertions assertions\n";
exit($failed === 0 ? 0 : 1);
