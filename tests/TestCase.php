<?php

declare(strict_types=1);

namespace Tests;

/** Minimal assertion base so tests run with plain PHP (no Composer packages needed). */
abstract class TestCase
{
    public int $assertions = 0;

    protected function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertions++;
        if ($expected !== $actual) {
            throw new AssertionFailed(sprintf('%sExpected %s, got %s', $message ? "$message: " : '', var_export($expected, true), var_export($actual, true)));
        }
    }

    protected function assertTrue(bool $condition, string $message = ''): void
    {
        $this->assertSame(true, $condition, $message);
    }

    protected function assertFalse(bool $condition, string $message = ''): void
    {
        $this->assertSame(false, $condition, $message);
    }

    /** @param class-string<\Throwable> $class */
    protected function assertThrows(string $class, callable $action, ?string $errorCode = null): \Throwable
    {
        $this->assertions++;
        try {
            $action();
        } catch (\Throwable $error) {
            if (!$error instanceof $class) {
                throw new AssertionFailed("Expected $class, got " . $error::class . ': ' . $error->getMessage());
            }
            if ($errorCode !== null && ($error->errorCode ?? null) !== $errorCode) {
                throw new AssertionFailed("Expected error code $errorCode, got " . ($error->errorCode ?? 'none'));
            }
            return $error;
        }
        throw new AssertionFailed("Expected $class to be thrown");
    }
}
