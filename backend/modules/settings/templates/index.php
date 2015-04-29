<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-title"><?php _e('Settings', 'ab') ?></div>
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

<div class="modal fade" id="lite_notice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Notice', 'ab') ?></h4>
            </div>
            <div class="modal-body">
                <?php _e('This function is disabled in the lite version of Bookly. If you find the plugin useful for your business please consider buying a licence for the full version. It costs just $38 and for this money you will get many useful functions, lifetime free update and excellent support! More information can be found here', 'ab'); ?>: <a href="http://bookly.ladela.com" target="_blank">http://bookly.ladela.com</a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'ab') ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->