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
use Joomla\Database\DatabaseInterface;
use KwtSMS\Component\Kwtsms\Administrator\Service\SettingsService;

/**
 * Settings controller for com_kwtsms.
 */
final class SettingsController extends BaseController
{
	/**
	 * Save settings from POST form.
	 */
	public function save(): void
	{
		$this->checkToken();

		if (!$this->app->getIdentity()->authorise('core.admin', 'com_kwtsms')) {
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$data = [
			'gateway_enabled'    => $this->input->post->getInt('gateway_enabled', 0),
			'test_mode'          => $this->input->post->getInt('test_mode', 1),
			'debug_logging'      => $this->input->post->getInt('debug_logging', 0),
			'log_retention_days' => $this->input->post->getInt('log_retention_days', 30),
			'sender_id'          => $this->input->post->getString('sender_id', ''),
			'default_country'    => $this->input->post->getString('default_country', '965'),
			'admin_phone'        => $this->input->post->getString('admin_phone', ''),
			'api_username'       => $this->input->post->getString('api_username', ''),
			'api_password'       => $this->input->post->getRaw('api_password', ''),
		];

		$model = $this->getModel('Settings', 'Administrator');

		if ($model->saveSettings($data)) {
			$this->setMessage(Text::_('COM_KWTSMS_MSG_SAVED'));
		} else {
			$this->setMessage(Text::_('COM_KWTSMS_MSG_SAVE_FAILED'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=settings', false));
	}

	/**
	 * Disconnect the gateway: clear credentials and synced data.
	 */
	public function logout(): void
	{
		$this->checkToken();

		if (!$this->app->getIdentity()->authorise('core.admin', 'com_kwtsms')) {
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		try {
			$db       = Factory::getContainer()->get(DatabaseInterface::class);
			$settings = new SettingsService($db, $this->app->get('secret', ''));

			$settings->set('api_username', '');
			$settings->set('api_password', '');
			$settings->set('gateway_configured', '0');
			$settings->set('senderids', '[]');
			$settings->set('coverage', '[]');
			$settings->set('balance', '0');
			$settings->set('last_sync', '');

			$this->setMessage(Text::_('COM_KWTSMS_MSG_LOGGED_OUT'));
		} catch (\Throwable $e) {
			$this->setMessage(Text::_('COM_KWTSMS_MSG_SAVE_FAILED'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=settings', false));
	}

	/**
	 * Reload balance, sender IDs, and coverage from the API.
	 */
	public function reload(): void
	{
		$this->checkToken();

		if (!$this->app->getIdentity()->authorise('core.admin', 'com_kwtsms')) {
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$model    = $this->getModel('Settings', 'Administrator');
		$response = $model->reload();

		if (($response['result'] ?? '') === 'OK') {
			$this->setMessage(Text::_('COM_KWTSMS_MSG_SYNCED'));
		} else {
			$this->setMessage($response['description'] ?? Text::_('COM_KWTSMS_MSG_SYNC_FAILED'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=settings', false));
	}

	/**
	 * Test the API connection, save credentials, sync data. Returns JSON.
	 */
	public function testConnection(): void
	{
		$this->checkToken();

		if (!$this->app->getIdentity()->authorise('core.admin', 'com_kwtsms')) {
			$this->app->setHeader('Content-Type', 'application/json; charset=utf-8');
			$this->app->sendHeaders();
			echo json_encode(['result' => 'ERROR', 'description' => Text::_('JERROR_ALERTNOAUTHOR')]);
			$this->app->close();
			return;
		}

		$username = $this->input->post->getString('api_username', '');
		$password = $this->input->post->getRaw('api_password', '');
		$model    = $this->getModel('Settings', 'Administrator');
		$response = $model->testConnection($username, $password);

		$this->app->setHeader('Content-Type', 'application/json; charset=utf-8');
		$this->app->sendHeaders();
		echo json_encode($response);
		$this->app->close();
	}
}
