<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

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
	 * Get log entries with optional filters. Defaults to 200-row limit (0 = no limit).
	 *
	 * @param array{level?: string, context?: string, search?: string, date_from?: string, date_to?: string} $filters
	 * @param int $limit Row limit; 0 means no limit
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getLogs(array $filters = [], int $limit = 200): array
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName(['id', 'level', 'context', 'message', 'data', 'created']))
			->from($db->quoteName('#__kwtsms_logs'))
			->order($db->quoteName('created') . ' DESC');

		if ($limit > 0) {
			$query->setLimit($limit);
		}

		$this->applyFilters($query, $db, $filters);

		return $db->setQuery($query)->loadAssocList() ?: [];
	}

	/**
	 * Count log entries matching filters (no row limit).
	 *
	 * @param array{level?: string, context?: string, search?: string, date_from?: string, date_to?: string} $filters
	 *
	 * @return int
	 */
	public function countLogs(array $filters = []): int
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__kwtsms_logs'));

		$this->applyFilters($query, $db, $filters);

		return (int) ($db->setQuery($query)->loadResult() ?? 0);
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
	 * Export logs matching filters as a CSV string (no row limit).
	 *
	 * @param array{level?: string, context?: string, search?: string, date_from?: string, date_to?: string} $filters
	 *
	 * @return string CSV content
	 */
	public function exportCsv(array $filters = []): string
	{
		$logs = $this->getLogs($filters, 0);

		$output = implode(',', ['id', 'level', 'context', 'message', 'data', 'created']) . "\n";

		foreach ($logs as $log) {
			$output .= implode(',', array_map(
				static fn(mixed $v): string => '"' . str_replace('"', '""', (string) $v) . '"',
				$log
			)) . "\n";
		}

		return $output;
	}

	/**
	 * Apply filter conditions to a query object.
	 *
	 * @param \Joomla\Database\DatabaseQuery $query
	 * @param \Joomla\Database\DatabaseInterface $db
	 * @param array $filters
	 */
	private function applyFilters(object $query, object $db, array $filters): void
	{
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
	}
}
