<?php

namespace App\Utilities;

class Access
{
    // Permission Constants
    public const READ = 'read';
    public const WRITE = 'write'; // Edit
    public const CREATE = 'create';
    public const DELETE = 'delete';

    /**
     * @param bool ...$conditions
     * @return bool (Implements '&&' - all must be true)
     */
    public static function all(bool ...$conditions): bool
    {
        return !in_array(false, $conditions, true);
    }

    /**
     * @param bool ...$conditions
     * @return bool (Implements '||' - at least one must be true)
     */
    public static function any(bool ...$conditions): bool
    {
        return in_array(true, $conditions, true);
    }
}
