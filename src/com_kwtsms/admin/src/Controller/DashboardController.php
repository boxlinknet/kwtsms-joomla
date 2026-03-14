<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filter\InputFilter;
use Joomla\Database\DatabaseInterface;
use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use KwtSMS\Component\Kwtsms\Administrator\Service\LogService;
use KwtSMS\Component\Kwtsms\Administrator\Service\SettingsService;

/**
 * Dashboard controller for com_kwtsms.
 */
final class DashboardController extends BaseController
{
	/**
	 * Sync balance, sender IDs, and coverage from the kwtSMS API.
	 */
	public function syncNow(): void
	{
		$this->checkToken();

		if (!$this->app->getIdentity()->authorise('core.manage', 'com_kwtsms')) {
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		try {
			$db       = Factory::getContainer()->get(DatabaseInterface::class);
			$settings = new SettingsService($db, $this->app->get('secret', ''));
			$log      = new LogService($db, $settings->get('debug_logging', '0') === '1');
			$creds    = $settings->getCredentials();
			$client   = new KwtSmsApiClient($creds['username'], $creds['password']);

			$balanceResp  = $client->balance();
			$senderResp   = $client->senderid();
			$coverageResp = $client->coverage();

			if (
				($balanceResp['result'] ?? '') === 'OK'
				&& ($senderResp['result'] ?? '') === 'OK'
				&& ($coverageResp['result'] ?? '') === 'OK'
			) {
				$balance   = (string) ($balanceResp['available'] ?? 0);
				$senderIds = $senderResp['senderid'] ?? [];
				$prefixes  = $coverageResp['prefixes'] ?? [];

				$settings->set('balance', $balance);
				$settings->set('senderids', json_encode($senderIds));
				$settings->set('coverage', json_encode($prefixes));
				$settings->set('last_sync', gmdate('Y-m-d H:i:s'));

				$log->info('sync', 'Gateway sync completed', [
					'balance'       => $balance,
					'senderid_count' => count($senderIds),
					'coverage_count' => count($prefixes),
				]);

				$this->setMessage(Text::_('COM_KWTSMS_MSG_SYNCED'));
			} else {
				$log->error('sync', 'Gateway sync failed: API returned non-OK', [
					'balance_result'  => $balanceResp['result'] ?? 'missing',
					'sender_result'   => $senderResp['result'] ?? 'missing',
					'coverage_result' => $coverageResp['result'] ?? 'missing',
				]);

				$this->setMessage(Text::_('COM_KWTSMS_MSG_SYNC_FAILED'), 'error');
			}
		} catch (\Throwable $e) {
			if (isset($log)) {
				$log->error('sync', 'Gateway sync exception: ' . $e->getMessage());
			}

			$this->setMessage(Text::_('COM_KWTSMS_MSG_SYNC_FAILED'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=dashboard', false));
	}

	/**
	 * Send a test SMS from the dashboard.
	 */
	public function sendTest(): void
	{
		$this->checkToken();

		if (!$this->app->getIdentity()->authorise('core.manage', 'com_kwtsms')) {
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		try {
			$db       = Factory::getContainer()->get(DatabaseInterface::class);
			$settings = new SettingsService($db, $this->app->get('secret', ''));
			$log      = new LogService($db, $settings->get('debug_logging', '0') === '1');
			$creds    = $settings->getCredentials();
			$filter   = InputFilter::getInstance();

			$phone   = $filter->clean($this->input->post->getString('test_phone', ''), 'STRING');
			$message = $filter->clean($this->input->post->getString('test_message', ''), 'STRING');

			if ($phone === '' || $message === '') {
				$this->setMessage(Text::_('COM_KWTSMS_MSG_TEST_MISSING'), 'warning');
				$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=dashboard', false));

				return;
			}

			$client   = new KwtSmsApiClient($creds['username'], $creds['password']);
			$sender   = $settings->get('sender_id', 'KWT-SMS');
			$testMode = $settings->get('test_mode', '1') === '1';
			$response = $client->send([$phone], $message, $sender, $testMode, $settings);
			$result   = $response['result'] ?? 'ERROR';

			if ($result === 'OK') {
				$log->info('send', 'Test SMS sent', [
					'recipient' => $phone,
					'msg_id'    => $response['msg-id'] ?? '',
					'test_mode' => $testMode,
				]);

				$balanceAfter = $response['balance-after'] ?? '';
				$msgText      = $testMode
					? Text::sprintf('COM_KWTSMS_MSG_TEST_SENT_TEST', $balanceAfter)
					: Text::sprintf('COM_KWTSMS_MSG_TEST_SENT', $balanceAfter);

				$this->setMessage($msgText);
			} elseif ($result === 'SKIPPED') {
				$reason = $response['reason'] ?? 'Unknown';

				$log->warning('send', 'Test SMS skipped: ' . $reason, ['recipient' => $phone]);
				$this->setMessage(Text::sprintf('COM_KWTSMS_MSG_TEST_SKIPPED', $reason), 'warning');
			} else {
				$desc = $response['description'] ?? $response['reason'] ?? 'Unknown error';

				$log->error('send', 'Test SMS failed: ' . $desc, ['recipient' => $phone]);
				$this->setMessage(Text::sprintf('COM_KWTSMS_MSG_TEST_FAILED', $desc), 'error');
			}
		} catch (\Throwable $e) {
			$this->setMessage(Text::sprintf('COM_KWTSMS_MSG_TEST_FAILED', $e->getMessage()), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=dashboard', false));
	}
}
