<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace KwtSMS\Component\Kwtsms\Administrator\View\Template;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
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
        $id             = Factory::getApplication()->getInput()->getInt('id', 0);
        $this->template = $this->getModel()->getTemplate((int) $id);

        ToolbarHelper::title('kwtSMS', 'phone');

        parent::display($tpl);
    }
}
