<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
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

		if (!$this->app->getIdentity()->authorise('core.manage', 'com_kwtsms')) {
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$data = [
			'integration_vm_enabled'        => $this->input->post->getInt('integration_vm_enabled', 0),
			'integration_vm_order_new'      => $this->input->post->getInt('integration_vm_order_new', 0),
			'integration_vm_order_status'   => $this->input->post->getInt('integration_vm_order_status', 0),
			'integration_vm_customer'       => $this->input->post->getInt('integration_vm_customer', 0),
			'integration_vm_admin'          => $this->input->post->getInt('integration_vm_admin', 0),
		];

		$model = $this->getModel('Integrations', 'Administrator');

		if ($model->saveIntegrations($data)) {
			$this->setMessage(Text::_('COM_KWTSMS_MSG_SAVED'));
		} else {
			$this->setMessage(Text::_('COM_KWTSMS_MSG_SAVE_FAILED'), 'error');
		}

		$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=integrations', false));
	}
}
