<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

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
				'coverage'           => $settings->getCoverage(),
				'default_country'    => $settings->get('default_country', '965'),
				'admin_phone'        => $settings->get('admin_phone', ''),
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
				'coverage'           => [],
				'default_country'    => '965',
				'admin_phone'        => '',
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
			$settings->set('default_country', $filter->clean($data['default_country'] ?? '965', 'STRING'));
			$settings->set('admin_phone', preg_replace('/\D/', '', $filter->clean($data['admin_phone'] ?? '', 'STRING')));

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
	/**
	 * Login: verify credentials, save them, and sync balance/senderids/coverage.
	 *
	 * @param string $username API username
	 * @param string $password API password
	 *
	 * @return array API response with balance, senderids, coverage
	 */
	public function testConnection(string $username = '', string $password = ''): array
	{
		try {
			$settings = new SettingsService($this->getDatabase(), Factory::getApplication()->get('secret', ''));
			$creds    = $settings->getCredentials();

			if ($username === '') {
				$username = $creds['username'];
			}

			if ($password === '') {
				$password = $creds['password'];
			}

			if (empty($username) || empty($password)) {
				return ['result' => 'ERROR', 'description' => 'No credentials configured'];
			}

			$client      = new KwtSmsApiClient($username, $password);
			$balanceResp = $client->balance();

			if (($balanceResp['result'] ?? '') !== 'OK') {
				return $balanceResp;
			}

			// Balance OK: save credentials and sync senderids + coverage
			$settings->saveCredentials($username, $password);
			$settings->set('balance', (string) ($balanceResp['available'] ?? 0));

			$senderResp   = $client->senderid();
			$coverageResp = $client->coverage();

			if (($senderResp['result'] ?? '') === 'OK') {
				$settings->set('senderids', json_encode($senderResp['senderid'] ?? []));
			}

			if (($coverageResp['result'] ?? '') === 'OK') {
				$settings->set('coverage', json_encode($coverageResp['prefixes'] ?? []));
			}

			$settings->set('last_sync', gmdate('Y-m-d H:i:s'));

			return $balanceResp;
		} catch (\Throwable $e) {
			return ['result' => 'ERROR', 'description' => 'Connection test failed. Check Joomla error logs for details.'];
		}
	}

	/**
	 * Reload: re-sync balance, senderids, and coverage from API.
	 *
	 * @return array API response
	 */
	public function reload(): array
	{
		try {
			$settings = new SettingsService($this->getDatabase(), Factory::getApplication()->get('secret', ''));
			$creds    = $settings->getCredentials();

			if (empty($creds['username']) || empty($creds['password'])) {
				return ['result' => 'ERROR', 'description' => 'No credentials configured'];
			}

			$client       = new KwtSmsApiClient($creds['username'], $creds['password']);
			$balanceResp  = $client->balance();
			$senderResp   = $client->senderid();
			$coverageResp = $client->coverage();

			if (($balanceResp['result'] ?? '') === 'OK') {
				$settings->set('balance', (string) ($balanceResp['available'] ?? 0));
			}

			if (($senderResp['result'] ?? '') === 'OK') {
				$settings->set('senderids', json_encode($senderResp['senderid'] ?? []));
			}

			if (($coverageResp['result'] ?? '') === 'OK') {
				$settings->set('coverage', json_encode($coverageResp['prefixes'] ?? []));
			}

			$settings->set('last_sync', gmdate('Y-m-d H:i:s'));

			return ['result' => 'OK', 'balance' => $balanceResp['available'] ?? 0];
		} catch (\Throwable $e) {
			return ['result' => 'ERROR', 'description' => 'Reload failed'];
		}
	}
}
