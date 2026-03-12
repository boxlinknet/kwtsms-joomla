<?php

namespace KwtSMS\Component\Kwtsms\Administrator\View\Logs;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Logs HTML view for com_kwtsms.
 */
final class HtmlView extends BaseHtmlView
{
	/** @var array<int, array<string, mixed>> Log entries */
	public array $logs = [];

	/** @var array<string, string> Current filter values */
	public array $filters = [];

	/**
	 * Display the view.
	 */
	public function display($tpl = null): void
	{
		$input = Factory::getApplication()->getInput();

		$this->filters = [
			'level'     => $input->getString('filter_level', ''),
			'search'    => $input->getString('filter_search', ''),
			'date_from' => $input->getString('filter_from', ''),
			'date_to'   => $input->getString('filter_to', ''),
		];

		$this->logs = $this->getModel()->getLogs($this->filters);

		ToolbarHelper::title('kwtSMS', 'phone');

		parent::display($tpl);
	}
}
