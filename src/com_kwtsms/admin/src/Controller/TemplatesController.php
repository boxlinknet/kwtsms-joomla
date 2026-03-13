<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
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
        $this->checkToken();

        if (!$this->app->getIdentity()->authorise('core.manage', 'com_kwtsms')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $id      = $this->input->post->getInt('id', 0);
        $enabled = $this->input->post->getInt('enabled', 0);
        $model   = $this->getModel('Templates', 'Administrator');

        $model->toggleEnabled($id, $enabled ? 0 : 1);

        $this->setRedirect(Route::_('index.php?option=com_kwtsms&view=templates', false));
    }
}
