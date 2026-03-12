<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Default display controller for com_kwtsms. Routes to the requested view.
 */
final class DisplayController extends BaseController
{
	/**
	 * Display the component.
	 *
	 * @param bool        $cachable  Whether the view output should be cached
	 * @param bool|array  $urlparams An array of safe URL parameters and their variable types
	 *
	 * @return static
	 */
	public function display($cachable = false, $urlparams = false): static
	{
		$view = $this->input->getCmd('view', 'dashboard');
		$this->input->set('view', $view);

		return parent::display($cachable, $urlparams);
	}
}
