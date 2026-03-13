<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Single template controller for com_kwtsms.
 */
final class TemplateController extends BaseController
{
    /**
     * Save a template and redirect back to list.
     */
    public function save(): void
    {
        $this->checkToken();

        if (!$this->app->getIdentity()->authorise('core.manage', 'com_kwtsms')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $data = [
            'id'      => $this->input->post->getInt('id', 0),
            'title'   => $this->input->post->getString('title', ''),
            'body'    => $this->input->post->getRaw('body', ''),
            'enabled' => $this->input->post->getInt('enabled', 1),
        ];

        $model = $this->getModel('Template', 'Administrator');

        if ($model->saveTemplate($data)) {
            $this->setMessage(Text::_('COM_KWTSMS_MSG_SAVED'));
        } else {
            $this->setMessage(Text::_('COM_KWTSMS_MSG_SAVE_FAILED'), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_kwtsms&view=templates', false));
    }

    /**
     * Cancel edit and redirect back to list.
     */
    public function cancel(): void
    {
        $this->setRedirect(Route::_('index.php?option=com_kwtsms&view=templates', false));
    }
}
