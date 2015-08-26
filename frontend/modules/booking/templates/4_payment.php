<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    /**
     * @var AB_PayPal $paypal
     * @var AB_UserBookingData $userData
     * @var string $progress_tracker
     * @var string $info_text
     * @var string $info_text_coupon
     * @var string $paypal_error_msg
     */

    $_local        = get_option( 'ab_settings_pay_locally' ) == 1;
    $_paypal       = get_option( 'ab_paypal_type' ) != 'disabled';
    $_authorizenet = get_option( 'ab_authorizenet_type' ) != 'disabled';
    $_stripe       = get_option( 'ab_stripe' ) == 1;
    echo $progress_tracker;
?>

<?php if ( get_option( 'ab_settings_coupons' ) ) : ?>
    <div style="margin-bottom: 15px!important;" class="ab-row-fluid ab-info-text-coupon"><?php _e( $info_text_coupon, 'bookly' ) ?></div>
    <div class="ab-row-fluid ab-list" style="overflow: visible!important;">
        <div class="ab-formGroup ab-full ab-lastGroup">
            <span style="display: inline-block;"><?php echo AB_Utils::getTranslatedOption( 'ab_appearance_text_label_coupon' ) ?></span>
            <div class="ab-formField" style="display: inline-block; white-space: nowrap;">
                <input class="ab-formElement ab-user-coupon" name="ab_coupon" maxlength="100" type="text" value="<?php echo esc_attr( $userData->get( 'coupon' ) ) ?>" />
                <button class="ab-btn ladda-button btn-apply-coupon apply-coupon" data-style="zoom-in" data-spinner-size="40">
                    <span class="ab-label"><?php _e( 'Apply', 'bookly' ) ?></span><span class="spinner"></span>
                </button>
            </div>
            <div class="ab-label-error ab-bold ab-coupon-error"></div>
        </div>
    </div>
<?php endif ?>

<div class="ab-payment-nav">
    <div style="margin-bottom: 15px!important;" class="ab-row-fluid"><?php _e( $info_text, 'bookly' ) ?></div>
    <?php if ( $_local ) : ?>
        <div class="ab-row-fluid ab-list">
            <label>
                <input type="radio" class="ab-local-payment" checked="checked" name="payment-method-<?php echo $form_id ?>" value="local"/>
                <?php echo AB_Utils::getTranslatedOption( 'ab_appearance_text_label_pay_locally' ) ?>
            </label>
        </div>
    <?php endif ?>

    <?php if ( $_paypal ) : ?>
        <div class="ab-row-fluid ab-list">
            <label>
                <input type="radio" class="ab-paypal-payment" <?php checked( !$_local ) ?> name="payment-method-<?php echo $form_id ?>" value="paypal"/>
                <?php _e( 'I will pay now with PayPal', 'bookly' ) ?>
                <img src="<?php echo plugins_url( 'frontend/resources/images/paypal.png', AB_PATH . '/main.php' ) ?>" style="margin-left: 10px;" alt="paypal" />
                <input id="tmp_form_id" type="hidden" value="<?php echo $form_id ? $form_id : '' ?>" />
            </label>
            <?php if ( $paypal_status && $paypal_status[ 'status' ] == 'error' ): ?>
                <div class="ab-label-error ab-bold" style="padding-top: 5px;">* <?php echo $paypal_status[ 'error' ] ?></div>
            <?php endif ?>
        </div>
    <?php endif ?>

    <?php if ( $_authorizenet ) : ?>
        <div class="ab-row-fluid ab-list">
            <label>
                <input type="radio" class="ab-authorizenet-payment" <?php checked( !$_local && !$_paypal ) ?> name="payment-method-<?php echo $form_id ?>" value="authorizenet"/>
                <?php _e( 'I will pay now with Credit Card', 'bookly' ) ?>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" style="margin-left: 10px;" alt="cards" />
                <input id="tmp_form_id" type="hidden" value="<?php echo $form_id ? $form_id : '' ?>" />
            </label>
            <form class="ab-third-step ab-authorizenet" style="<?php if ( $_local || $_paypal ) echo "display: none;"; ?> margin-top: 15px;">
                <?php include "_card_payment.php" ?>
            </form>
        </div>
    <?php endif ?>

    <?php if ( $_stripe ) : ?>
        <div class="ab-row-fluid ab-list ab-last">
            <label>
                <input type="radio" class="ab-stripe-payment" <?php checked( !$_local && !$_paypal && !$_authorizenet) ?> name="payment-method-<?php echo $form_id ?>" value="stripe"/>
                <?php _e( 'I will pay now with Credit Card', 'bookly' ) ?>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" style="margin-left: 10px;" alt="cards" />
                <input id="tmp_form_id" type="hidden" value="<?php echo $form_id ? $form_id : '' ?>" />
            </label>
            <form class="ab-third-step ab-stripe" style="<?php if ( $_local || $_paypal || $_authorizenet ) echo "display: none;"; ?> margin-top: 15px;">
                <?php include "_card_payment.php" ?>
            </form>
        </div>
    <?php endif ?>

    <div class="ab-row-fluid ab-list" style="display: none">
        <input type="radio" class="ab-coupon-free" name="payment-method-<?php echo $form_id ?>" value="coupon" />
    </div>
</div>

<?php if ( $_local ) : ?>
    <div class="ab-local-pay-button ab-row-fluid ab-nav-steps">
        <button class="ab-left ab-to-third-step ab-btn ladda-button" data-style="zoom-in" style="margin-right: 10px;" data-spinner-size="40">
            <span class="ladda-label"><?php _e( 'Back', 'bookly' ) ?></span>
        </button>
        <button class="ab-right ab-final-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php _e( 'Next', 'bookly' ) ?></span>
        </button>
    </div>
<?php endif ?>

<?php if ( $_paypal ) : ?>
    <div class="ab-paypal-payment-button ab-row-fluid ab-nav-steps" <?php if ( $_local ) echo 'style="display:none"' ?>>
        <?php $paypal->renderForm( $form_id ) ?>
    </div>
<?php endif ?>

<?php if ( $_authorizenet || $_stripe ) : ?>
    <div class="ab-card-payment-button ab-row-fluid ab-nav-steps" <?php if ( $_local || $_paypal ) echo 'style="display:none"' ?>>
        <button class="ab-left ab-to-third-step ab-btn ladda-button" data-style="zoom-in" style="margin-right: 10px;" data-spinner-size="40">
            <span class="ladda-label"><?php _e( 'Back', 'bookly' ) ?></span>
        </button>
        <button class="ab-right ab-final-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php _e( 'Next', 'bookly' ) ?></span>
        </button>
    </div>
<?php endif ?>

<div class="ab-coupon-payment-button ab-row-fluid ab-nav-steps" style="display: none">
    <button class="ab-left ab-to-third-step ab-btn ladda-button" data-style="zoom-in" style="margin-right: 10px;" data-spinner-size="40">
        <span class="ladda-label"><?php _e( 'Back', 'bookly' ) ?></span>
    </button>
    <button class="ab-right ab-final-step ab-coupon-payment ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php _e( 'Next', 'bookly' ) ?></span>
    </button>
</div>
