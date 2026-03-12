<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use KwtSMS\Component\Kwtsms\Administrator\Service\SettingsService;

/**
 * Dashboard model for com_kwtsms.
 */
final class DashboardModel extends BaseDatabaseModel
{
	/**
	 * Get gateway status fields from SettingsService.
	 *
	 * @return array{gateway_enabled: string, gateway_configured: string, test_mode: string, balance: string, sender_id: string, last_sync: string}
	 */
	public function getStatus(): array
	{
		try {
			$settings = Factory::getContainer()->get(SettingsService::class);

			return [
				'gateway_enabled'    => $settings->get('gateway_enabled', '0'),
				'gateway_configured' => $settings->get('gateway_configured', '0'),
				'test_mode'          => $settings->get('test_mode', '1'),
				'balance'            => $settings->get('balance', '0'),
				'sender_id'          => $settings->get('sender_id', ''),
				'last_sync'          => $settings->get('last_sync', ''),
			];
		} catch (\Throwable $e) {
			return [
				'gateway_enabled'    => '0',
				'gateway_configured' => '0',
				'test_mode'          => '1',
				'balance'            => '0',
				'sender_id'          => '',
				'last_sync'          => '',
			];
		}
	}

	/**
	 * Get SMS send statistics from the messages table.
	 *
	 * @return array{sent_today: int, sent_week: int, sent_month: int, total_errors: int}
	 */
	public function getStats(): array
	{
		$db = $this->getDatabase();

		try {
			$now = new \DateTime('now', new \DateTimeZone('UTC'));

			$todayStart = (clone $now)->format('Y-m-d') . ' 00:00:00';
			$weekStart  = (clone $now)->modify('monday this week')->format('Y-m-d') . ' 00:00:00';
			$monthStart = (clone $now)->format('Y-m-01') . ' 00:00:00';

			$query = $db->getQuery(true)
				->select([
					'SUM(CASE WHEN created >= ' . $db->quote($todayStart) . " AND status = 'ok' THEN 1 ELSE 0 END) AS sent_today",
					'SUM(CASE WHEN created >= ' . $db->quote($weekStart) . " AND status = 'ok' THEN 1 ELSE 0 END) AS sent_week",
					'SUM(CASE WHEN created >= ' . $db->quote($monthStart) . " AND status = 'ok' THEN 1 ELSE 0 END) AS sent_month",
					"SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) AS total_errors",
				])
				->from($db->quoteName('#__kwtsms_messages'));

			$row = $db->setQuery($query)->loadAssoc();

			return [
				'sent_today'   => (int) ($row['sent_today'] ?? 0),
				'sent_week'    => (int) ($row['sent_week'] ?? 0),
				'sent_month'   => (int) ($row['sent_month'] ?? 0),
				'total_errors' => (int) ($row['total_errors'] ?? 0),
			];
		} catch (\Throwable $e) {
			return ['sent_today' => 0, 'sent_week' => 0, 'sent_month' => 0, 'total_errors' => 0];
		}
	}
}
