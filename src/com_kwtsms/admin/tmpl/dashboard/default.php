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

$gatewayOk = $this->status['gateway_enabled'] === '1' && $this->status['gateway_configured'] === '1';
?>

<?php include JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/layout/tabs.php'; ?>

<div class="container-fluid mt-3">

	<?php if ($this->status['test_mode'] === '1') : ?>
	<div class="alert alert-warning">
		<?php echo Text::_('COM_KWTSMS_TEST_MODE_ON'); ?>
	</div>
	<?php endif; ?>

	<!-- Status Cards Row 1: Gateway Enabled, Gateway Connected, Test Mode -->
	<div class="row g-3 mb-4">

		<div class="col-md-4 col-sm-6">
			<div class="card kwtsms-status-card h-100">
				<div class="card-body">
					<h6 class="card-subtitle text-muted mb-2"><?php echo Text::_('COM_KWTSMS_PLUGIN_ENABLED'); ?></h6>
					<div class="kwtsms-status-value">
						<?php if ($this->status['gateway_enabled'] === '1') : ?>
							<span class="badge bg-success"><?php echo Text::_('JENABLED'); ?></span>
						<?php else : ?>
							<span class="badge bg-danger"><?php echo Text::_('JDISABLED'); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-4 col-sm-6">
			<div class="card kwtsms-status-card h-100">
				<div class="card-body">
					<h6 class="card-subtitle text-muted mb-2"><?php echo Text::_('COM_KWTSMS_GATEWAY_CONNECTED'); ?></h6>
					<div class="kwtsms-status-value">
						<?php if ($gatewayOk) : ?>
							<span class="badge bg-success"><?php echo Text::_('JYES'); ?></span>
						<?php else : ?>
							<span class="badge bg-danger"><?php echo Text::_('JNO'); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-4 col-sm-6">
			<div class="card kwtsms-status-card h-100">
				<div class="card-body">
					<h6 class="card-subtitle text-muted mb-2"><?php echo Text::_('COM_KWTSMS_MODE'); ?></h6>
					<div class="kwtsms-status-value">
						<?php if ($this->status['test_mode'] === '1') : ?>
							<span class="badge bg-warning text-dark">Test</span>
						<?php else : ?>
							<span class="badge bg-success">Live</span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Row 2: Balance, Sender ID, Last Sync -->
		<div class="col-md-4 col-sm-6">
			<div class="card kwtsms-status-card h-100">
				<div class="card-body">
					<h6 class="card-subtitle text-muted mb-2"><?php echo Text::_('COM_KWTSMS_BALANCE'); ?></h6>
					<div class="kwtsms-status-value"><?php echo htmlspecialchars($this->status['balance'], ENT_QUOTES, 'UTF-8'); ?></div>
				</div>
			</div>
		</div>

		<div class="col-md-4 col-sm-6">
			<div class="card kwtsms-status-card h-100">
				<div class="card-body">
					<h6 class="card-subtitle text-muted mb-2"><?php echo Text::_('COM_KWTSMS_SENDER_ID'); ?></h6>
					<div class="kwtsms-status-value"><?php echo htmlspecialchars($this->status['sender_id'] ?: 'KWT-SMS', ENT_QUOTES, 'UTF-8'); ?></div>
				</div>
			</div>
		</div>

		<div class="col-md-4 col-sm-6">
			<div class="card kwtsms-status-card h-100">
				<div class="card-body">
					<h6 class="card-subtitle text-muted mb-2"><?php echo Text::_('COM_KWTSMS_LAST_SYNC'); ?></h6>
					<div class="kwtsms-status-value" style="font-size:1rem"><?php echo htmlspecialchars($this->status['last_sync'] ?: 'Never', ENT_QUOTES, 'UTF-8'); ?></div>
				</div>
			</div>
		</div>

	</div>

	<!-- Stats Row -->
	<div class="row g-3 mb-4">
		<div class="col-md-3 col-sm-6">
			<div class="card text-center">
				<div class="card-body">
					<div class="kwtsms-status-value"><?php echo (int) $this->stats['sent_today']; ?></div>
					<div class="card-subtitle text-muted"><?php echo Text::_('COM_KWTSMS_SENT_TODAY'); ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-3 col-sm-6">
			<div class="card text-center">
				<div class="card-body">
					<div class="kwtsms-status-value"><?php echo (int) $this->stats['sent_week']; ?></div>
					<div class="card-subtitle text-muted"><?php echo Text::_('COM_KWTSMS_SENT_WEEK'); ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-3 col-sm-6">
			<div class="card text-center">
				<div class="card-body">
					<div class="kwtsms-status-value"><?php echo (int) $this->stats['sent_month']; ?></div>
					<div class="card-subtitle text-muted"><?php echo Text::_('COM_KWTSMS_SENT_MONTH'); ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-3 col-sm-6">
			<div class="card text-center">
				<div class="card-body">
					<div class="kwtsms-status-value text-danger"><?php echo (int) $this->stats['total_errors']; ?></div>
					<div class="card-subtitle text-muted"><?php echo Text::_('COM_KWTSMS_TOTAL_ERRORS'); ?></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Action Buttons -->
	<div class="d-flex gap-2 mb-4">
		<form method="post" action="<?php echo Route::_('index.php?option=com_kwtsms&task=dashboard.syncNow', false); ?>">
			<?php echo HTMLHelper::_('form.token'); ?>
			<button type="submit" class="btn btn-kwtsms">
				<?php echo Text::_('COM_KWTSMS_SYNC_NOW'); ?>
			</button>
		</form>
	</div>

	<!-- Send Test SMS -->
	<?php if ($gatewayOk) : ?>
	<div class="card mb-3">
		<div class="card-header kwtsms-card-header"><?php echo Text::_('COM_KWTSMS_TEST_SMS'); ?></div>
		<div class="card-body">
			<form method="post" action="<?php echo Route::_('index.php?option=com_kwtsms&task=dashboard.sendTest', false); ?>">
				<?php echo HTMLHelper::_('form.token'); ?>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="test_phone"><?php echo Text::_('COM_KWTSMS_TEST_PHONE'); ?></label>
					<div class="col-sm-4">
						<input type="text" class="form-control" id="test_phone" name="test_phone"
							   placeholder="96598765432" maxlength="15" pattern="\d{7,15}"
							   required>
						<div class="form-text"><?php echo Text::_('COM_KWTSMS_TEST_PHONE_HINT'); ?></div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="test_message"><?php echo Text::_('COM_KWTSMS_TEST_MESSAGE'); ?></label>
					<div class="col-sm-6">
						<input type="text" class="form-control" id="test_message" name="test_message"
							   value="Test SMS from kwtSMS Joomla extension" maxlength="160"
							   required>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-9 offset-sm-3">
						<button type="submit" class="btn btn-outline-success">
							<?php echo Text::_('COM_KWTSMS_TEST_SMS'); ?>
						</button>
						<?php if ($this->status['test_mode'] === '1') : ?>
							<span class="ms-2 text-muted"><?php echo Text::_('COM_KWTSMS_TEST_SMS_NOTE'); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</form>
		</div>
	</div>
	<?php endif; ?>

</div>
