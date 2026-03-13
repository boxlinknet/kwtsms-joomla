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
        $phone = preg_replace('/\D/', '', $phone) ?? '';

        // Strip leading zeros (handles 00 country code prefix)
        $phone = ltrim($phone, '0');

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
     * Send SMS to one or more recipients with full pipeline:
     * normalize, verify coverage, clean message, batch, POST.
     *
     * The caller must persist the result to #__kwtsms_messages.
     * This method only updates the cached balance on success.
     *
     * @param string[]             $recipients Phone numbers (any format: normalized internally)
     * @param string               $message    Message text (cleaned internally)
     * @param string               $sender     Sender ID
     * @param bool                 $testMode   If true, message enters queue but is not delivered
     * @param SettingsService|null $settings   Optional settings for gateway guard, coverage filter, balance update
     */
    public function send(
        array $recipients,
        string $message,
        string $sender,
        bool $testMode = false,
        ?SettingsService $settings = null
    ): array {
        // 1. Gateway guard
        if ($settings !== null && !$settings->isGatewayReady()) {
            return ['result' => 'SKIPPED', 'reason' => 'Gateway disabled or not configured'];
        }

        // 2. Balance guard
        if ($settings !== null && (float) $settings->get('balance', '0') <= 0) {
            return ['result' => 'SKIPPED', 'reason' => 'Zero balance'];
        }

        // 3. Normalize all recipients
        $normalized = array_map([$this, 'normalize'], $recipients);
        $normalized = array_filter($normalized, static fn(string $n): bool => $n !== '');
        $normalized = array_values($normalized);

        // 4. Filter by coverage if settings available
        if ($settings !== null) {
            $coverage = $settings->getCoverage();
            $normalized = array_values(array_filter(
                $normalized,
                fn(string $n): bool => $this->verify($n, $coverage)
            ));
        }

        // 5. Guard: no valid recipients
        if (empty($normalized)) {
            return ['result' => 'SKIPPED', 'reason' => 'No valid recipients'];
        }

        // 6. Clean message
        $cleanMsg = $this->cleanMessage($message);

        // 7. Guard: empty message after cleaning
        if ($cleanMsg === '') {
            return ['result' => 'ERROR', 'reason' => 'Empty message after cleaning'];
        }

        // 8. Bulk if more than 200 recipients
        if (count($normalized) > 200) {
            return $this->bulkSend($normalized, $cleanMsg, $sender, $testMode, $settings);
        }

        // 9. Build and send payload
        $payload = [
            'sender'  => $sender,
            'mobile'  => implode(',', $normalized),
            'message' => $cleanMsg,
            'test'    => $testMode ? '1' : '0',
        ];

        $response = $this->post('send', $payload);

        // 10. Update balance on success
        if (($response['result'] ?? '') === 'OK' && $settings !== null) {
            $balanceAfter = max(0.0, min(999999.99, (float) ($response['balance-after'] ?? 0)));
            $settings->updateBalance($balanceAfter);
        }

        return $response;
    }

    /**
     * Send to more than 200 recipients in batches of 200 with 0.2s delay between batches.
     *
     * @param string[]             $recipients Already normalized phone numbers
     * @param string               $message    Already cleaned message text
     * @param string               $sender     Sender ID
     * @param bool                 $testMode   Test mode flag
     * @param SettingsService|null $settings   Optional settings for balance update
     */
    public function bulkSend(
        array $recipients,
        string $message,
        string $sender,
        bool $testMode = false,
        ?SettingsService $settings = null
    ): array {
        $batches = array_chunk($recipients, 200);
        $results = [];

        foreach ($batches as $index => $batch) {
            if ($index > 0) {
                usleep(200000); // 0.2s between batches
            }

            $payload = [
                'sender'  => $sender,
                'mobile'  => implode(',', $batch),
                'message' => $message,
                'test'    => $testMode ? '1' : '0',
            ];

            $response = $this->post('send', $payload);

            if (($response['result'] ?? '') === 'OK' && $settings !== null) {
                $balanceAfter = max(0.0, min(999999.99, (float) ($response['balance-after'] ?? 0)));
                $settings->updateBalance($balanceAfter);
            }

            $results[] = $response;
        }

        return ['result' => 'BULK', 'batches' => count($batches), 'responses' => $results];
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
