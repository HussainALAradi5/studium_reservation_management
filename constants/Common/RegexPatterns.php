<?php

declare(strict_types=1);

namespace App\Constants\Common;

/**
 * Every validation regex in one place. Server validators use them directly; views pass them to
 * HTML `pattern` attributes through html(), so client and server check the same rule.
 */
final class RegexPatterns
{
    /** Letters (any language), spaces, apostrophes, dots, hyphens. */
    public const PERSON_NAME = '/^[\p{L}][\p{L}\s.\'-]{2,119}$/u';
    public const USERNAME = '/^[A-Za-z0-9._]{3,40}$/';
    /** E.164 style: optional +, 8–15 digits. */
    public const PHONE = '/^\+?[0-9]{8,15}$/';
    /** ISO 3166-1 alpha-2. */
    public const COUNTRY_CODE = '/^[A-Z]{2}$/';
    public const PLACE_NAME = '/^[\p{L}0-9][\p{L}0-9\s.\'-]{1,79}$/u';
    public const STREET = '/^[\p{L}0-9][\p{L}0-9\s.,\'\/#\-]{1,119}$/u';
    /** Building or block numbers such as 12, 12A, 338-B. */
    public const BUILDING_NUMBER = '/^[A-Za-z0-9\-]{1,20}$/';
    public const STADIUM_NAME = '/^[\p{L}0-9][\p{L}0-9\s.&\'()\-]{2,119}$/u';
    public const PAYMENT_REFERENCE = '/^[A-Za-z0-9\/_.\-]{1,80}$/';
    /** Public record codes, e.g. STD-7KQ2M9XA. */
    public const RECORD_CODE = '/^[A-Z]{3}-[A-Z0-9]{8}$/';
    public const TIME_HHMM = '/^([01]\d|2[0-3]):[0-5]\d$/';
    public const DATE_ISO = '/^\d{4}-\d{2}-\d{2}$/';
    /** Spaces, dashes, dots, and brackets people type inside phone numbers ("+973 3300-0001"). */
    public const PHONE_SEPARATORS = '/[\s\-().]/';
    /** Runs of whitespace, collapsed to one space. */
    public const WHITESPACE_RUN = '/\s+/u';
    /** Named placeholders in notification templates, e.g. ":stadium". */
    public const TEMPLATE_PLACEHOLDER = '/:([a-z]+)/';
    public const PASSWORD_LETTER = '/[A-Za-z]/';
    public const PASSWORD_DIGIT = '/\d/';

    /**
     * Converts a PHP pattern to an HTML `pattern` attribute (no delimiters, anchors, or flags).
     * Use it for the ASCII patterns; Unicode ones are checked on the server and shown inline via AJAX.
     */
    public static function html(string $pattern): string
    {
        $body = substr($pattern, 1, strrpos($pattern, '/') - 1);
        return preg_replace(['/^\^/', '/\$$/'], '', $body);
    }
}
