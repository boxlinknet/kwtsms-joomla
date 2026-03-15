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
use Joomla\CMS\Session\Session;
use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;

HTMLHelper::_('stylesheet', 'media/com_kwtsms/css/kwtsms.css');

$senderIds  = $this->settings['senderids'] ?? [];
$coverage   = $this->settings['coverage'] ?? [];
$isLoggedIn = $this->settings['gateway_configured'] === '1' && $this->settings['api_username'] !== '';
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
					<label class="col-sm-3 col-form-label"><?php echo Text::_('COM_KWTSMS_PLUGIN_ENABLED'); ?></label>
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

		<?php if (!$isLoggedIn) : ?>
		<!-- PRE-AUTH: Gateway Login -->
		<div class="card mb-3">
			<div class="card-header kwtsms-card-header"><?php echo Text::_('COM_KWTSMS_GATEWAY_LOGIN'); ?></div>
			<div class="card-body">
				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="api_username"><?php echo Text::_('COM_KWTSMS_API_USERNAME'); ?></label>
					<div class="col-sm-6">
						<input type="text" class="form-control" id="api_username" name="api_username" autocomplete="username">
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="api_password"><?php echo Text::_('COM_KWTSMS_API_PASSWORD'); ?></label>
					<div class="col-sm-6">
						<input type="password" class="form-control" id="api_password" name="api_password" autocomplete="current-password">
					</div>
				</div>

				<div class="row mb-3">
					<div class="col-sm-9 offset-sm-3">
						<button type="button" class="btn btn-kwtsms" id="kwtsms-login-btn">
							<?php echo Text::_('COM_KWTSMS_GATEWAY_LOGIN_BTN'); ?>
						</button>
						<span id="kwtsms-login-result" class="ms-3"></span>
					</div>
				</div>

				<div class="form-text text-muted">
					<?php echo Text::_('COM_KWTSMS_GATEWAY_LOGIN_HINT'); ?>
				</div>
			</div>
		</div>

		<?php else : ?>
		<!-- POST-AUTH: Gateway Configuration -->
		<div class="card mb-3">
			<div class="card-header kwtsms-card-header d-flex justify-content-between align-items-center">
				<span><?php echo Text::_('COM_KWTSMS_GATEWAY_CONNECTED'); ?></span>
				<div class="d-flex gap-2">
					<a href="<?php echo Route::_('index.php?option=com_kwtsms&task=settings.reload&' . Session::getFormToken() . '=1', false); ?>"
					   class="btn btn-sm btn-outline-primary"><?php echo Text::_('COM_KWTSMS_RELOAD'); ?></a>
					<a href="<?php echo Route::_('index.php?option=com_kwtsms&task=settings.logout&' . Session::getFormToken() . '=1', false); ?>"
					   class="btn btn-sm btn-outline-danger"
					   onclick="return confirm('<?php echo Text::_('COM_KWTSMS_LOGOUT_CONFIRM'); ?>');"><?php echo Text::_('COM_KWTSMS_LOGOUT'); ?></a>
				</div>
			</div>
			<div class="card-body">
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
						<?php endif; ?>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="default_country"><?php echo Text::_('COM_KWTSMS_DEFAULT_COUNTRY'); ?></label>
					<div class="col-sm-6">
						<?php if (!empty($coverage)) : ?>
							<select class="form-select" id="default_country" name="default_country">
								<?php foreach ($coverage as $prefix) : ?>
									<option value="<?php echo htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8'); ?>"
										<?php echo $this->settings['default_country'] === $prefix ? 'selected' : ''; ?>>
										+<?php echo htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars(KwtSmsApiClient::countryName($prefix), ENT_QUOTES, 'UTF-8'); ?>)
									</option>
								<?php endforeach; ?>
							</select>
						<?php else : ?>
							<input type="text" class="form-control" id="default_country" name="default_country"
								   value="<?php echo htmlspecialchars($this->settings['default_country'], ENT_QUOTES, 'UTF-8'); ?>"
								   placeholder="965" maxlength="4">
						<?php endif; ?>
						<div class="form-text"><?php echo Text::_('COM_KWTSMS_DEFAULT_COUNTRY_HINT'); ?></div>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label" for="admin_phone"><?php echo Text::_('COM_KWTSMS_ADMIN_PHONE'); ?></label>
					<div class="col-sm-6">
						<input type="text" class="form-control" id="admin_phone" name="admin_phone"
							   value="<?php echo htmlspecialchars($this->settings['admin_phone'], ENT_QUOTES, 'UTF-8'); ?>"
							   placeholder="96598765432" maxlength="15" pattern="\d{7,15}">
						<div class="form-text"><?php echo Text::_('COM_KWTSMS_ADMIN_PHONE_HINT'); ?></div>
					</div>
				</div>
			</div>
		</div>

		<!-- Active Coverage -->
		<?php if (!empty($coverage)) : ?>
		<div class="card mb-3">
			<div class="card-header kwtsms-card-header d-flex justify-content-between align-items-center">
				<span><?php echo Text::_('COM_KWTSMS_COVERAGE'); ?> (<?php echo count($coverage); ?>)</span>
				<a href="https://www.kwtsms.com/login/" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
					<?php echo Text::_('COM_KWTSMS_COVERAGE_ADD'); ?>
				</a>
			</div>
			<div class="card-body">
				<div class="d-flex flex-wrap gap-2">
					<?php foreach ($coverage as $prefix) : ?>
						<span class="badge bg-light text-dark border">
							+<?php echo htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8'); ?>
							<?php echo htmlspecialchars(KwtSmsApiClient::countryName($prefix), ENT_QUOTES, 'UTF-8'); ?>
						</span>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php endif; ?>

		<button type="submit" class="btn btn-kwtsms">
			<?php echo Text::_('COM_KWTSMS_SAVE'); ?>
		</button>
	</form>
</div>

<?php if (!$isLoggedIn) : ?>
<script>
document.getElementById('kwtsms-login-btn').addEventListener('click', function () {
	var resultEl = document.getElementById('kwtsms-login-result');
	resultEl.textContent = '...';
	resultEl.className = 'ms-3';

	var formData = new URLSearchParams();
	formData.append('<?php echo Session::getFormToken(); ?>', '1');
	formData.append('api_username', document.getElementById('api_username').value);
	formData.append('api_password', document.getElementById('api_password').value);

	fetch('index.php?option=com_kwtsms&task=settings.testConnection', {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: formData
	})
		.then(function (r) { return r.json(); })
		.then(function (data) {
			if (data.result === 'OK') {
				resultEl.textContent = 'Connected. Balance: ' + data.available;
				resultEl.className = 'ms-3 text-success';
				setTimeout(function () { window.location.reload(); }, 1500);
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
<?php endif; ?>
