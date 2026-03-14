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

HTMLHelper::_('stylesheet', 'com_kwtsms/css/kwtsms.css', [], true);

$levelColors = [
	'debug'   => 'secondary',
	'info'    => 'info',
	'warning' => 'warning',
	'error'   => 'danger',
];
?>

<?php include JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/layout/tabs.php'; ?>

<div class="container-fluid mt-3">

	<!-- Filter Bar -->
	<form method="get" action="index.php" class="row g-2 mb-3 align-items-end">
		<input type="hidden" name="option" value="com_kwtsms">
		<input type="hidden" name="view" value="logs">

		<div class="col-auto">
			<label class="form-label"><?php echo Text::_('COM_KWTSMS_LOG_LEVEL'); ?></label>
			<select class="form-select form-select-sm" name="filter_level">
				<option value=""><?php echo Text::_('JALL'); ?></option>
				<?php foreach (['debug', 'info', 'warning', 'error'] as $lvl) : ?>
					<option value="<?php echo $lvl; ?>" <?php echo $this->filters['level'] === $lvl ? 'selected' : ''; ?>>
						<?php echo htmlspecialchars(ucfirst($lvl), ENT_QUOTES, 'UTF-8'); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="col-auto">
			<label class="form-label"><?php echo Text::_('JSEARCH_FILTER'); ?></label>
			<input type="text" class="form-control form-control-sm" name="filter_search"
				   value="<?php echo htmlspecialchars($this->filters['search'], ENT_QUOTES, 'UTF-8'); ?>"
				   placeholder="<?php echo Text::_('COM_KWTSMS_LOG_MESSAGE'); ?>">
		</div>

		<div class="col-auto">
			<button type="submit" class="btn btn-sm btn-outline-secondary">
				<?php echo Text::_('COM_KWTSMS_LOG_FILTER_APPLY'); ?>
			</button>
		</div>
	</form>

	<!-- Action Buttons -->
	<div class="d-flex gap-2 mb-3 align-items-center">
		<form method="post" action="<?php echo Route::_('index.php?option=com_kwtsms&task=logs.clearLogs', false); ?>"
			  onsubmit="return confirm('<?php echo Text::_('COM_KWTSMS_MSG_CONFIRM_CLEAR'); ?>');">
			<?php echo HTMLHelper::_('form.token'); ?>
			<button type="submit" class="btn btn-sm btn-outline-danger"><?php echo Text::_('COM_KWTSMS_CLEAR_LOGS'); ?></button>
		</form>
		<form method="post" action="<?php echo Route::_('index.php?option=com_kwtsms&task=logs.exportCsv', false); ?>">
			<?php echo HTMLHelper::_('form.token'); ?>
			<input type="hidden" name="filter_level" value="<?php echo htmlspecialchars($this->filters['level'], ENT_QUOTES, 'UTF-8'); ?>">
			<input type="hidden" name="filter_search" value="<?php echo htmlspecialchars($this->filters['search'], ENT_QUOTES, 'UTF-8'); ?>">
			<input type="hidden" name="filter_from" value="<?php echo htmlspecialchars($this->filters['date_from'], ENT_QUOTES, 'UTF-8'); ?>">
			<input type="hidden" name="filter_to" value="<?php echo htmlspecialchars($this->filters['date_to'], ENT_QUOTES, 'UTF-8'); ?>">
			<button type="submit" class="btn btn-sm btn-outline-secondary"><?php echo Text::_('COM_KWTSMS_EXPORT_CSV'); ?></button>
		</form>
		<?php if ($this->total > 200) : ?>
			<small class="text-muted ms-auto">Showing 200 of <?php echo (int) $this->total; ?> entries</small>
		<?php elseif ($this->total > 0) : ?>
			<small class="text-muted ms-auto"><?php echo (int) $this->total; ?> entries</small>
		<?php endif; ?>
	</div>

	<!-- Logs Table -->
	<div class="table-responsive">
		<table class="table table-sm table-striped">
			<thead>
				<tr>
					<th width="140"><?php echo Text::_('COM_KWTSMS_LOG_DATE'); ?></th>
					<th width="90"><?php echo Text::_('COM_KWTSMS_LOG_LEVEL'); ?></th>
					<th width="120"><?php echo Text::_('COM_KWTSMS_LOG_CONTEXT'); ?></th>
					<th><?php echo Text::_('COM_KWTSMS_LOG_MESSAGE'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($this->logs)) : ?>
					<tr><td colspan="4" class="text-center text-muted py-3">No log entries found.</td></tr>
				<?php else : ?>
					<?php foreach ($this->logs as $log) : ?>
						<tr>
							<td class="text-nowrap small"><?php echo htmlspecialchars($log['created'], ENT_QUOTES, 'UTF-8'); ?></td>
							<td>
								<span class="badge bg-<?php echo $levelColors[$log['level']] ?? 'secondary'; ?>">
									<?php echo htmlspecialchars($log['level'], ENT_QUOTES, 'UTF-8'); ?>
								</span>
							</td>
							<td class="small text-muted"><?php echo htmlspecialchars($log['context'], ENT_QUOTES, 'UTF-8'); ?></td>
							<td>
								<?php echo htmlspecialchars($log['message'], ENT_QUOTES, 'UTF-8'); ?>
								<?php if (!empty($log['data'])) : ?>
									<details class="mt-1">
										<summary class="small text-muted">Data</summary>
										<pre class="small"><?php echo htmlspecialchars($log['data'], ENT_QUOTES, 'UTF-8'); ?></pre>
									</details>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

</div>
