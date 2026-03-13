<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

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

		$data  = $this->input->post->getArray();
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
		$this->checkToken('get');

		$username = $this->input->get->getString('api_username', '');
		$password = $this->input->get->getString('api_password', '');
		$model    = $this->getModel('Settings', 'Administrator');
		$response = $model->testConnection($username, $password);

		$this->app->setHeader('Content-Type', 'application/json; charset=utf-8');
		$this->app->sendHeaders();
		echo json_encode($response);
		$this->app->close();
	}
}
