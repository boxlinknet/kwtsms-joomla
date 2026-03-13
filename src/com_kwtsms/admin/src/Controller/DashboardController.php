<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
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

		try {
			$db       = Factory::getContainer()->get(DatabaseInterface::class);
			$settings = new SettingsService($db, $this->app->get('secret', ''));
			$creds    = $settings->getCredentials();
			$client    = new KwtSmsApiClient($creds['username'], $creds['password']);

			$balanceResp  = $client->balance();
			$senderResp   = $client->senderid();
			$coverageResp = $client->coverage();

			if (
				($balanceResp['result'] ?? '') === 'OK'
				&& ($senderResp['result'] ?? '') === 'OK'
				&& ($coverageResp['result'] ?? '') === 'OK'
			) {
				$settings->set('balance', (string) ($balanceResp['available'] ?? 0));
				$settings->set('senderids', json_encode($senderResp['senderid'] ?? []));
				$settings->set('coverage', json_encode($coverageResp['prefixes'] ?? []));
				$settings->set('last_sync', gmdate('Y-m-d H:i:s'));

				$this->setMessage(Text::_('COM_KWTSMS_MSG_SYNCED'));
			} else {
				$this->setMessage(Text::_('COM_KWTSMS_MSG_SYNC_FAILED'), 'error');
			}
		} catch (\Throwable $e) {
			$this->setMessage(Text::_('COM_KWTSMS_MSG_SYNC_FAILED'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=dashboard', false));
	}
}
