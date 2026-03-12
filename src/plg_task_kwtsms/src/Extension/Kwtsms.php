<?php

namespace KwtSMS\Plugin\Task\Kwtsms\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;
use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use KwtSMS\Component\Kwtsms\Administrator\Service\SettingsService;

/**
 * kwtSMS task plugin. Runs daily sync of balance, sender IDs, and coverage.
 */
final class Kwtsms extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;

    protected const TASKS_MAP = [
        'kwtsms.sync' => [
            'langConstPrefix' => 'PLG_TASK_KWTSMS_SYNC',
            'method'          => 'syncGatewayData',
            'form'            => 'synctask',
        ],
    ];

    /**
     * Returns subscribed event names.
     *
     * @return string[]
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            'onExecuteScheduledTask' => 'onExecuteScheduledTask',
        ];
    }

    /**
     * Sync balance, sender IDs, and coverage from the kwtSMS API.
     */
    protected function syncGatewayData(ExecuteTaskEvent $event): int
    {
        // Load SettingsService from the component's DI container
        $container = Factory::getContainer();

        try {
            $settings = $container->get(SettingsService::class);
        } catch (\Throwable $e) {
            // Component not installed or container not available
            return Status::OK;
        }

        // Pre-flight: skip if gateway is not enabled or not configured
        if ($settings->get('gateway_enabled', '0') !== '1' || $settings->get('gateway_configured', '0') !== '1') {
            return Status::OK;
        }

        $creds  = $settings->getCredentials();
        $client = new KwtSmsApiClient($creds['username'], $creds['password']);

        $balanceResp  = $client->balance();
        $senderResp   = $client->senderid();
        $coverageResp = $client->coverage();

        if (
            ($balanceResp['result'] ?? '') === 'OK'
            && ($senderResp['result'] ?? '') === 'OK'
            && ($coverageResp['result'] ?? '') === 'OK'
        ) {
            $settings->set('balance', (string) ($balanceResp['available'] ?? 0));
            $settings->set('senderids', json_encode($senderResp['senderid'] ?? []));
            $settings->set('coverage', json_encode($coverageResp['prefixes'] ?? []));
            $settings->set('last_sync', gmdate('Y-m-d H:i:s'));
        }

        return Status::OK;
    }
}
