<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use KwtSMS\Component\Kwtsms\Administrator\Service\SettingsService;

/**
 * Settings model for com_kwtsms.
 */
final class SettingsModel extends BaseDatabaseModel
{
	/**
	 * Get all settings for display. Returns masked password.
	 *
	 * @return array<string, mixed>
	 */
	public function getSettings(): array
	{
		try {
			$settings = new SettingsService($this->getDatabase(), Factory::getApplication()->get('secret', ''));

			return [
				'gateway_enabled'    => $settings->get('gateway_enabled', '0'),
				'gateway_configured' => $settings->get('gateway_configured', '0'),
				'test_mode'          => $settings->get('test_mode', '1'),
				'debug_logging'      => $settings->get('debug_logging', '0'),
				'log_retention_days' => $settings->get('log_retention_days', '30'),
				'api_username'       => $settings->get('api_password', '') !== '' ? $settings->getCredentials()['username'] : '',
				'api_password'       => $settings->get('api_password', '') !== '' ? '***' : '',
				'sender_id'          => $settings->get('sender_id', ''),
				'senderids'          => $settings->getSenderIds(),
			];
		} catch (\Throwable $e) {
			return [
				'gateway_enabled'    => '0',
				'gateway_configured' => '0',
				'test_mode'          => '1',
				'debug_logging'      => '0',
				'log_retention_days' => '30',
				'api_username'       => '',
				'api_password'       => '',
				'sender_id'          => '',
				'senderids'          => [],
			];
		}
	}

	/**
	 * Save settings from POST data.
	 *
	 * @param array<string, mixed> $data Raw POST data
	 */
	public function saveSettings(array $data): bool
	{
		try {
			$filter   = InputFilter::getInstance();
			$settings = new SettingsService($this->getDatabase(), Factory::getApplication()->get('secret', ''));

			$settings->set('gateway_enabled', $filter->clean($data['gateway_enabled'] ?? '0', 'INT') ? '1' : '0');
			$settings->set('test_mode', $filter->clean($data['test_mode'] ?? '1', 'INT') ? '1' : '0');
			$settings->set('debug_logging', $filter->clean($data['debug_logging'] ?? '0', 'INT') ? '1' : '0');
			$settings->set('log_retention_days', (string) max(1, min(365, (int) ($data['log_retention_days'] ?? 30))));
			$settings->set('sender_id', $filter->clean($data['sender_id'] ?? '', 'STRING'));

			$username = $filter->clean($data['api_username'] ?? '', 'STRING');
			$password = $filter->clean($data['api_password'] ?? '', 'RAW');

			if ($username !== '') {
				if ($password === '' || $password === '***') {
					// Keep existing password: just save username
					$existingCreds = $settings->getCredentials();
					$settings->saveCredentials($username, $existingCreds['password']);
				} else {
					$settings->saveCredentials($username, $password);
				}
			}

			return true;
		} catch (\Throwable $e) {
			return false;
		}
	}

	/**
	 * Test the API connection. Uses provided credentials if given, otherwise uses stored credentials.
	 *
	 * @param string $username Optional username to test with (typed in form, not yet saved)
	 * @param string $password Optional password to test with (typed in form, not yet saved)
	 *
	 * @return array API response from balance endpoint
	 */
	public function testConnection(string $username = '', string $password = ''): array
	{
		try {
			if ($username === '') {
				$settings = new SettingsService($this->getDatabase(), Factory::getApplication()->get('secret', ''));
				$creds    = $settings->getCredentials();
				$username = $creds['username'];
				$password = $creds['password'];
			}

			if (empty($username) || empty($password)) {
				return ['result' => 'ERROR', 'description' => 'No credentials configured'];
			}

			$client = new KwtSmsApiClient($username, $password);

			return $client->balance();
		} catch (\Throwable $e) {
			return ['result' => 'ERROR', 'description' => 'Connection test failed. Check Joomla error logs for details.'];
		}
	}
}
