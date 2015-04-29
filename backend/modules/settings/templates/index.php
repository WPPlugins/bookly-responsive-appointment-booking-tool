<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-title"><?php _e('Settings') ?></div>
<div style="min-width: 800px;">
	<div class="ab-left-bar">
        <div id="ab_settings_general" class="ab-left-tab <?php echo ( ! isset( $_GET[ 'type' ] ) || $_GET[ 'type' ] == '_general' ) ? 'ab-active' : ''  ?>"><?php _e( 'General','ab' ) ?></div>
        <div id="ab_settings_company" class="ab-left-tab <?php echo isset( $_GET['type'] ) && $_GET['type'] == '_company' ? 'ab-active' : ''  ?>"><?php _e( 'Company','ab' ) ?></div>
		<div id="ab_settings_payments" class="ab-left-tab <?php echo isset( $_GET['type'] ) && $_GET['type'] == '_payments' ? 'ab-active' : ''  ?>"><?php _e( 'Payments','ab' ) ?></div>
		<div id="ab_settings_hours" class="ab-left-tab <?php echo isset( $_GET['type'] ) && $_GET['type'] == '_hours' ? 'ab-active' : ''  ?>"><?php _e( 'Business hours','ab' ) ?></div>
		<div id="ab_settings_holidays" class="ab-left-tab <?php echo isset( $_GET['type'] ) && $_GET['type'] == '_holidays' ? 'ab-active' : ''  ?>"><?php _e( 'Holidays','ab' ) ?></div>
		<div id="ab_settings_purchase_code" class="ab-left-tab <?php echo isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] == '_purchase_code' ? 'ab-active' : ''  ?>"><?php _e( 'Purchase Code','ab' ) ?></div>
	</div>
	<div class="ab-right-content" id="content_wrapper">
        <div id="general-form" class="<?php echo ( ! isset( $_GET[ 'type' ] ) || $_GET[ 'type' ] == '_general' ) ? '' : 'hidden' ?> ab-staff-tab-content">
            <?php include '_generalForm.php' ?>
        </div>
		<div id="company-form" class="<?php echo ( isset( $_GET['type'] ) && $_GET['type'] == '_company' ) ? '' : 'hidden' ?>">
			<?php include '_companyForm.php' ?>
		</div>
		<div id="payments-form" class="<?php echo ( isset( $_GET['type'] ) && $_GET['type'] == '_payments' ) ? '' : 'hidden' ?>">
			<?php include '_paymentsForm.php' ?>
		</div>
		<div id="hours-form" class="<?php echo ( isset( $_GET['type'] ) && $_GET['type'] == '_hours' ) ? '' : 'hidden' ?>">
			<?php include '_hoursForm.php' ?>
		</div>
		<div id="holidays-form" class="<?php echo ( isset( $_GET['type'] ) && $_GET['type'] == '_holidays' ) ? '' : 'hidden' ?> ab-staff-tab-content">
			<?php include '_holidaysForm.php' ?>
		</div>
		<div id="purchase-code-form" class="<?php echo ( isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] == '_purchase_code' ) ? '' : 'hidden' ?> ab-staff-tab-content">
			<?php include '_purchaseCodeForm.php' ?>
		</div>
	</div>
</div>