<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

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
	/** @var array<int, array<string, mixed>> Log entries (capped at 200) */
	public array $logs = [];

	/** @var array<string, string> Current filter values */
	public array $filters = [];

	/** @var int Total matching entries without the 200-row cap */
	public int $total = 0;

	/**
	 * Display the view.
	 */
	public function display($tpl = null): void
	{
		$input = Factory::getApplication()->getInput();

		$allowedLevels = ['debug', 'info', 'warning', 'error'];
		$rawLevel      = $input->getString('filter_level', '');

		$this->filters = [
			'level'     => in_array($rawLevel, $allowedLevels, true) ? $rawLevel : '',
			'search'    => $input->getString('filter_search', ''),
			'date_from' => $this->sanitizeDate($input->getString('filter_from', '')),
			'date_to'   => $this->sanitizeDate($input->getString('filter_to', '')),
		];

		$this->logs  = $this->getModel()->getLogs($this->filters);
		$this->total = $this->getModel()->countLogs($this->filters);

		ToolbarHelper::title('kwtSMS', 'phone');

		parent::display($tpl);
	}

	private function sanitizeDate(string $value): string
	{
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
			return '';
		}

		$dt = \DateTime::createFromFormat('Y-m-d', $value);

		return ($dt && $dt->format('Y-m-d') === $value) ? $value : '';
	}
}
