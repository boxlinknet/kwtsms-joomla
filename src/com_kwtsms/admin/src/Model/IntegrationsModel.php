<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use KwtSMS\Component\Kwtsms\Administrator\Service\SettingsService;

/**
 * Integrations model for com_kwtsms.
 */
final class IntegrationsModel extends BaseDatabaseModel
{
	/**
	 * Get integration configuration for all supported integrations.
	 *
	 * @return array<string, mixed>
	 */
	public function getIntegrations(): array
	{
		try {
			$settings = Factory::getContainer()->get(SettingsService::class);

			return [
				'virtuemart' => [
					'enabled'    => $settings->get('integration_vm_enabled', '0') === '1',
					'events'     => [
						'order_new'           => $settings->get('integration_vm_order_new_enabled', '0') === '1',
						'order_status_update' => $settings->get('integration_vm_order_status_enabled', '0') === '1',
					],
					'recipients' => [
						'customer' => $settings->get('integration_vm_notify_customer', '1') === '1',
						'admin'    => $settings->get('integration_vm_notify_admin', '0') === '1',
					],
				],
			];
		} catch (\Throwable $e) {
			return [
				'virtuemart' => [
					'enabled'    => false,
					'events'     => ['order_new' => false, 'order_status_update' => false],
					'recipients' => ['customer' => true, 'admin' => false],
				],
			];
		}
	}

	/**
	 * Save integration settings.
	 *
	 * @param array<string, mixed> $data Raw POST data
	 */
	public function saveIntegrations(array $data): bool
	{
		try {
			$filter   = InputFilter::getInstance();
			$settings = Factory::getContainer()->get(SettingsService::class);

			$settings->set('integration_vm_enabled', isset($data['integration_vm_enabled']) ? '1' : '0');
			$settings->set('integration_vm_order_new_enabled', isset($data['integration_vm_order_new']) ? '1' : '0');
			$settings->set('integration_vm_order_status_enabled', isset($data['integration_vm_order_status']) ? '1' : '0');
			$settings->set('integration_vm_notify_customer', isset($data['integration_vm_customer']) ? '1' : '0');
			$settings->set('integration_vm_notify_admin', isset($data['integration_vm_admin']) ? '1' : '0');

			return true;
		} catch (\Throwable $e) {
			return false;
		}
	}
}
