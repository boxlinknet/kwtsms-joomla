<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Templates list controller for com_kwtsms.
 */
final class TemplatesController extends BaseController
{
    /**
     * Toggle enabled state for a template.
     */
    public function toggleEnabled(): void
    {
        $this->checkToken('get');

        $id      = $this->input->getInt('id', 0);
        $enabled = $this->input->getInt('enabled', 0);
        $model   = $this->getModel('Templates', 'Administrator');

        $model->toggleEnabled($id, $enabled ? 0 : 1);

        $this->setRedirect(Route::_('index.php?option=com_kwtsms&view=templates', false));
    }
}
