<?php

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('stylesheet', 'com_kwtsms/css/kwtsms.css', [], true);

$vm = $this->integrations['virtuemart'];
?>

<?php echo $this->loadTemplate('../../layout/tabs'); ?>

<div class="container-fluid mt-3">
	<form method="post" action="<?php echo Route::_('index.php?option=com_kwtsms&task=integrations.save', false); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>

		<!-- VirtueMart Integration -->
		<div class="card mb-3">
			<div class="card-header kwtsms-card-header">
				<?php echo Text::_('COM_KWTSMS_INTEGRATION_VM'); ?>
			</div>
			<div class="card-body">

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label"><?php echo Text::_('COM_KWTSMS_INTEGRATION_ENABLED'); ?></label>
					<div class="col-sm-9">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" role="switch"
								   id="vm_enabled" name="integration_vm_enabled" value="1"
								   <?php echo $vm['enabled'] ? 'checked' : ''; ?>>
							<label class="form-check-label" for="vm_enabled">
								<?php echo $vm['enabled'] ? Text::_('JENABLED') : Text::_('JDISABLED'); ?>
							</label>
						</div>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label"><?php echo Text::_('COM_KWTSMS_EVENT_ORDER_NEW'); ?></label>
					<div class="col-sm-9">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="integration_vm_order_new" value="1"
								   id="vm_order_new" <?php echo $vm['events']['order_new'] ? 'checked' : ''; ?>>
							<label class="form-check-label" for="vm_order_new"><?php echo Text::_('COM_KWTSMS_EVENT_ORDER_NEW'); ?></label>
						</div>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label"><?php echo Text::_('COM_KWTSMS_EVENT_ORDER_STATUS'); ?></label>
					<div class="col-sm-9">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="integration_vm_order_status" value="1"
								   id="vm_order_status" <?php echo $vm['events']['order_status_update'] ? 'checked' : ''; ?>>
							<label class="form-check-label" for="vm_order_status"><?php echo Text::_('COM_KWTSMS_EVENT_ORDER_STATUS'); ?></label>
						</div>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label"><?php echo Text::_('COM_KWTSMS_RECIPIENT_CUSTOMER'); ?></label>
					<div class="col-sm-9">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="integration_vm_customer" value="1"
								   id="vm_customer" <?php echo $vm['recipients']['customer'] ? 'checked' : ''; ?>>
							<label class="form-check-label" for="vm_customer"><?php echo Text::_('COM_KWTSMS_RECIPIENT_CUSTOMER'); ?></label>
						</div>
					</div>
				</div>

				<div class="row mb-3">
					<label class="col-sm-3 col-form-label"><?php echo Text::_('COM_KWTSMS_RECIPIENT_ADMIN'); ?></label>
					<div class="col-sm-9">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="integration_vm_admin" value="1"
								   id="vm_admin" <?php echo $vm['recipients']['admin'] ? 'checked' : ''; ?>>
							<label class="form-check-label" for="vm_admin"><?php echo Text::_('COM_KWTSMS_RECIPIENT_ADMIN'); ?></label>
						</div>
					</div>
				</div>

			</div>
		</div>

		<!-- Coming Soon -->
		<div class="card mb-3 border-dashed">
			<div class="card-body text-muted text-center py-4">
				<?php echo Text::_('COM_KWTSMS_COMING_SOON'); ?>
			</div>
		</div>

		<button type="submit" class="btn btn-kwtsms"><?php echo Text::_('COM_KWTSMS_SAVE'); ?></button>
	</form>
</div>
