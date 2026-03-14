<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace KwtSMS\Component\Kwtsms\Administrator\View\Integrations;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Integrations HTML view for com_kwtsms.
 */
final class HtmlView extends BaseHtmlView
{
	/** @var array<string, mixed> Integration configuration */
	public array $integrations = [];

	/**
	 * Display the view.
	 *
	 * @param string|null $tpl Template name override
	 */
	public function display($tpl = null): void
	{
		$this->integrations = $this->getModel()->getIntegrations();

		ToolbarHelper::title('kwtSMS', 'phone');

		parent::display($tpl);
	}
}
