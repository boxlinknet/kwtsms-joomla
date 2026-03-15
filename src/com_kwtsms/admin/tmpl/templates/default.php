<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('stylesheet', 'media/com_kwtsms/css/kwtsms.css');
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
                    <form method="post" action="<?php echo Route::_('index.php?option=com_kwtsms&task=templates.toggleEnabled', false); ?>" style="display:inline">
                        <?php echo HTMLHelper::_('form.token'); ?>
                        <input type="hidden" name="id" value="<?php echo (int) $t['id']; ?>">
                        <input type="hidden" name="enabled" value="<?php echo (int) $t['enabled']; ?>">
                        <button type="submit" class="btn btn-link p-0 border-0 align-baseline">
                            <?php if ($t['enabled']) : ?>
                                <span class="badge bg-success"><?php echo Text::_('JENABLED'); ?></span>
                            <?php else : ?>
                                <span class="badge bg-danger"><?php echo Text::_('JDISABLED'); ?></span>
                            <?php endif; ?>
                        </button>
                    </form>
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
