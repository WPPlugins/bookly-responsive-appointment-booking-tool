<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-progress-tracker<?php if ( $this->payment_disabled ) echo ' ab-progress-tracker-four-steps'?>">
    <?php
        // Show Progress Tracker if enabled in settings
        if ( intval( get_option( 'ab_appearance_show_progress_tracker' ) == 1 ) ) {
            _e( $progress_tracker, 'ab' ) ;
        }
    ?>
</div>
<div style="margin-bottom: 15px!important;" class="ab-row-fluid"><?php echo $info_text ?>
  <?php
    $local_payment = get_option( 'ab_settings_pay_locally' ) == 1;
  ?>
</div>
<?php if ( $local_payment ) : ?>
    <div class="ab-row-fluid ab-list">
        <label>
            <input type="radio" class="ab-local-payment" checked="checked" name="payment-method-<?php echo $form_id ?>" value="local"/>
            <?php _e( 'I will pay locally', 'ab' ) ?>
        </label>
    </div>
<?php endif ?>
<?php if ( $local_payment ) : ?>
    <div class="ab-local-pay-button ab-row-fluid ab-nav-steps">
	    <button class="ab-left ab-to-third-step ladda-button orange zoom-in" style="margin-right: 10px;">
		    <span class="ab_label"><?php _e( 'Back', 'ab' ) ?></span><span class="spinner"></span>
	    </button>
	    <button class="ab-right ab-final-step ladda-button orange zoom-in">
		    <span class="ab_label"><?php _e( 'Next', 'ab' ) ?></span><span class="spinner"></span>
	    </button>
    </div>
<?php endif ?>