<?php

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('stylesheet', 'com_kwtsms/css/kwtsms.css', [], true);

$template = $this->template;

if ($template === null) {
    echo '<div class="alert alert-danger">Template not found.</div>';
    return;
}

$placeholders = $template->placeholders ?? '';
$isArabic     = $template->lang === 'ar';
$pageLimit    = $isArabic ? 70 : 160;
?>

<?php echo $this->loadTemplate('../../layout/tabs'); ?>

<div class="container-fluid mt-3">
    <form method="post" action="<?php echo Route::_('index.php?option=com_kwtsms&task=template.save', false); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
        <input type="hidden" name="id" value="<?php echo (int) $template->id; ?>">

        <div class="card mb-3">
            <div class="card-header kwtsms-card-header"><?php echo Text::_('COM_KWTSMS_TEMPLATE_EDIT'); ?></div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <?php echo htmlspecialchars($template->template_key, ENT_QUOTES, 'UTF-8'); ?>
                        &nbsp;|&nbsp;
                        <?php echo $isArabic ? 'Arabic' : 'English'; ?>
                    </label>
                </div>

                <div class="mb-3">
                    <label for="tmpl_title" class="form-label"><?php echo Text::_('COM_KWTSMS_TEMPLATE_TITLE'); ?></label>
                    <input type="text" class="form-control" id="tmpl_title" name="title"
                           value="<?php echo htmlspecialchars($template->title, ENT_QUOTES, 'UTF-8'); ?>"
                           maxlength="100" required>
                </div>

                <div class="mb-3">
                    <label for="tmpl_body" class="form-label"><?php echo Text::_('COM_KWTSMS_TEMPLATE_BODY'); ?></label>
                    <textarea class="form-control" id="tmpl_body" name="body" rows="5"
                              maxlength="1120" required
                              dir="<?php echo $isArabic ? 'rtl' : 'ltr'; ?>"><?php echo htmlspecialchars($template->body, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <div class="kwtsms-char-counter" id="kwtsms-counter">0 / <?php echo $pageLimit; ?> chars (1 page)</div>
                </div>

                <?php if (!empty($placeholders)) : ?>
                <div class="mb-3">
                    <label class="form-label text-muted">Placeholders:</label>
                    <div>
                        <?php foreach (explode(',', $placeholders) as $ph) : ?>
                            <span class="badge bg-light text-dark border me-1"><?php echo htmlspecialchars(trim($ph), ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label"><?php echo Text::_('COM_KWTSMS_TEMPLATE_ENABLED'); ?></label>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="enabled" id="en_no" value="0" <?php echo !$template->enabled ? 'checked' : ''; ?>>
                        <label class="btn btn-outline-danger" for="en_no"><?php echo Text::_('JNO'); ?></label>
                        <input type="radio" class="btn-check" name="enabled" id="en_yes" value="1" <?php echo $template->enabled ? 'checked' : ''; ?>>
                        <label class="btn btn-outline-success" for="en_yes"><?php echo Text::_('JYES'); ?></label>
                    </div>
                </div>

            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-kwtsms"><?php echo Text::_('COM_KWTSMS_TEMPLATE_SAVE'); ?></button>
            <a href="<?php echo Route::_('index.php?option=com_kwtsms&view=templates', false); ?>" class="btn btn-outline-secondary">
                <?php echo Text::_('COM_KWTSMS_TEMPLATE_CANCEL'); ?>
            </a>
        </div>
    </form>
</div>

<script>
(function () {
    var textarea  = document.getElementById('tmpl_body');
    var counter   = document.getElementById('kwtsms-counter');
    var pageLimit = <?php echo $pageLimit; ?>;

    function updateCounter() {
        var len   = textarea.value.length;
        var pages = Math.ceil(len / pageLimit) || 1;
        counter.textContent = len + ' chars (' + pages + ' page' + (pages > 1 ? 's' : '') + ')';
        counter.className   = 'kwtsms-char-counter' + (len > pageLimit * 6 ? ' danger' : len > pageLimit * 4 ? ' warning' : '');
    }

    textarea.addEventListener('input', updateCounter);
    updateCounter();
})();
</script>
