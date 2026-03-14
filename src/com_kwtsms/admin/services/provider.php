<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use KwtSMS\Component\Kwtsms\Administrator\Extension\KwtsmsComponent;
use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use KwtSMS\Component\Kwtsms\Administrator\Service\LogService;
use KwtSMS\Component\Kwtsms\Administrator\Service\SettingsService;
use KwtSMS\Component\Kwtsms\Administrator\Service\TemplateResolver;

return new class implements ServiceProviderInterface {

	public function register(Container $container): void
	{
		$container->registerServiceProvider(new MVCFactory('\\KwtSMS\\Component\\Kwtsms'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\KwtSMS\\Component\\Kwtsms'));

		$container->set(
			ComponentInterface::class,
			function (Container $container): KwtsmsComponent {
				$component = new KwtsmsComponent($container->get(ComponentDispatcherFactoryInterface::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));

				return $component;
			}
		);

		$container->set(
			SettingsService::class,
			function (Container $container): SettingsService {
				$db  = $container->get(DatabaseInterface::class);
				$key = \Joomla\CMS\Factory::getApplication()->get('secret', '');

				return new SettingsService($db, $key);
			}
		);

		$container->set(
			LogService::class,
			function (Container $container): LogService {
				$db      = $container->get(DatabaseInterface::class);
				$settings = $container->get(SettingsService::class);
				$debug   = $settings->get('debug_logging', '0') === '1';

				return new LogService($db, $debug);
			}
		);

		$container->set(
			KwtSmsApiClient::class,
			function (Container $container): KwtSmsApiClient {
				$settings = $container->get(SettingsService::class);
				$creds    = $settings->getCredentials();

				return new KwtSmsApiClient($creds['username'], $creds['password']);
			}
		);

		$container->set(
			TemplateResolver::class,
			function (Container $container): TemplateResolver {
				return new TemplateResolver($container->get(DatabaseInterface::class));
			}
		);
	}
};
