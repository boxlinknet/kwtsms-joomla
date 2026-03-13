<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Logs controller for com_kwtsms.
 */
final class LogsController extends BaseController
{
	/**
	 * Clear all logs.
	 */
	public function clearLogs(): void
	{
		$this->checkToken();

		if (!$this->app->getIdentity()->authorise('core.manage', 'com_kwtsms')) {
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$model = $this->getModel('Logs', 'Administrator');
		$model->clearLogs();

		$this->setMessage(Text::_('COM_KWTSMS_MSG_LOGS_CLEARED'));
		$this->setRedirect(Route::_('index.php?option=com_kwtsms&view=logs', false));
	}

	/**
	 * Export logs as CSV download, honoring current filter state.
	 */
	public function exportCsv(): void
	{
		$this->checkToken();

		if (!$this->app->getIdentity()->authorise('core.manage', 'com_kwtsms')) {
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$allowedLevels = ['debug', 'info', 'warning', 'error'];
		$rawLevel      = $this->input->post->getString('filter_level', '');

		$filters = [
			'level'     => in_array($rawLevel, $allowedLevels, true) ? $rawLevel : '',
			'search'    => $this->input->post->getString('filter_search', ''),
			'date_from' => $this->sanitizeDate($this->input->post->getString('filter_from', '')),
			'date_to'   => $this->sanitizeDate($this->input->post->getString('filter_to', '')),
		];

		$model = $this->getModel('Logs', 'Administrator');
		$csv   = $model->exportCsv($filters);

		$this->app->setHeader('Content-Type', 'text/csv; charset=utf-8');
		$this->app->setHeader('Content-Disposition', 'attachment; filename="kwtsms-logs.csv"');
		$this->app->sendHeaders();
		echo $csv;
		$this->app->close();
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
