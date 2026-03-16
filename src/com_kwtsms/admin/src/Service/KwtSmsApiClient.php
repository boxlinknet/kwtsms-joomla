<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

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

    /**
     * Phone validation rules by country code.
     * Each entry has 'localLengths' (array of ints) and optional 'mobileStartDigits' (array of single-char strings).
     * localLengths = valid digit count AFTER country code.
     * mobileStartDigits = valid first digit(s) of the local portion.
     * Countries not listed pass through with generic E.164 validation only (7-15 digits).
     */
    private const PHONE_RULES = [
        // GCC
        '965' => ['localLengths' => [8], 'mobileStartDigits' => ['4', '5', '6', '9']],   // Kuwait
        '966' => ['localLengths' => [9], 'mobileStartDigits' => ['5']],                    // Saudi Arabia
        '971' => ['localLengths' => [9], 'mobileStartDigits' => ['5']],                    // UAE
        '973' => ['localLengths' => [8], 'mobileStartDigits' => ['3', '6']],               // Bahrain
        '974' => ['localLengths' => [8], 'mobileStartDigits' => ['3', '5', '6', '7']],    // Qatar
        '968' => ['localLengths' => [8], 'mobileStartDigits' => ['7', '9']],               // Oman
        // Levant
        '962' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                    // Jordan
        '961' => ['localLengths' => [7, 8], 'mobileStartDigits' => ['3', '7', '8']],      // Lebanon
        '970' => ['localLengths' => [9], 'mobileStartDigits' => ['5']],                    // Palestine
        '964' => ['localLengths' => [10], 'mobileStartDigits' => ['7']],                   // Iraq
        '963' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                    // Syria
        // Other Arab
        '967' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                    // Yemen
        '20'  => ['localLengths' => [10], 'mobileStartDigits' => ['1']],                   // Egypt
        '218' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                    // Libya
        '216' => ['localLengths' => [8], 'mobileStartDigits' => ['2', '4', '5', '9']],    // Tunisia
        '212' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],               // Morocco
        '213' => ['localLengths' => [9], 'mobileStartDigits' => ['5', '6', '7']],          // Algeria
        '249' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                    // Sudan
        // Non-Arab Middle East
        '98'  => ['localLengths' => [10], 'mobileStartDigits' => ['9']],                   // Iran
        '90'  => ['localLengths' => [10], 'mobileStartDigits' => ['5']],                   // Turkey
        '972' => ['localLengths' => [9], 'mobileStartDigits' => ['5']],                    // Israel
        // South Asia
        '91'  => ['localLengths' => [10], 'mobileStartDigits' => ['6', '7', '8', '9']],   // India
        '92'  => ['localLengths' => [10], 'mobileStartDigits' => ['3']],                   // Pakistan
        '880' => ['localLengths' => [10], 'mobileStartDigits' => ['1']],                   // Bangladesh
        '94'  => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                    // Sri Lanka
        '960' => ['localLengths' => [7], 'mobileStartDigits' => ['7', '9']],               // Maldives
        // East Asia
        '86'  => ['localLengths' => [11], 'mobileStartDigits' => ['1']],                   // China
        '81'  => ['localLengths' => [10], 'mobileStartDigits' => ['7', '8', '9']],         // Japan
        '82'  => ['localLengths' => [10], 'mobileStartDigits' => ['1']],                   // South Korea
        '886' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                    // Taiwan
        // Southeast Asia
        '65'  => ['localLengths' => [8], 'mobileStartDigits' => ['8', '9']],               // Singapore
        '60'  => ['localLengths' => [9, 10], 'mobileStartDigits' => ['1']],                // Malaysia
        '62'  => ['localLengths' => [9, 10, 11, 12], 'mobileStartDigits' => ['8']],        // Indonesia
        '63'  => ['localLengths' => [10], 'mobileStartDigits' => ['9']],                   // Philippines
        '66'  => ['localLengths' => [9], 'mobileStartDigits' => ['6', '8', '9']],          // Thailand
        '84'  => ['localLengths' => [9], 'mobileStartDigits' => ['3', '5', '7', '8', '9']], // Vietnam
        '95'  => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                    // Myanmar
        '855' => ['localLengths' => [8, 9], 'mobileStartDigits' => ['1', '6', '7', '8', '9']], // Cambodia
        '976' => ['localLengths' => [8], 'mobileStartDigits' => ['6', '8', '9']],          // Mongolia
        // Europe
        '44'  => ['localLengths' => [10], 'mobileStartDigits' => ['7']],                   // UK
        '33'  => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],               // France
        '49'  => ['localLengths' => [10, 11], 'mobileStartDigits' => ['1']],               // Germany
        '39'  => ['localLengths' => [10], 'mobileStartDigits' => ['3']],                   // Italy
        '34'  => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],               // Spain
        '31'  => ['localLengths' => [9], 'mobileStartDigits' => ['6']],                    // Netherlands
        '32'  => ['localLengths' => [9]],                                                   // Belgium
        '41'  => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                    // Switzerland
        '43'  => ['localLengths' => [10], 'mobileStartDigits' => ['6']],                   // Austria
        '47'  => ['localLengths' => [8], 'mobileStartDigits' => ['4', '9']],               // Norway
        '48'  => ['localLengths' => [9]],                                                   // Poland
        '30'  => ['localLengths' => [10], 'mobileStartDigits' => ['6']],                   // Greece
        '420' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],               // Czech Republic
        '46'  => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                    // Sweden
        '45'  => ['localLengths' => [8]],                                                   // Denmark
        '40'  => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                    // Romania
        '36'  => ['localLengths' => [9]],                                                   // Hungary
        '380' => ['localLengths' => [9]],                                                   // Ukraine
        // Americas
        '1'   => ['localLengths' => [10]],                                                  // USA/Canada
        '52'  => ['localLengths' => [10]],                                                  // Mexico
        '55'  => ['localLengths' => [11]],                                                  // Brazil
        '57'  => ['localLengths' => [10], 'mobileStartDigits' => ['3']],                   // Colombia
        '54'  => ['localLengths' => [10], 'mobileStartDigits' => ['9']],                   // Argentina
        '56'  => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                    // Chile
        '58'  => ['localLengths' => [10], 'mobileStartDigits' => ['4']],                   // Venezuela
        '51'  => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                    // Peru
        '593' => ['localLengths' => [9], 'mobileStartDigits' => ['9']],                    // Ecuador
        '53'  => ['localLengths' => [8], 'mobileStartDigits' => ['5', '6']],               // Cuba
        // Africa
        '27'  => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7', '8']],          // South Africa
        '234' => ['localLengths' => [10], 'mobileStartDigits' => ['7', '8', '9']],         // Nigeria
        '254' => ['localLengths' => [9], 'mobileStartDigits' => ['1', '7']],               // Kenya
        '233' => ['localLengths' => [9], 'mobileStartDigits' => ['2', '5']],               // Ghana
        '251' => ['localLengths' => [9], 'mobileStartDigits' => ['7', '9']],               // Ethiopia
        '255' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],               // Tanzania
        '256' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                    // Uganda
        '237' => ['localLengths' => [9], 'mobileStartDigits' => ['6']],                    // Cameroon
        '225' => ['localLengths' => [10]],                                                  // Ivory Coast
        '221' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                    // Senegal
        '252' => ['localLengths' => [9], 'mobileStartDigits' => ['6', '7']],               // Somalia
        '250' => ['localLengths' => [9], 'mobileStartDigits' => ['7']],                    // Rwanda
        // Oceania
        '61'  => ['localLengths' => [9], 'mobileStartDigits' => ['4']],                    // Australia
        '64'  => ['localLengths' => [8, 9, 10], 'mobileStartDigits' => ['2']],             // New Zealand
    ];

    /** Country display names used in validation error messages. */
    private const COUNTRY_NAMES = [
        '965' => 'Kuwait',       '966' => 'Saudi Arabia', '971' => 'UAE',
        '973' => 'Bahrain',      '974' => 'Qatar',        '968' => 'Oman',
        '962' => 'Jordan',       '961' => 'Lebanon',      '970' => 'Palestine',
        '964' => 'Iraq',         '963' => 'Syria',        '967' => 'Yemen',
        '20'  => 'Egypt',        '218' => 'Libya',        '216' => 'Tunisia',
        '212' => 'Morocco',      '213' => 'Algeria',      '249' => 'Sudan',
        '98'  => 'Iran',         '90'  => 'Turkey',       '972' => 'Israel',
        '91'  => 'India',        '92'  => 'Pakistan',     '880' => 'Bangladesh',
        '94'  => 'Sri Lanka',    '960' => 'Maldives',
        '86'  => 'China',        '81'  => 'Japan',        '82'  => 'South Korea',
        '886' => 'Taiwan',
        '65'  => 'Singapore',    '60'  => 'Malaysia',     '62'  => 'Indonesia',
        '63'  => 'Philippines',  '66'  => 'Thailand',     '84'  => 'Vietnam',
        '95'  => 'Myanmar',      '855' => 'Cambodia',     '976' => 'Mongolia',
        '44'  => 'UK',           '33'  => 'France',       '49'  => 'Germany',
        '39'  => 'Italy',        '34'  => 'Spain',        '31'  => 'Netherlands',
        '32'  => 'Belgium',      '41'  => 'Switzerland',  '43'  => 'Austria',
        '47'  => 'Norway',       '48'  => 'Poland',       '30'  => 'Greece',
        '420' => 'Czech Republic', '46' => 'Sweden',      '45'  => 'Denmark',
        '40'  => 'Romania',      '36'  => 'Hungary',      '380' => 'Ukraine',
        '1'   => 'USA/Canada',   '52'  => 'Mexico',       '55'  => 'Brazil',
        '57'  => 'Colombia',     '54'  => 'Argentina',    '56'  => 'Chile',
        '58'  => 'Venezuela',    '51'  => 'Peru',         '593' => 'Ecuador',
        '53'  => 'Cuba',
        '27'  => 'South Africa', '234' => 'Nigeria',      '254' => 'Kenya',
        '233' => 'Ghana',        '251' => 'Ethiopia',     '255' => 'Tanzania',
        '256' => 'Uganda',       '237' => 'Cameroon',     '225' => 'Ivory Coast',
        '221' => 'Senegal',      '252' => 'Somalia',      '250' => 'Rwanda',
        '61'  => 'Australia',    '64'  => 'New Zealand',
    ];

    public function __construct(
        private readonly string $username,
        private readonly string $password
    ) {
    }

    /**
     * Get the country name for a given prefix code.
     *
     * @param string $prefix Country prefix (e.g. '965')
     */
    public static function countryName(string $prefix): string
    {
        return self::COUNTRY_NAMES[$prefix] ?? $prefix;
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
        $phone = preg_replace('/\D/', '', $phone) ?? '';

        // Strip leading zeros (handles 00 country code prefix)
        $phone = ltrim($phone, '0');

        // Strip trunk prefix 0 immediately after country code.
        // e.g. 9660559xxxxx -> 966 + 0559xxxxx -> 966 + 559xxxxx (Saudi trunk prefix)
        $cc = $this->findCountryCode($phone);

        if ($cc !== null) {
            $local = substr($phone, strlen($cc));

            if (str_starts_with($local, '0')) {
                $phone = $cc . substr($local, 1);
            }
        }

        return $phone;
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

    /**
     * Normalize and fully validate a phone number.
     * Equivalent to the TypeScript normalize() which calls validatePhoneFormat() internally.
     *
     * Returns an array with:
     *   'valid'      bool    - whether the number passed all checks
     *   'normalized' string  - digits-only international format (may be partial on failure)
     *   'error'      ?string - human-readable reason on failure, null on success
     *
     * @param string $phone             Raw phone number in any format
     * @param string $defaultCountryCode Optional country code to prepend for local numbers (e.g. '965')
     *
     * @return array{valid: bool, normalized: string, error: ?string}
     */
    public function normalizeValidated(string $phone, string $defaultCountryCode = ''): array
    {
        if ($phone === '' || trim($phone) === '') {
            return ['valid' => false, 'normalized' => '', 'error' => 'Phone number is required'];
        }

        $normalized = $this->normalize($phone);

        if ($normalized === '') {
            return ['valid' => false, 'normalized' => '', 'error' => 'No digits found in phone number'];
        }

        // Prepend default country code for local numbers (9 digits or fewer)
        if ($defaultCountryCode !== '' && strlen($normalized) <= 9 && !str_starts_with($normalized, $defaultCountryCode)) {
            $normalized = $defaultCountryCode . $normalized;
        }

        if (strlen($normalized) < 7) {
            return ['valid' => false, 'normalized' => $normalized, 'error' => 'Phone number too short (minimum 7 digits)'];
        }

        if (strlen($normalized) > 15) {
            return ['valid' => false, 'normalized' => $normalized, 'error' => 'Phone number too long (maximum 15 digits)'];
        }

        $formatCheck = $this->validateFormat($normalized);

        if (!$formatCheck['valid']) {
            return ['valid' => false, 'normalized' => $normalized, 'error' => $formatCheck['error'] ?? 'Invalid phone format'];
        }

        return ['valid' => true, 'normalized' => $normalized, 'error' => null];
    }

    /**
     * Validate a normalized (digits-only) phone number against country-specific format rules.
     * Checks local number length and mobile starting digit.
     * Numbers with no matching country rule pass through (generic E.164 only).
     *
     * @param string $normalized Digits-only phone number in international format
     *
     * @return array{valid: bool, error: ?string}
     */
    public function validateFormat(string $normalized): array
    {
        $cc = $this->findCountryCode($normalized);

        if ($cc === null) {
            return ['valid' => true, 'error' => null];
        }

        $rule    = self::PHONE_RULES[$cc];
        $local   = substr($normalized, strlen($cc));
        $country = self::COUNTRY_NAMES[$cc] ?? '+' . $cc;

        if (!in_array(strlen($local), $rule['localLengths'], true)) {
            $expected = implode(' or ', $rule['localLengths']);

            return [
                'valid' => false,
                'error' => "Invalid {$country} number: expected {$expected} digits after +{$cc}, got " . strlen($local),
            ];
        }

        if (!empty($rule['mobileStartDigits'])) {
            $firstChar = substr($local, 0, 1);

            if (!in_array($firstChar, $rule['mobileStartDigits'], true)) {
                return [
                    'valid' => false,
                    'error' => "Invalid {$country} mobile number: after +{$cc} must start with " . implode(', ', $rule['mobileStartDigits']),
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Get current account balance.
     */
    public function balance(): array
    {
        return $this->post('balance', []);
    }

    /**
     * Get list of available sender IDs.
     */
    public function senderid(): array
    {
        return $this->post('senderid', []);
    }

    /**
     * Get active country prefix coverage list.
     */
    public function coverage(): array
    {
        return $this->post('coverage', []);
    }

    /**
     * Validate one or more phone numbers (comma-separated).
     *
     * @param string $mobilesCsv Comma-separated phone numbers in international format
     */
    public function validate(string $mobilesCsv): array
    {
        return $this->post('validate', ['mobile' => $mobilesCsv]);
    }

    /**
     * Send SMS to one or more recipients.
     *
     * Single entry point for all SMS sending. Handles: gateway guard, credential check,
     * balance check (with 24h auto-refresh), normalization, deduplication, phone validation,
     * coverage filtering, message cleaning, bulk batching (200 per call, 0.5s delay),
     * balance update from API response, and logging.
     *
     * Test mode is read from settings (not passed as parameter). All callers respect the
     * global test_mode toggle.
     *
     * @param string[]             $recipients    Phone numbers (any format: normalized internally)
     * @param string               $message       Message text (cleaned internally)
     * @param string               $sender        Sender ID
     * @param SettingsService|null $settings      Settings for gateway guard, coverage, balance, test mode
     * @param LogService|null      $log           Optional logger for audit trail
     * @param string               $recipientType Context label for logging (e.g. 'customer', 'admin', 'test')
     *
     * @return array Result with 'result' key: 'OK', 'BULK', 'SKIPPED', or 'ERROR'
     */
    public function send(
        array $recipients,
        string $message,
        string $sender,
        ?SettingsService $settings = null,
        ?LogService $log = null,
        string $recipientType = 'sms'
    ): array {
        // 1. Gateway guard
        if ($settings !== null && !$settings->isGatewayReady()) {
            $this->logSend($log, 'warning', $recipientType, 'Gateway is disabled or not configured', $recipients);

            return ['result' => 'SKIPPED', 'reason' => 'Gateway is disabled or not configured. Enable it in Settings.'];
        }

        // 2. Credential check
        if ($this->username === '' || $this->password === '') {
            $this->logSend($log, 'error', $recipientType, 'No API credentials configured', $recipients);

            return ['result' => 'ERROR', 'reason' => 'No API credentials configured. Enter them in Settings.'];
        }

        // 3. Refresh balance if last sync is older than 24 hours
        if ($settings !== null) {
            $lastSync = $settings->get('last_sync', '');

            if ($lastSync === '' || (time() - strtotime($lastSync)) > 86400) {
                $freshBalance = $this->balance();

                if (($freshBalance['result'] ?? '') === 'OK') {
                    $settings->updateBalance((float) ($freshBalance['available'] ?? 0));
                    $settings->set('last_sync', gmdate('Y-m-d H:i:s'));
                }
            }
        }

        // 4. Balance guard
        if ($settings !== null && (float) $settings->get('balance', '0') <= 0) {
            $this->logSend($log, 'warning', $recipientType, 'Insufficient balance (0 credits)', $recipients);

            return ['result' => 'SKIPPED', 'reason' => 'Insufficient balance (0 credits). Recharge at kwtsms.com.'];
        }

        // 5. Read test mode from settings (defaults to true when no settings, safe default)
        //    KWTSMS_TEST_MODE env var forces test mode (used by PHPUnit bootstrap)
        $testMode = ($_ENV['KWTSMS_TEST_MODE'] ?? '') === '1'
            || $settings === null
            || $settings->get('test_mode', '1') === '1';

        // 6. Normalize, deduplicate, validate
        $normalized = [];
        $invalid    = [];

        foreach ($recipients as $raw) {
            $n = $this->normalize($raw);

            if ($n === '') {
                continue;
            }

            $check = $this->validateFormat($n);

            if ($check['valid']) {
                $normalized[] = $n;
            } else {
                $invalid[] = ['number' => $n, 'error' => $check['error']];
            }
        }

        $normalized = array_values(array_unique($normalized));

        if (empty($normalized)) {
            $reason = empty($invalid)
                ? 'No valid phone numbers after normalization. Check the input format.'
                : 'All numbers failed validation: ' . $invalid[0]['error'];
            $this->logSend($log, 'warning', $recipientType, $reason, $recipients);

            return ['result' => 'SKIPPED', 'reason' => $reason, 'invalid' => $invalid];
        }

        // 7. Coverage filter
        if ($settings !== null) {
            $coverage    = $settings->getCoverage();
            $beforeCount = count($normalized);
            $normalized  = array_values(array_filter(
                $normalized,
                fn(string $n): bool => $this->verify($n, $coverage)
            ));

            if (empty($normalized)) {
                $reason = "All {$beforeCount} recipients are outside your account coverage. Enable more countries at kwtsms.com.";
                $this->logSend($log, 'warning', $recipientType, $reason, $recipients);

                return ['result' => 'SKIPPED', 'reason' => $reason];
            }
        }

        // 8. Clean message
        $cleanMsg = $this->cleanMessage($message);

        if ($cleanMsg === '') {
            $this->logSend($log, 'error', $recipientType, 'Message is empty after cleaning', $recipients);

            return ['result' => 'ERROR', 'reason' => 'Message is empty after cleaning (HTML tags, emoji, and hidden characters were removed).'];
        }

        // 8b. Max length guard (7 pages: 1078 chars English/GSM-7, 472 chars Arabic/Unicode)
        $isUnicode = preg_match('/[^\x20-\x7E\n\r]/', $cleanMsg);
        $maxChars  = $isUnicode ? 472 : 1078;

        if (mb_strlen($cleanMsg, 'UTF-8') > $maxChars) {
            $this->logSend($log, 'error', $recipientType, 'Message too long (' . mb_strlen($cleanMsg, 'UTF-8') . ' chars, max ' . $maxChars . ')', $recipients);

            return ['result' => 'ERROR', 'reason' => 'Message too long. Maximum 7 SMS pages (' . $maxChars . ' characters for ' . ($isUnicode ? 'Arabic/Unicode' : 'English') . ').'];
        }

        // 9. Send (single or bulk)
        $response = count($normalized) <= 200
            ? $this->dispatch($normalized, $cleanMsg, $sender, $testMode, $settings)
            : $this->bulkDispatch($normalized, $cleanMsg, $sender, $testMode, $settings);

        // 10. Log result
        $result = $response['result'] ?? 'ERROR';

        if ($result === 'OK' || $result === 'BULK') {
            $this->logSend($log, 'info', $recipientType, 'SMS sent', $recipients, [
                'count'     => count($normalized),
                'test_mode' => $testMode,
                'msg_id'    => $response['msg-id'] ?? '',
                'invalid'   => count($invalid),
            ]);
        } else {
            $this->logSend($log, 'error', $recipientType, 'SMS send failed: ' . ($response['description'] ?? $response['reason'] ?? 'Unknown'), $recipients);
        }

        if (!empty($invalid)) {
            $response['invalid'] = $invalid;
        }

        return $response;
    }

    /**
     * Log a send operation if a logger is available.
     */
    private function logSend(?LogService $log, string $level, string $context, string $message, array $recipients, array $extra = []): void
    {
        if ($log === null) {
            return;
        }

        $data = array_merge(['recipient' => implode(',', array_slice($recipients, 0, 3))], $extra);
        $log->$level($context, $message, $data);
    }

    /**
     * Send to 200+ recipients in batches of 200 with 0.5s delay between batches.
     */
    private function bulkDispatch(
        array $recipients,
        string $message,
        string $sender,
        bool $testMode,
        ?SettingsService $settings = null
    ): array {
        $batches   = array_chunk($recipients, 200);
        $results   = [];
        $totalSent = 0;
        $totalFail = 0;

        foreach ($batches as $index => $batch) {
            if ($index > 0) {
                usleep(500000);
            }

            $response  = $this->dispatch($batch, $message, $sender, $testMode, $settings);
            $results[] = $response;

            if (($response['result'] ?? '') === 'OK') {
                $totalSent += count($batch);
            } else {
                $totalFail += count($batch);
            }
        }

        return [
            'result'    => 'BULK',
            'batches'   => count($batches),
            'sent'      => $totalSent,
            'failed'    => $totalFail,
            'responses' => $results,
        ];
    }

    /**
     * Dispatch a single API call to send SMS (max 200 recipients).
     * Updates local cached balance from API response on success.
     */
    private function dispatch(
        array $recipients,
        string $message,
        string $sender,
        bool $testMode,
        ?SettingsService $settings = null
    ): array {
        $response = $this->post('send', [
            'sender'  => $sender,
            'mobile'  => implode(',', $recipients),
            'message' => $message,
            'test'    => $testMode ? '1' : '0',
        ]);

        if (($response['result'] ?? '') === 'OK' && $settings !== null) {
            $settings->updateBalance((float) ($response['balance-after'] ?? 0));
        }

        return $response;
    }

    /**
     * Find the country code prefix from a normalized phone number.
     * Tries 3-digit codes first, then 2-digit, then 1-digit (longest match wins).
     *
     * @param string $normalized Digits-only phone number in international format
     *
     * @return string|null The matching country code, or null if not found in PHONE_RULES
     */
    private function findCountryCode(string $normalized): ?string
    {
        if (strlen($normalized) >= 3 && isset(self::PHONE_RULES[substr($normalized, 0, 3)])) {
            return substr($normalized, 0, 3);
        }

        if (strlen($normalized) >= 2 && isset(self::PHONE_RULES[substr($normalized, 0, 2)])) {
            return substr($normalized, 0, 2);
        }

        if (strlen($normalized) >= 1 && isset(self::PHONE_RULES[substr($normalized, 0, 1)])) {
            return substr($normalized, 0, 1);
        }

        return null;
    }

    /**
     * POST a request to the kwtSMS API and return decoded JSON.
     */
    private function post(string $endpoint, array $payload): array
    {
        $payload['username'] = $this->username;
        $payload['password'] = $this->password;

        $url  = 'https://www.kwtsms.com/API/' . $endpoint . '/';
        $json = json_encode($payload);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Content-Length: ' . strlen($json),
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['result' => 'ERROR', 'code' => 'CURL', 'description' => $error];
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            return ['result' => 'ERROR', 'code' => 'JSON', 'description' => 'Invalid JSON response'];
        }

        return $decoded;
    }
}
