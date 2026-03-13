<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Log service for kwtSMS. Writes structured log entries to #__kwtsms_logs.
 * Credentials and phone data are masked before logging.
 */
final class LogService
{
    public function __construct(
        private readonly DatabaseInterface $db,
        private readonly bool $debugEnabled = false
    ) {
    }

    /**
     * Log a debug message.
     *
     * Only logged if debug mode is enabled.
     *
     * @param string $context The context for this log entry
     * @param string $message The log message
     * @param array  $data    Additional data to log
     *
     * @return void
     */
    public function debug(string $context, string $message, array $data = []): void
    {
        if (!$this->debugEnabled) {
            return;
        }

        $this->write('debug', $context, $message, $data);
    }

    /**
     * Log an info message.
     *
     * @param string $context The context for this log entry
     * @param string $message The log message
     * @param array  $data    Additional data to log
     *
     * @return void
     */
    public function info(string $context, string $message, array $data = []): void
    {
        $this->write('info', $context, $message, $data);
    }

    /**
     * Log a warning message.
     *
     * @param string $context The context for this log entry
     * @param string $message The log message
     * @param array  $data    Additional data to log
     *
     * @return void
     */
    public function warning(string $context, string $message, array $data = []): void
    {
        $this->write('warning', $context, $message, $data);
    }

    /**
     * Log an error message.
     *
     * @param string $context The context for this log entry
     * @param string $message The log message
     * @param array  $data    Additional data to log
     *
     * @return void
     */
    public function error(string $context, string $message, array $data = []): void
    {
        $this->write('error', $context, $message, $data);
    }

    /**
     * Write a log entry to the database.
     *
     * @param string $level   The log level (debug, info, warning, error)
     * @param string $context The context for this log entry
     * @param string $message The log message
     * @param array  $data    Additional data to log
     *
     * @return void
     */
    private function write(string $level, string $context, string $message, array $data = []): void
    {
        try {
            $maskedData = $this->maskCredentials($data);
            $dataJson = json_encode($maskedData) ?: '';

            $query = $this->db->getQuery(true)
                ->insert($this->db->quoteName('#__kwtsms_logs'))
                ->columns($this->db->quoteName(['level', 'context', 'message', 'data']))
                ->values(':level, :context, :message, :data');

            $query->bind(':level', $level, ParameterType::STRING);
            $query->bind(':context', $context, ParameterType::STRING);
            $query->bind(':message', $message, ParameterType::STRING);
            $query->bind(':data', $dataJson, ParameterType::STRING);

            $this->db->setQuery($query)->execute();
        } catch (\Throwable $e) {
            // Logging must never crash the application, silently swallow errors
        }
    }

    /**
     * Mask sensitive credentials in data array.
     *
     * Recursively walks the array and replaces values for sensitive keys
     * with '***'. Sensitive keys include: username, password, api_username,
     * and api_password.
     *
     * @param array $data The data array to mask
     *
     * @return array The masked data array
     */
    private function maskCredentials(array $data): array
    {
        $credentialKeys = ['username', 'password', 'api_username', 'api_password'];
        $phoneKeys      = ['mobile', 'phone', 'phone_1', 'phone_2', 'recipient'];
        $masked         = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $credentialKeys, true)) {
                $masked[$key] = '***';
            } elseif (in_array($key, $phoneKeys, true)) {
                $str          = (string) $value;
                $masked[$key] = strlen($str) > 7
                    ? substr($str, 0, 7) . str_repeat('*', strlen($str) - 7)
                    : '***';
            } elseif (is_array($value)) {
                $masked[$key] = $this->maskCredentials($value);
            } else {
                $masked[$key] = $value;
            }
        }

        return $masked;
    }
}
