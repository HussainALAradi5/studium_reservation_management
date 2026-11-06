<?php

declare(strict_types=1);

namespace App\Constants\Common;

/** Public code prefixes per table (see docs: server/models/Base/base-entity.md). */
final class CodePrefixes
{
    public const USER = 'USR';
    public const CITY = 'CTY';
    public const ADDRESS = 'ADR';
    public const STADIUM = 'STD';
    public const RESERVATION = 'RSV';
    public const PAYMENT = 'PAY';
    public const EXPENSE = 'EXP';
    public const SETTING = 'SET';
    public const ROLE_PERMISSION = 'RPM';
    public const LENGTH = 8;
    public const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
}
