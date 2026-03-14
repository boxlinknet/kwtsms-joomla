<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace KwtSMS\Component\Kwtsms\Administrator\View\Help;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseInterface;

/**
 * Help HTML view for com_kwtsms.
 */
final class HtmlView extends BaseHtmlView
{
	/** @var string Extension version (visible to super admins only) */
	public string $extensionVersion = '';

	/** @var string Joomla version (visible to super admins only) */
	public string $joomlaVersion = '';

	/** @var string PHP version (visible to super admins only) */
	public string $phpVersion = '';

	/** @var bool Whether the current user has core.admin */
	public bool $isAdmin = false;

	/**
	 * Display the view.
	 *
	 * @param string|null $tpl Template name override
	 */
	public function display($tpl = null): void
	{
		$app            = Factory::getApplication();
		$this->isAdmin  = $app->getIdentity()->authorise('core.admin', 'com_kwtsms');

		if ($this->isAdmin) {
			$this->joomlaVersion   = (new Version())->getShortVersion();
			$this->phpVersion      = PHP_VERSION;
			$this->extensionVersion = $this->loadExtensionVersion();
		}

		ToolbarHelper::title('kwtSMS', 'phone');

		parent::display($tpl);
	}

	private function loadExtensionVersion(): string
	{
		try {
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true)
				->select($db->quoteName('manifest_cache'))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('element') . ' = ' . $db->quote('pkg_kwtsms'));

			$json = $db->setQuery($query)->loadResult();

			if ($json) {
				$data = json_decode($json, true);
				return $data['version'] ?? '1.0.0';
			}
		} catch (\Throwable $e) {
			// ignore
		}

		return '1.0.0';
	}
}
