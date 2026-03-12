<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Determine active view from URL
$currentView = Factory::getApplication()->input->getCmd('view', 'dashboard');

$tabs = [
    'dashboard'    => Text::_('COM_KWTSMS_DASHBOARD'),
    'settings'     => Text::_('COM_KWTSMS_SETTINGS'),
    'templates'    => Text::_('COM_KWTSMS_TEMPLATES'),
    'integrations' => Text::_('COM_KWTSMS_INTEGRATIONS'),
    'logs'         => Text::_('COM_KWTSMS_LOGS'),
    'help'         => Text::_('COM_KWTSMS_HELP'),
];
?>
<div class="kwtsms-tabs mb-3">
    <ul class="nav nav-tabs" role="tablist">
        <?php foreach ($tabs as $view => $label) : ?>
            <?php $isActive = ($currentView === $view); ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link<?php echo $isActive ? ' active' : ''; ?>"
                   href="<?php echo Route::_('index.php?option=com_kwtsms&view=' . $view); ?>"
                   role="tab">
                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
