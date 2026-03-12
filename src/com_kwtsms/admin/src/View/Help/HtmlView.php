<?php

namespace KwtSMS\Component\Kwtsms\Administrator\View\Help;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Version;

/**
 * Help HTML view for com_kwtsms.
 */
final class HtmlView extends BaseHtmlView
{
	/** @var string Extension version */
	public string $extensionVersion = '1.0.0';

	/** @var string Joomla version */
	public string $joomlaVersion = '';

	/** @var string PHP version */
	public string $phpVersion = '';

	/**
	 * Display the view.
	 *
	 * @param string|null $tpl Template name override
	 */
	public function display($tpl = null): void
	{
		$this->joomlaVersion   = (new Version())->getShortVersion();
		$this->phpVersion      = PHP_VERSION;

		ToolbarHelper::title('kwtSMS', 'phone');

		parent::display($tpl);
	}
}
