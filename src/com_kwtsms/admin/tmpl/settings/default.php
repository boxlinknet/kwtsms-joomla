<?php

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

HTMLHelper::_('stylesheet', 'com_kwtsms/css/kwtsms.css', [], true);

$senderIds = $this->settings['senderids'] ?? [];
?>

<?php include JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/layout/tabs.php'; ?>

<div class="container-fluid mt-3">
	<form method="post" action="<?php echo Route::_('index.php?option=com_kwtsms&task=settings.save', false); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>

		<!-- General Settings -->
		<div class="card mb-3">
			<div class="card-header kwtsms-card-header"><?php echo Text::_('COM_KWTSMS_SETTINGS'); ?></div>
			<div class="card-body">
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label"><?php echo Text::_('COM_KWTSMS_GATEWAY_ENABLED'); ?></label>
					<div class="col-sm-9">
						<div class="btn-group" role="group">
							<input type="radio" class="btn-check" name="gateway_enabled" id="gw_off" value="0" <?php echo $this->settings['gateway_enabled'] !== '1' ? 'checked' : ''; ?>>
							<label class="btn btn-outline-secondary" for="gw_off"><?php echo Text::_('JNO'); ?></label>
							<input type="radio" class="btn-check" name="gateway_enabled" id="gw_on" value="1" <?php echo $this->settings['gateway_enabled'] === '1' ? 'checked' : ''; ?>>
							<label class="btn btn-outline-success" for="gw_on"><?php echo Text::_('JYES'); ?></label>
						</div>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label"><?php echo Text::_('COM_KWTSMS_TEST_MODE'); ?></label>
					<div class="col-sm-9">
						<div class="btn-group" role="group">
							<input type="radio" class="btn-check" name="test_mode" id="tm_on" value="1" <?php echo $this->settings['test_mode'] === '1' ? 'checked' : ''; ?>>
							<label class="btn btn-outline-warning" for="tm_on"><?php echo Text::_('JYES'); ?></label>
							<input type="radio" class="btn-check" name="test_mode" id="tm_off" value="0" <?php echo $this->settings['test_mode'] !== '1' ? 'checked' : ''; ?>>
							<label class="btn btn-outline-secondary" for="tm_off"><?php echo Text::_('JNO'); ?></label>
						</div>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label"><?php echo Text::_('COM_KWTSMS_DEBUG_LOGGING'); ?></label>
					<div class="col-sm-9">
						<div class="btn-group" role="group">
							<input type="radio" class="btn-check" name="debug_logging" id="dl_off" value="0" <?php echo $this->settings['debug_logging'] !== '1' ? 'checked' : ''; ?>>
							<label class="btn btn-outline-secondary" for="dl_off"><?php echo Text::_('JNO'); ?></label>
							<input type="radio" class="btn-check" name="debug_logging" id="dl_on" value="1" <?php echo $this->settings['debug_logging'] === '1' ? 'checked' : ''; ?>>
							<label class="btn btn-outline-primary" for="dl_on"><?php echo Text::_('JYES'); ?></label>
						</div>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="log_retention_days"><?php echo Text::_('COM_KWTSMS_LOG_RETENTION'); ?></label>
					<div class="col-sm-3">
						<input type="number" class="form-control" id="log_retention_days" name="log_retention_days"
							   value="<?php echo htmlspecialchars($this->settings['log_retention_days'], ENT_QUOTES, 'UTF-8'); ?>"
							   min="1" max="365">
					</div>
				</div>
			</div>
		</div>

		<!-- Gateway Credentials -->
		<div class="card mb-3">
			<div class="card-header kwtsms-card-header"><?php echo Text::_('COM_KWTSMS_GATEWAY_CONNECTED'); ?></div>
			<div class="card-body">
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="api_username"><?php echo Text::_('COM_KWTSMS_API_USERNAME'); ?></label>
					<div class="col-sm-6">
						<input type="text" class="form-control" id="api_username" name="api_username"
							   value="<?php echo htmlspecialchars($this->settings['api_username'], ENT_QUOTES, 'UTF-8'); ?>"
							   autocomplete="username">
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="api_password"><?php echo Text::_('COM_KWTSMS_API_PASSWORD'); ?></label>
					<div class="col-sm-6">
						<input type="password" class="form-control" id="api_password" name="api_password"
							   placeholder="<?php echo $this->settings['api_password'] === '***' ? '(unchanged)' : ''; ?>"
							   autocomplete="current-password">
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="sender_id"><?php echo Text::_('COM_KWTSMS_SENDER_ID'); ?></label>
					<div class="col-sm-6">
						<?php if (!empty($senderIds)) : ?>
							<select class="form-select" id="sender_id" name="sender_id">
								<?php foreach ($senderIds as $sid) : ?>
									<option value="<?php echo htmlspecialchars($sid, ENT_QUOTES, 'UTF-8'); ?>"
										<?php echo $this->settings['sender_id'] === $sid ? 'selected' : ''; ?>>
										<?php echo htmlspecialchars($sid, ENT_QUOTES, 'UTF-8'); ?>
									</option>
								<?php endforeach; ?>
							</select>
						<?php else : ?>
							<input type="text" class="form-control" id="sender_id" name="sender_id"
								   value="<?php echo htmlspecialchars($this->settings['sender_id'], ENT_QUOTES, 'UTF-8'); ?>"
								   placeholder="KWT-SMS" maxlength="11">
							<div class="form-text"><?php echo Text::_('COM_KWTSMS_GATEWAY_NOT_CONFIGURED'); ?></div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Test Connection -->
				<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<button type="button" class="btn btn-outline-primary" id="kwtsms-test-btn">
							<?php echo Text::_('COM_KWTSMS_TEST_CONNECTION'); ?>
						</button>
						<span id="kwtsms-test-result" class="ms-3"></span>
					</div>
				</div>
			</div>
		</div>

		<button type="submit" class="btn btn-kwtsms">
			<?php echo Text::_('COM_KWTSMS_SAVE'); ?>
		</button>
	</form>
</div>

<script>
document.getElementById('kwtsms-test-btn').addEventListener('click', function () {
	var resultEl = document.getElementById('kwtsms-test-result');
	resultEl.textContent = '...';

	var token = document.querySelector('input[name="<?php echo Session::getFormToken(); ?>"]');
	var tokenValue = token ? token.value : '';

	var username = encodeURIComponent(document.getElementById('api_username').value);
	var password = encodeURIComponent(document.getElementById('api_password').value);

	fetch('index.php?option=com_kwtsms&task=settings.testConnection&<?php echo Session::getFormToken(); ?>=1&api_username=' + username + '&api_password=' + password)
		.then(function (r) { return r.json(); })
		.then(function (data) {
			if (data.result === 'OK') {
				resultEl.textContent = 'Connected. Balance: ' + data.available;
				resultEl.className = 'ms-3 text-success';
			} else {
				resultEl.textContent = 'Error: ' + (data.description || data.code || 'Unknown');
				resultEl.className = 'ms-3 text-danger';
			}
		})
		.catch(function () {
			resultEl.textContent = 'Request failed';
			resultEl.className = 'ms-3 text-danger';
		});
});
</script>
