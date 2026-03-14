<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace KwtSMS\Component\Kwtsms\Administrator\View\Templates;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Templates list HTML view for com_kwtsms.
 */
final class HtmlView extends BaseHtmlView
{
    /** @var array<int, array<string, mixed>> Template rows */
    public array $templates = [];

    /**
     * Display the view.
     */
    public function display($tpl = null): void
    {
        $this->templates = $this->getModel()->getTemplates();

        ToolbarHelper::title('kwtSMS', 'phone');

        parent::display($tpl);
    }
}
