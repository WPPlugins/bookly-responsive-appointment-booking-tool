<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>
    <div class="bookly-box">
        <span data-option-default="<?php form_option( 'bookly_l10n_info_coupon' ) ?>" data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 7 ), false ) ) ?>" class="bookly-editable" id="bookly_l10n_info_coupon" data-type="textarea"><?php echo esc_html( get_option( 'bookly_l10n_info_coupon' ) ) ?></span>
    </div>

    <div class="bookly-box ab-list">
        <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_label_coupon', ) ) ?>
        <div class="ab-inline-block">
            <input class="ab-user-coupon" type="text" />
            <div class="ab-btn btn-apply-coupon">
                <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_apply', ) ) ?>
            </div>
        </div>
    </div>
    <div class="ab-payment-nav">
        <div class="bookly-box">
            <span data-option-default="<?php form_option( 'bookly_l10n_info_payment_step' ) ?>" data-notes="<?php echo esc_attr( $this->render( '_codes', compact( 'step' ), false ) ) ?>" class="bookly-editable" id="bookly_l10n_info_payment_step" data-type="textarea"><?php echo esc_html( get_option( 'bookly_l10n_info_payment_step' ) ) ?></span>
        </div>

        <div class="bookly-box ab-list">
            <label>
                <input type="radio" name="payment" checked="checked" />
                <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_label_pay_locally', ) ) ?>
            </label>
        </div>

        <div class="bookly-box ab-list">
            <label>
                <input type="radio" name="payment" />
                <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_label_pay_paypal', ) ) ?>
                <img src="<?php echo plugins_url( 'frontend/resources/images/paypal.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>" alt="paypal" />
            </label>
        </div>

        <div class="bookly-box ab-list">
            <label>
                <input type="radio" name="payment" class="ab-card-payment" />
                <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_label_pay_ccard', ) ) ?>
                <img src="<?php echo plugins_url( 'frontend/resources/images/cards.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>" alt="cards" />
            </label>
            <form class="ab-card-form ab-clearBottom" style="margin-top:15px;display: none;">
                <?php include '_card_payment.php' ?>
            </form>
        </div>

        <div class="bookly-box ab-list">
            <label>
                <input type="radio" name="payment" />
                <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_label_pay_mollie', ) ) ?>
                <img src="<?php echo plugins_url( 'frontend/resources/images/mollie.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>" alt="mollie" />
            </label>
        </div>

        <?php do_action( 'bookly_render_appearance_payment_gateway_selector' ) ?>
    </div>

    <?php do_action( 'bookly_recurring_appointments_render_editable_message_info' ) ?>

    <div class="bookly-box bookly-nav-steps">
        <div class="bookly-back-step ab-btn">
            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_back' ), 'bookly-js-text-back' ) ?>
        </div>
        <div class="bookly-next-step ab-btn">
            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_next' ), 'bookly-js-text-next' ) ?>
        </div>
    </div>
</div>