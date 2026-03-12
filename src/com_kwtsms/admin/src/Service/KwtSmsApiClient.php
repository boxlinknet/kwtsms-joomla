<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Service;

defined('_JEXEC') or die;

/**
 * kwtSMS API client. Zero Joomla dependencies: unit testable standalone.
 */
final class KwtSmsApiClient
{
    // Arabic-Indic digit codepoints (U+0660 to U+0669)
    private const ARABIC_INDIC = ["\u{0660}", "\u{0661}", "\u{0662}", "\u{0663}", "\u{0664}", "\u{0665}", "\u{0666}", "\u{0667}", "\u{0668}", "\u{0669}"];

    // Extended Arabic-Indic digit codepoints (U+06F0 to U+06F9)
    private const EXTENDED_ARABIC_INDIC = ["\u{06F0}", "\u{06F1}", "\u{06F2}", "\u{06F3}", "\u{06F4}", "\u{06F5}", "\u{06F6}", "\u{06F7}", "\u{06F8}", "\u{06F9}"];

    private const LATIN_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    public function __construct(
        private readonly string $username,
        private readonly string $password
    ) {
    }

    /**
     * Normalize a phone number to digits-only international format.
     * Strips +, 00 prefix, spaces, dashes, and converts Arabic/Extended Arabic digits to Latin.
     */
    public function normalize(string $phone): string
    {
        if ($phone === '') {
            return '';
        }

        // Convert Arabic-Indic and Extended Arabic-Indic digits to Latin
        $phone = str_replace(self::ARABIC_INDIC, self::LATIN_DIGITS, $phone);
        $phone = str_replace(self::EXTENDED_ARABIC_INDIC, self::LATIN_DIGITS, $phone);

        // Strip all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // Strip leading zeros (handles 00 country code prefix)
        $phone = ltrim($phone, '0');

        return $phone ?? '';
    }
}
