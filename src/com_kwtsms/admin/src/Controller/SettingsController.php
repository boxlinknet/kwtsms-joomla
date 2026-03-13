<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

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
	 * Test the API connection and return a JSON response.
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
