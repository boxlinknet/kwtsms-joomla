<?php
/**
 * PHPUnit bootstrap.
 * Loads extension classes without Joomla for unit and integration tests.
 * Unit tests: no credentials needed.
 * Integration tests: requires tests/.env with KWTSMS_* vars.
 */
declare(strict_types=1);

define('_JEXEC', 1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load .env for integration tests if present
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $_ENV[$key] = $value;
    }
}
