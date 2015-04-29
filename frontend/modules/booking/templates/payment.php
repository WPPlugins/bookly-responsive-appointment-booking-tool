<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @var AB_UserBookingData $userData
 * @var string $progress_tracker
 * @var string $info_text
 * @var string $info_text_coupon
 */


// Show Progress Tracker if enabled in settings
if ( get_option( 'ab_appearance_show_progress_tracker' ) == 1 ) {
    echo $progress_tracker;
}

$_local = get_option( 'ab_settings_pay_locally' ) == 1;
?>

<div style="margin-bottom: 15px!important;" class="ab-row-fluid"><?php echo $info_text ?></div>
<?php if ($_local) : ?>
    <div class="ab-row-fluid ab-list">
        <label>
            <input type="radio" class="ab-local-payment" checked="checked" name="payment-method-<?php echo $form_id ?>" value="local"/>
            <?php _e( 'I will pay locally', 'ab' ) ?>
        </label>
    </div>
    <div class="ab-local-pay-button ab-row-fluid ab-nav-steps">
        <button class="ab-left ab-to-third-step ab-btn ladda-button orange zoom-in" style="margin-right: 10px;">
            <span class="ab_label"><?php _e( 'Back', 'ab' ) ?></span><span class="spinner"></span>
        </button>
        <button class="ab-right ab-final-step ab-btn ladda-button orange zoom-in">
            <span class="ab_label"><?php _e( 'Next', 'ab' ) ?></span><span class="spinner"></span>
        </button>
    </div>
<?php endif ?>