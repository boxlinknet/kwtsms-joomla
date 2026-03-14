<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('stylesheet', 'com_kwtsms/css/kwtsms.css', [], true);
?>

<?php include JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/layout/tabs.php'; ?>

<div class="container-fluid mt-3">

	<div class="row g-3">

		<!-- Quick Start -->
		<div class="col-lg-6">
			<div class="card h-100">
				<div class="card-header kwtsms-card-header"><?php echo Text::_('COM_KWTSMS_HELP_QUICK_START'); ?></div>
				<div class="card-body">
					<ol>
						<li>Register an account at <a href="https://www.kwtsms.com" target="_blank" rel="noopener noreferrer">kwtsms.com</a>.</li>
						<li>Go to Account &gt; API and enable API access.</li>
						<li>Copy your API username and password.</li>
						<li>Enter credentials in the <strong>Settings</strong> tab and click <em>Test Connection</em>.</li>
						<li>Register a private Sender ID (contact kwtSMS support).</li>
						<li>Select your Sender ID in Settings, enable VirtueMart events in <strong>Integrations</strong>.</li>
						<li>Enable the gateway. Disable Test Mode only when ready for production.</li>
					</ol>
				</div>
			</div>
		</div>

		<!-- SenderID Guide -->
		<div class="col-lg-6">
			<div class="card h-100">
				<div class="card-header kwtsms-card-header">SenderID Guide</div>
				<div class="card-body">
					<p>There are two types of Sender ID:</p>
					<table class="table table-sm table-bordered">
						<thead>
							<tr>
								<th></th>
								<th>Promotional</th>
								<th>Transactional</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Use for</td>
								<td>Bulk/marketing</td>
								<td>OTP, order notifications</td>
							</tr>
							<tr>
								<td>DND numbers</td>
								<td>Blocked</td>
								<td>Bypassed</td>
							</tr>
							<tr>
								<td>Cost</td>
								<td>10 KD</td>
								<td>15 KD</td>
							</tr>
						</tbody>
					</table>
					<p class="text-warning"><strong>Note:</strong> <code>KWT-SMS</code> is for testing only. Never use it in production.</p>
				</div>
			</div>
		</div>

		<!-- Troubleshooting -->
		<div class="col-lg-6">
			<div class="card h-100">
				<div class="card-header kwtsms-card-header">Troubleshooting</div>
				<div class="card-body">
					<dl>
						<dt>SMS not arriving</dt>
						<dd>Check kwtSMS account queue/archive. Ensure Test Mode is OFF. Check Sender ID type (use Transactional for order notifications).</dd>

						<dt>ERR003: Wrong username/password</dt>
						<dd>Use API credentials (not your mobile number). Special characters in password? Ensure credentials are saved via the Settings form (POST only).</dd>

						<dt>ERR006: No valid numbers</dt>
						<dd>Phone number format issue. Check normalize() output. Phone must be digits only in international format.</dd>

						<dt>ERR013: Queue full</dt>
						<dd>kwtSMS queue limit reached (1000 messages). Wait for queue to clear. Check kwtSMS dashboard.</dd>

						<dt>Messages stuck in queue</dt>
						<dd>Possible causes: emoji in message, HTML tags, bad language filter. Check Logs tab. Delete from kwtSMS queue to recover credits.</dd>
					</dl>
				</div>
			</div>
		</div>

		<!-- Support and Version -->
		<div class="col-lg-6">
			<div class="card h-100">
				<div class="card-header kwtsms-card-header"><?php echo Text::_('COM_KWTSMS_HELP_SUPPORT'); ?></div>
				<div class="card-body">
					<ul class="list-unstyled">
						<li><strong>Website:</strong> <a href="https://www.kwtsms.com" target="_blank" rel="noopener noreferrer">kwtsms.com</a></li>
						<li><strong>Support:</strong> <a href="https://www.kwtsms.com/support.html" target="_blank" rel="noopener noreferrer">kwtsms.com/support.html</a></li>
						<li><strong>API Docs:</strong> <a href="https://www.kwtsms.com/doc/KwtSMS.com_API_Documentation_v41.pdf" target="_blank" rel="noopener noreferrer">API Documentation v4.1</a></li>
						<li><strong>Security Issues:</strong> <a href="mailto:support@kwtsms.com">support@kwtsms.com</a></li>
					</ul>

					<?php if ($this->isAdmin) : ?>
				<hr>

					<table class="table table-sm">
						<tbody>
							<tr>
								<th><?php echo Text::_('COM_KWTSMS_HELP_VERSION'); ?></th>
								<td><?php echo htmlspecialchars($this->extensionVersion, ENT_QUOTES, 'UTF-8'); ?></td>
							</tr>
							<tr>
								<th>Joomla</th>
								<td><?php echo htmlspecialchars($this->joomlaVersion, ENT_QUOTES, 'UTF-8'); ?></td>
							</tr>
							<tr>
								<th>PHP</th>
								<td><?php echo htmlspecialchars($this->phpVersion, ENT_QUOTES, 'UTF-8'); ?></td>
							</tr>
						</tbody>
					</table>
				<?php endif; ?>
				</div>
			</div>
		</div>

	</div>

</div>
