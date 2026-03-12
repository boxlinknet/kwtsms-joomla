<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Logs model for com_kwtsms.
 */
final class LogsModel extends BaseDatabaseModel
{
	/**
	 * Get log entries with optional filters.
	 *
	 * @param array{level?: string, context?: string, search?: string, date_from?: string, date_to?: string} $filters
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getLogs(array $filters = []): array
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName(['id', 'level', 'context', 'message', 'data', 'created']))
			->from($db->quoteName('#__kwtsms_logs'))
			->order($db->quoteName('created') . ' DESC')
			->setLimit(200);

		if (!empty($filters['level'])) {
			$level = $filters['level'];
			$query->where($db->quoteName('level') . ' = :level');
			$query->bind(':level', $level, ParameterType::STRING);
		}

		if (!empty($filters['context'])) {
			$ctx = $filters['context'];
			$query->where($db->quoteName('context') . ' = :context');
			$query->bind(':context', $ctx, ParameterType::STRING);
		}

		if (!empty($filters['search'])) {
			$search = '%' . $db->escape($filters['search']) . '%';
			$query->where($db->quoteName('message') . ' LIKE :search');
			$query->bind(':search', $search, ParameterType::STRING);
		}

		if (!empty($filters['date_from'])) {
			$df = $filters['date_from'] . ' 00:00:00';
			$query->where($db->quoteName('created') . ' >= :date_from');
			$query->bind(':date_from', $df, ParameterType::STRING);
		}

		if (!empty($filters['date_to'])) {
			$dt = $filters['date_to'] . ' 23:59:59';
			$query->where($db->quoteName('created') . ' <= :date_to');
			$query->bind(':date_to', $dt, ParameterType::STRING);
		}

		return $db->setQuery($query)->loadAssocList() ?: [];
	}

	/**
	 * Delete all log entries.
	 *
	 * @return int Number of rows deleted
	 */
	public function clearLogs(): int
	{
		$db = $this->getDatabase();
		$db->setQuery('DELETE FROM ' . $db->quoteName('#__kwtsms_logs'))->execute();

		return $db->getAffectedRows();
	}

	/**
	 * Export all logs as a CSV string.
	 */
	public function exportCsv(): string
	{
		$logs = $this->getLogs();

		$output = implode(',', ['id', 'level', 'context', 'message', 'data', 'created']) . "\n";

		foreach ($logs as $log) {
			$output .= implode(',', array_map(
				static fn(mixed $v): string => '"' . str_replace('"', '""', (string) $v) . '"',
				$log
			)) . "\n";
		}

		return $output;
	}
}
