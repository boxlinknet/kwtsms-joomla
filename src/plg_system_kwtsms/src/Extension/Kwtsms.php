<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace KwtSMS\Plugin\System\Kwtsms\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * kwtSMS system plugin. Bootstraps the gateway guard on initialise.
 */
final class Kwtsms extends CMSPlugin implements SubscriberInterface
{
    /**
     * Track whether the gateway is ready so other plugins can query it.
     */
    private static bool $gatewayReady = false;

    /**
     * Returns subscribed event names.
     *
     * @return string[]
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise' => 'onAfterInitialise',
            'onUserAfterSave'   => 'onUserAfterSave',
            'onUserAfterDelete' => 'onUserAfterDelete',
        ];
    }

    /**
     * Bootstrap: check gateway readiness and cache result for the request lifecycle.
     */
    public function onAfterInitialise(Event $event): void
    {
        // Gateway readiness check will be implemented when SettingsService
        // is loaded from the component container in Phase 2.
        // Phase 1: stub that loads the plugin and marks gateway as not yet checked.
        self::$gatewayReady = false;
    }

    /**
     * User registration hook: Phase 2 implementation.
     * Phase 1: stub only.
     */
    public function onUserAfterSave(Event $event): void
    {
        // Phase 2: send welcome SMS on new user registration using user_registration template.
    }

    /**
     * User delete hook: clean up any cached user data.
     */
    public function onUserAfterDelete(Event $event): void
    {
        // No user-specific cache to clear in Phase 1.
    }

    /**
     * Check if the kwtSMS gateway is ready for this request.
     */
    public static function isGatewayReady(): bool
    {
        return self::$gatewayReady;
    }
}
