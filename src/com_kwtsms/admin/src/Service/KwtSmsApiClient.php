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

    /**
     * Clean a message before sending. Strips HTML, emojis, hidden control characters,
     * and converts Arabic-Indic numerals to Latin digits.
     */
    public function cleanMessage(string $message): string
    {
        // 1. Strip HTML tags
        $message = strip_tags($message);

        // 2. Strip hidden/zero-width control characters
        $hidden = [
            "\u{200B}", // zero-width space
            "\u{FEFF}", // BOM / zero-width no-break space
            "\u{00AD}", // soft hyphen
            "\u{200C}", // zero-width non-joiner
            "\u{200D}", // zero-width joiner
        ];
        $message = str_replace($hidden, '', $message);

        // 3. Strip emoji (Unicode supplemental ranges)
        $message = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $message);
        $message = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $message);
        $message = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $message);
        $message = preg_replace('/[\x{1F700}-\x{1F77F}]/u', '', $message);
        $message = preg_replace('/[\x{1F780}-\x{1F7FF}]/u', '', $message);
        $message = preg_replace('/[\x{1F800}-\x{1F8FF}]/u', '', $message);
        $message = preg_replace('/[\x{1F900}-\x{1F9FF}]/u', '', $message);
        $message = preg_replace('/[\x{1FA00}-\x{1FA6F}]/u', '', $message);
        $message = preg_replace('/[\x{1FA70}-\x{1FAFF}]/u', '', $message);
        $message = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $message);
        $message = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $message);

        // 4. Convert Arabic-Indic and Extended Arabic-Indic digits to Latin
        $message = str_replace(self::ARABIC_INDIC, self::LATIN_DIGITS, $message);
        $message = str_replace(self::EXTENDED_ARABIC_INDIC, self::LATIN_DIGITS, $message);

        // 5. Trim leading/trailing whitespace
        return trim($message);
    }

    /**
     * Verify a normalized phone number is covered by the given prefix list.
     * Returns false immediately if coverage list is empty.
     *
     * @param string   $normalizedPhone Phone in digits-only international format
     * @param string[] $coverage        Array of active country prefixes (e.g. ['965','971'])
     */
    public function verify(string $normalizedPhone, array $coverage): bool
    {
        if (empty($coverage)) {
            return false;
        }

        $prefix = substr($normalizedPhone, 0, 3);

        return in_array($prefix, $coverage, true);
    }
}
