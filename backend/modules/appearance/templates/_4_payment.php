<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-booking-form" style="overflow: hidden">
    <!-- Progress Tracker-->
    <?php $step = 4; include '_progress_tracker.php'; ?>
    <!-- payment -->
    <div class="ab-payment">
        <!--   Coupons   -->
        <div style="margin-bottom: 15px!important;" class="ab-row-fluid">
            <span data-inputclass="input-xxlarge" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_coupon' ) ) ?>" data-notes = "<?php _e( '<b>[[SERVICE_PRICE]]</b> - price of service', 'ab' );?>" data-link-class="ab-text-info-coupon" class="ab-text-info-coupon-preview ab-row-fluid ab_editable" id="ab-text-info-coupon" data-type="textarea" data-pk="1"><?php echo esc_html( get_option( 'ab_appearance_text_info_coupon' ) ) ?></span>
        </div>

        <div style="margin-bottom: 15px!important;">
            <span style="display: inline-block;" data-default="<?php echo get_option( 'ab_appearance_text_label_coupon' ); ?>" data-link-class="text_coupon_label" class="ab_editable editable editable-click" id="ab-text-label-coupon" data-type="text" data-pk="1"><?php echo esc_html(get_option( 'ab_appearance_text_label_coupon' )) ?></span>
            <div style="display: inline-block;">
                <input class="ab-user-coupon" maxlength="40" type="text" value="" style="display: inline-block; margin: 0 10px;">
                <button class="ab-btn ladda-button orange" style="display: inline-block;">Apply</button>
            </div>
        </div>
        <div class="ab-clear"></div>

        <div style="margin-bottom: 15px!important;" class="ab-row-fluid">
            <span data-inputclass="input-xxlarge" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_fourth_step' ) ) ?>" data-link-class="ab-text-info-fourth" class="ab-text-info-fourth-preview ab-row-fluid ab_editable" id="ab-text-info-fourth" data-type="textarea" data-pk="1"><?php echo esc_html( get_option( 'ab_appearance_text_info_fourth_step' ) ) ?></span>
        </div>

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
                <img src="<?php echo plugins_url( 'frontend/resources/images/paypal.png', AB_PATH . '/main.php' ) ?>" style="margin-left: 10px;" alt="paypal" />
            </label>
        </div>
        <!--div class="ab-row-fluid">
            <label>
                <input type="radio" name="payment" class="ab-authorizenet-payment" value="authorizenet"/>
                <?php _e( 'I will pay now with Credit Card', 'ab' ) ?>
                <img src="<?php echo plugins_url( 'frontend/resources/images/cards.png', AB_PATH . '/main.php' ) ?>" style="margin-left: 10px;" alt="cards" />
            </label>
            <form class="authorizenet ab-clearBottom" style="display: none; margin-top: 15px;">
                <?php include "_card_payment.php" ?>
            </form>
        </div-->
        <div class="ab-row-fluid">
            <label>
                <input type="radio" name="payment" class="ab-stripe-payment" value="stripe"/>
                <?php _e( 'I will pay now with Credit Card', 'ab' ) ?>
                <img src="<?php echo plugins_url( 'frontend/resources/images/cards.png', AB_PATH . '/main.php' ) ?>" style="margin-left: 10px;" alt="cards" />
            </label>
            <form class="stripe ab-clearBottom" style="display: none; margin-top: 15px;">
                <?php include "_card_payment.php" ?>
            </form>
        </div>

        <!-- buttons -->
        <div class="ab-local-pay-button ab-row-fluid ab-nav-steps last-row">
            <button class="ab-left ab-to-third-step ab-btn ladda-button orange zoom-in" style="margin-right: 10px;">
                <span><?php _e( 'Back', 'ab' ) ?></span>
            </button>
            <button class="ab-right ab-final-step ab-btn ladda-button orange zoom-in">
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