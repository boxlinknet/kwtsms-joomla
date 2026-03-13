<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Integrations controller for com_kwtsms.
 */
final class IntegrationsController extends BaseController
{
	/**
	 * Save integration settings.
	 */
	public function save(): void
	{
		$this->checkToken();

		$data  = $this->input->post->getArray();
		$model = $this->getModel('Integrations', 'Administrator');

		if ($model->saveIntegrations($data)) {
			$this->setMessage(Text::_('COM_KWTSMS_MSG_SAVED'));
		} else {
			$this->setMessage(Text::_('COM_KWTSMS_MSG_SAVE_FAILED'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=integrations', false));
	}
}
