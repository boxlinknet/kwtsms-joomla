<?php

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('stylesheet', 'com_kwtsms/css/kwtsms.css', [], true);
?>

<?php include JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/layout/tabs.php'; ?>

<div class="container-fluid mt-3">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?php echo Text::_('COM_KWTSMS_TEMPLATE_KEY'); ?></th>
                <th><?php echo Text::_('COM_KWTSMS_TEMPLATE_LANG'); ?></th>
                <th><?php echo Text::_('COM_KWTSMS_TEMPLATE_TITLE'); ?></th>
                <th><?php echo Text::_('COM_KWTSMS_TEMPLATE_ENABLED'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->templates as $t) : ?>
            <tr>
                <td><code><?php echo htmlspecialchars($t['template_key'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                <td>
                    <?php if ($t['lang'] === 'ar') : ?>
                        <span class="badge bg-info">AR</span>
                    <?php else : ?>
                        <span class="badge bg-secondary">EN</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($t['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <a href="<?php echo Route::_('index.php?option=com_kwtsms&task=templates.toggleEnabled&id=' . (int)$t['id'] . '&enabled=' . (int)$t['enabled'] . '&' . \Joomla\CMS\Session\Session::getFormToken() . '=1', false); ?>">
                        <?php if ($t['enabled']) : ?>
                            <span class="badge bg-success"><?php echo Text::_('JENABLED'); ?></span>
                        <?php else : ?>
                            <span class="badge bg-danger"><?php echo Text::_('JDISABLED'); ?></span>
                        <?php endif; ?>
                    </a>
                </td>
                <td>
                    <a href="<?php echo Route::_('index.php?option=com_kwtsms&view=template&id=' . (int)$t['id'], false); ?>"
                       class="btn btn-sm btn-outline-secondary">
                        <?php echo Text::_('COM_KWTSMS_TEMPLATE_EDIT'); ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
