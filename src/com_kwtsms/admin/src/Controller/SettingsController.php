<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

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
			$this->setMessage($this->getLanguage()->_('COM_KWTSMS_MSG_SAVED'));
		} else {
			$this->setMessage($this->getLanguage()->_('COM_KWTSMS_MSG_SAVE_FAILED'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=settings', false));
	}

	/**
	 * Test the API connection and return a JSON response.
	 */
	public function testConnection(): void
	{
		$this->checkToken('get');

		$model    = $this->getModel('Settings', 'Administrator');
		$response = $model->testConnection();

		$this->getApplication()->setHeader('Content-Type', 'application/json; charset=utf-8');
		echo json_encode($response);
		$this->getApplication()->close();
	}
}
