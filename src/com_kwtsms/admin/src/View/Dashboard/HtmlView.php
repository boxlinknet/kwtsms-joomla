<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace KwtSMS\Component\Kwtsms\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Dashboard HTML view for com_kwtsms.
 */
final class HtmlView extends BaseHtmlView
{
	/** @var array Gateway status fields */
	public array $status = [];

	/** @var array SMS send statistics */
	public array $stats = [];

	/**
	 * Display the view.
	 *
	 * @param string|null $tpl Template name override
	 */
	public function display($tpl = null): void
	{
		$model = $this->getModel();

		$this->status = $model->getStatus();
		$this->stats  = $model->getStats();

		ToolbarHelper::title('kwtSMS', 'phone');

		parent::display($tpl);
	}
}
