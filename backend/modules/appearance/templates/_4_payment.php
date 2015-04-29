<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!-- ab-booking-info-third-preview -->
<div class="ab-booking-form" style="overflow: hidden">
    <!-- Progress Tracker-->
    <?php $step = 4; include '_progress_tracker.php'; ?>
    <div style="margin-bottom: 15px!important;" class="ab-row-fluid">
      <span data-inputclass="input-xxlarge" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_fourth_step' ) ) ?>" data-link-class="ab-text-info-fourth" class="ab-text-info-fourth-preview ab-row-fluid ab_editable" id="ab-text-info-fourth" data-type="textarea" data-pk="1"><?php echo esc_html( get_option( 'ab_appearance_text_info_fourth_step' ) ) ?></span>
    </div>
    <!-- payment -->
    <div class="ab-payment">
        <!-- label -->
        <div class="ab-row-fluid">
            <label>
                <input type="radio" name="payment" class="ab-local-payment" checked="checked" value="local"/>
                <?php _e( 'I will pay locally', 'ab' ) ?>
            </label>
        </div>
        <div class="ab-row-fluid">
            <label>
                <input type="radio" name="payment" class="ab-paypal-payment" value="paypal"/>
                <?php _e( 'I will pay now with PayPal', 'ab' ) ?>
            </label>
        </div>
        <!-- buttons -->
        <div class="ab-local-pay-button ab-row-fluid ab-nav-steps last-row">
            <button class="ab-left ab-to-third-step ladda-button orange zoom-in" style="margin-right: 10px;">
                <span><?php _e( 'Back', 'ab' ) ?></span>
            </button>
            <button class="ab-right ab-final-step ladda-button orange zoom-in">
                <span><?php _e( 'Next', 'ab' ) ?></span>
            </button>
        </div>
    </div>
</div>

<!-- fourth step options -->
<div class="ab-fourth-step-options">
    <!-- booking-info -->
    <div class="ab-booking-details">
    </div>
</div>