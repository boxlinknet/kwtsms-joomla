<?php

namespace KwtSMS\Component\Kwtsms\Administrator\View\Template;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Template edit HTML view for com_kwtsms.
 */
final class HtmlView extends BaseHtmlView
{
    /** @var object|null Current template data */
    public ?object $template = null;

    /**
     * Display the view.
     */
    public function display($tpl = null): void
    {
        $id             = $this->getState('template.id', 0);
        $this->template = $this->getModel()->getTemplate((int) $id);

        ToolbarHelper::title('kwtSMS', 'phone');

        parent::display($tpl);
    }
}
