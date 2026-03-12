<?php

defined('_JEXEC') or die;

namespace KwtSMS\Component\Kwtsms\Administrator\Service;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Settings service for kwtSMS. Reads and writes settings to #__kwtsms_settings.
 * Handles AES-256-CBC encryption for API credentials.
 */
final class SettingsService
{
    private array $cache = [];

    public function __construct(
        private readonly DatabaseInterface $db,
        private string $encryptionKey = ''
    ) {
    }

    /**
     * Get a setting value from cache or database.
     *
     * @param  string  $key      The setting key
     * @param  mixed   $default  Default value if not found
     *
     * @return mixed The setting value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('setting_value'))
            ->from($this->db->quoteName('#__kwtsms_settings'))
            ->where($this->db->quoteName('setting_key') . ' = :key')
            ->bind(':key', $key, ParameterType::STRING);

        $this->db->setQuery($query);
        $result = $this->db->loadResult();

        if ($result === null) {
            return $default;
        }

        $this->cache[$key] = $result;

        return $result;
    }

    /**
     * Set a setting value in the database and cache.
     *
     * @param  string  $key       The setting key
     * @param  mixed   $value     The setting value
     * @param  bool    $autoload  Whether to autoload this setting
     *
     * @return void
     */
    public function set(string $key, mixed $value, bool $autoload = false): void
    {
        $strValue = (string) $value;

        $query = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__kwtsms_settings'))
            ->where($this->db->quoteName('setting_key') . ' = :key')
            ->bind(':key', $key, ParameterType::STRING);

        $this->db->setQuery($query);
        $exists = (int) $this->db->loadResult() > 0;

        if ($exists) {
            $query = $this->db->getQuery(true)
                ->update($this->db->quoteName('#__kwtsms_settings'))
                ->set($this->db->quoteName('setting_value') . ' = :value')
                ->set($this->db->quoteName('autoload') . ' = :autoload')
                ->where($this->db->quoteName('setting_key') . ' = :key')
                ->bind(':value', $strValue, ParameterType::STRING)
                ->bind(':autoload', (int) $autoload, ParameterType::INTEGER)
                ->bind(':key', $key, ParameterType::STRING);
        } else {
            $query = $this->db->getQuery(true)
                ->insert($this->db->quoteName('#__kwtsms_settings'))
                ->columns($this->db->quoteName(['setting_key', 'setting_value', 'autoload']))
                ->values(':key, :value, :autoload')
                ->bind(':key', $key, ParameterType::STRING)
                ->bind(':value', $strValue, ParameterType::STRING)
                ->bind(':autoload', (int) $autoload, ParameterType::INTEGER);
        }

        $this->db->setQuery($query);
        $this->db->execute();

        $this->cache[$key] = $strValue;
    }

    /**
     * Encrypt plaintext using AES-256-CBC.
     *
     * @param  string  $plaintext  The text to encrypt
     *
     * @return string The base64-encoded ciphertext (IV + data), or empty string on failure
     */
    public function encrypt(string $plaintext): string
    {
        if ($plaintext === '') {
            return '';
        }

        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($plaintext, 'AES-256-CBC', $this->getAesKey(), OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            return '';
        }

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt ciphertext using AES-256-CBC.
     *
     * @param  string  $ciphertext  The base64-encoded ciphertext (IV + data)
     *
     * @return string The decrypted plaintext, or empty string on failure
     */
    public function decrypt(string $ciphertext): string
    {
        if ($ciphertext === '') {
            return '';
        }

        $decoded = base64_decode($ciphertext, true);

        if ($decoded === false || strlen($decoded) < 17) {
            return '';
        }

        $iv = substr($decoded, 0, 16);
        $data = substr($decoded, 16);

        $result = openssl_decrypt($data, 'AES-256-CBC', $this->getAesKey(), OPENSSL_RAW_DATA, $iv);

        return $result !== false ? $result : '';
    }

    /**
     * Get API credentials from encrypted storage.
     *
     * @return array Array with 'username' and 'password' keys
     */
    public function getCredentials(): array
    {
        return [
            'username' => $this->decrypt($this->get('api_username', '')),
            'password' => $this->decrypt($this->get('api_password', '')),
        ];
    }

    /**
     * Save API credentials with encryption.
     *
     * @param  string  $username  The API username
     * @param  string  $password  The API password
     *
     * @return void
     */
    public function saveCredentials(string $username, string $password): void
    {
        $this->set('api_username', $this->encrypt($username));
        $this->set('api_password', $this->encrypt($password));
        $this->set('gateway_configured', '1');
    }

    /**
     * Update the gateway balance.
     *
     * @param  float  $balanceAfter  The new balance value
     *
     * @return void
     */
    public function updateBalance(float $balanceAfter): void
    {
        $this->set('balance', (string) $balanceAfter);
    }

    /**
     * Check if the gateway is ready to use.
     *
     * @return bool True if gateway is enabled and configured
     */
    public function isGatewayReady(): bool
    {
        return $this->get('gateway_enabled') === '1' && $this->get('gateway_configured') === '1';
    }

    /**
     * Get coverage information from settings.
     *
     * @return array Array of coverage data, empty array if not set
     */
    public function getCoverage(): array
    {
        return json_decode($this->get('coverage', '[]'), true) ?? [];
    }

    /**
     * Get sender IDs from settings.
     *
     * @return array Array of sender ID data, empty array if not set
     */
    public function getSenderIds(): array
    {
        return json_decode($this->get('senderids', '[]'), true) ?? [];
    }

    /**
     * Clear the settings cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Derive the AES encryption key from the encryption key string.
     *
     * @return string 32-byte SHA-256 hash suitable for AES-256
     */
    private function getAesKey(): string
    {
        return hash('sha256', $this->encryptionKey, true);
    }
}
