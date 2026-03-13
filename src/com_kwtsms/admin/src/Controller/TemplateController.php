<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

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

        $data  = $this->input->post->getArray();
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
