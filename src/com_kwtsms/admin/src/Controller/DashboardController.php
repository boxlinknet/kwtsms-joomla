<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
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
			$container = $this->getApplication()->getContainer();
			$settings  = $container->get(SettingsService::class);
			$creds     = $settings->getCredentials();
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

				$this->setMessage($this->getLanguage()->_('COM_KWTSMS_MSG_SYNCED'));
			} else {
				$this->setMessage($this->getLanguage()->_('COM_KWTSMS_MSG_SYNC_FAILED'), 'error');
			}
		} catch (\Throwable $e) {
			$this->setMessage($this->getLanguage()->_('COM_KWTSMS_MSG_SYNC_FAILED'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=dashboard', false));
	}
}
