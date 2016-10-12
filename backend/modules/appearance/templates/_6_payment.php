<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>
    <!--   Coupons   -->
    <div class="ab-row">
        <span data-inputclass="input-xxlarge" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_coupon' ) ) ?>" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 6 ), false ) ) ?>" class="ab_editable" id="ab-text-info-coupon" data-type="textarea"><?php echo esc_html( get_option( 'ab_appearance_text_info_coupon' ) ) ?></span>
    </div>

    <div class="ab-row ab-list">
        <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_coupon' ) ) ?>" class="ab_editable editable editable-click inline-block" id="ab-text-label-coupon" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_coupon' ) ) ?></span>
        <div class="ab-inline-block">
            <input class="ab-user-coupon" type="text" />
            <div class="ab-btn btn-apply-coupon">
                <span class="ab_editable" id="ab-text-button-apply" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_apply' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_apply' ) ) ?></span>
            </div>
        </div>
    </div>
    <div class="ab-payment-nav">
        <div class="ab-row">
            <span data-inputclass="input-xxlarge" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_payment_step' ) ) ?>" data-notes="<?php echo esc_attr( $this->render( '_codes', compact( 'step' ), false ) ) ?>" class="ab_editable" id="ab-text-info-payment" data-type="textarea"><?php echo esc_html( get_option( 'ab_appearance_text_info_payment_step' ) ) ?></span>
        </div>

        <div class="ab-row ab-list">
            <label>
                <input type="radio" name="payment" checked="checked" />
                <span id="ab-text-label-pay-locally" class="ab_editable" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_pay_locally' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_label_pay_locally' ) ) ?></span>
            </label>
        </div>

        <div class="ab-row ab-list">
            <label>
                <input type="radio" name="payment" />
                <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_pay_paypal' ) ) ?>" class="ab_editable editable editable-click inline-block" id="ab-text-label-pay-paypal" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_pay_paypal' ) ) ?></span>
                <img src="<?php echo plugins_url( 'frontend/resources/images/paypal.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>" alt="paypal" />
            </label>
        </div>

        <div class="ab-row ab-list">
            <label>
                <input type="radio" name="payment" class="ab-card-payment" />
                <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_pay_ccard' ) ) ?>" class="ab_editable editable editable-click inline-block" id="ab-text-label-pay-ccard" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_pay_ccard' ) ) ?></span>
                <img src="<?php echo plugins_url( 'frontend/resources/images/cards.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>" alt="cards" />
            </label>
            <form class="ab-card-form ab-clearBottom" style="margin-top:15px;display: none;">
                <?php include '_card_payment.php' ?>
            </form>
        </div>

        <div class="ab-row ab-list">
            <label>
                <input type="radio" name="payment" />
                <span id="ab-text-label-pay-mollie" class="ab_editable" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_pay_mollie' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_label_pay_mollie' ) ) ?></span>
                <img src="<?php echo plugins_url( 'frontend/resources/images/mollie.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>" alt="mollie" />
            </label>
        </div>
    </div>

    <!-- buttons -->
    <div class="ab-row ab-nav-steps">
        <div class="ab-left ab-back-step ab-btn">
            <span class="text_back ab_editable" id="ab-text-button-back" data-mirror="text_back" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_back' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_back' ) ) ?></span>
        </div>
        <div class="ab-right ab-next-step ab-btn">
            <span class="text_next ab_editable" id="ab-text-button-next" data-mirror="text_next" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_next' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_next' ) ) ?></span>
        </div>
    </div>
</div>