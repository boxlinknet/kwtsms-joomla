<?php

namespace KwtSMS\Plugin\Vmcustom\Kwtsms\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use KwtSMS\Component\Kwtsms\Administrator\Service\SettingsService;
use KwtSMS\Component\Kwtsms\Administrator\Service\TemplateResolver;

/**
 * kwtSMS VirtueMart plugin.
 * Sends SMS notifications for new orders and order status changes.
 *
 * Verified against VirtueMart 4.x event names:
 * - plgVmOnUserOrder: fires when a customer places an order
 * - plgVmOnOrderStatusChange: fires when admin changes order status
 */
final class Kwtsms extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns subscribed Joomla event names.
     * VirtueMart events are handled via direct method calls (plgVm* methods below).
     *
     * @return string[]
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [];
    }

    /**
     * VirtueMart hook: fires when a customer places a new order.
     *
     * @param object $cart  VirtueMart cart object
     * @param array  $order VirtueMart order data array
     *
     * @return bool
     */
    public function plgVmOnUserOrder(object $cart, array &$order): bool
    {
        try {
            $settings = $this->getSettingsService();

            if ($settings === null || !$settings->isGatewayReady()) {
                return true;
            }

            if ($settings->get('integration_vm_order_new_enabled', '0') !== '1') {
                return true;
            }

            $billingAddress = $order['details']['BT'] ?? null;

            if ($billingAddress === null) {
                return true;
            }

            $rawPhone = $billingAddress->phone_1 ?? $billingAddress->phone_2 ?? '';

            if (empty($rawPhone)) {
                return true;
            }

            $client = $this->getApiClient($settings);

            if ($client === null) {
                return true;
            }

            $phone = $client->normalize($rawPhone);

            if (empty($phone)) {
                return true;
            }

            $resolver = $this->getTemplateResolver();

            if ($resolver === null) {
                return true;
            }

            $locale       = !empty($billingAddress->language) ? $billingAddress->language : (string) Factory::getLanguage()->getTag();
            $shopName     = Factory::getApplication()->get('sitename', 'Our Shop');
            $orderId      = $order['details']['BT']->virtuemart_order_id ?? '';
            $orderTotal   = $order['details']['BT']->order_total ?? '';
            $firstName    = $billingAddress->first_name ?? '';
            $lastName     = $billingAddress->last_name ?? '';
            $customerName = trim($firstName . ' ' . $lastName) ?: 'Customer';

            $message = $resolver->resolve('order_new', $locale, [
                'customer_name' => $customerName,
                'order_id'      => (string) $orderId,
                'order_total'   => (string) $orderTotal,
                'shop_name'     => $shopName,
            ]);

            if (empty($message)) {
                return true;
            }

            $sender   = $settings->get('sender_id', 'KWT-SMS');
            $testMode = $settings->get('test_mode', '1') === '1';

            $client->send([$phone], $message, $sender, $testMode, $settings);
        } catch (\Throwable $e) {
            // Log but never crash VirtueMart order placement
        }

        return true;
    }

    /**
     * VirtueMart hook: fires when an admin changes an order status.
     *
     * @param array  $orders    Array of order objects
     * @param string $oldStatus Previous status code
     * @param string $newStatus New status code
     *
     * @return bool
     */
    public function plgVmOnOrderStatusChange(array &$orders, string $oldStatus, string $newStatus): bool
    {
        try {
            $settings = $this->getSettingsService();

            if ($settings === null || !$settings->isGatewayReady()) {
                return true;
            }

            if ($settings->get('integration_vm_order_status_enabled', '0') !== '1') {
                return true;
            }

            $client = $this->getApiClient($settings);

            if ($client === null) {
                return true;
            }

            $resolver = $this->getTemplateResolver();

            if ($resolver === null) {
                return true;
            }

            $sender   = $settings->get('sender_id', 'KWT-SMS');
            $testMode = $settings->get('test_mode', '1') === '1';
            $shopName = Factory::getApplication()->get('sitename', 'Our Shop');

            foreach ($orders as $order) {
                $billingAddress = $order->BT ?? null;

                if ($billingAddress === null) {
                    continue;
                }

                $rawPhone = $billingAddress->phone_1 ?? $billingAddress->phone_2 ?? '';

                if (empty($rawPhone)) {
                    continue;
                }

                $phone = $client->normalize($rawPhone);

                if (empty($phone)) {
                    continue;
                }

                $locale       = (string) Factory::getLanguage()->getTag();
                $firstName    = $billingAddress->first_name ?? '';
                $lastName     = $billingAddress->last_name ?? '';
                $customerName = trim($firstName . ' ' . $lastName) ?: 'Customer';
                $orderId      = $order->virtuemart_order_id ?? '';

                $message = $resolver->resolve('order_status_update', $locale, [
                    'customer_name' => $customerName,
                    'order_id'      => (string) $orderId,
                    'order_status'  => $newStatus,
                    'shop_name'     => $shopName,
                ]);

                if (empty($message)) {
                    continue;
                }

                $client->send([$phone], $message, $sender, $testMode, $settings);
            }
        } catch (\Throwable $e) {
            // Log but never crash VirtueMart order management
        }

        return true;
    }

    /**
     * Build an API client from stored credentials. Returns null if credentials are missing.
     */
    private function getApiClient(SettingsService $settings): ?KwtSmsApiClient
    {
        $credentials = $settings->getCredentials();

        if (empty($credentials['username'])) {
            return null;
        }

        return new KwtSmsApiClient($credentials['username'], $credentials['password']);
    }

    /**
     * Load SettingsService from the component DI container.
     */
    private function getSettingsService(): ?SettingsService
    {
        try {
            return Factory::getContainer()->get(SettingsService::class);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Load TemplateResolver from the component DI container.
     */
    private function getTemplateResolver(): ?TemplateResolver
    {
        try {
            return Factory::getContainer()->get(TemplateResolver::class);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
