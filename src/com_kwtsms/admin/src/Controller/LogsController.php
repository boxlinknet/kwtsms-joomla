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
	 * Export logs as CSV download.
	 */
	public function exportCsv(): void
	{
		$this->checkToken('get');

		if (!$this->app->getIdentity()->authorise('core.manage', 'com_kwtsms')) {
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$model = $this->getModel('Logs', 'Administrator');
		$csv   = $model->exportCsv();

		$this->app->setHeader('Content-Type', 'text/csv; charset=utf-8');
		$this->app->setHeader('Content-Disposition', 'attachment; filename="kwtsms-logs.csv"');
		$this->app->sendHeaders();
		echo $csv;
		$this->app->close();
	}
}
