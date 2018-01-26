<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Lib\Proxy;
use BooklyLite\Lib\Utils\Common;

echo $progress_tracker;
echo $coupon_html;
?>

<div class="bookly-payment-nav">
    <div class="bookly-box"><?php echo $info_text ?></div>
    <?php if ( $pay_local ) : ?>
        <div class="bookly-box bookly-list">
            <label>
                <input type="radio" class="bookly-payment" name="payment-method-<?php echo $form_id ?>" value="local"/>
                <span><?php echo Common::getTranslatedOption( 'bookly_l10n_label_pay_locally' ) ?></span>
            </label>
        </div>
    <?php endif ?>

    <?php if ( $pay_paypal ) : ?>
        <div class="bookly-box bookly-list">
            <label>
                <input type="radio" class="bookly-payment" name="payment-method-<?php echo $form_id ?>" value="paypal"/>
                <span><?php echo Common::getTranslatedOption( 'bookly_l10n_label_pay_paypal' ) ?></span>
                <img src="<?php echo plugins_url( 'frontend/resources/images/paypal.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>" alt="PayPal" />
            </label>
            <?php if ( $payment['gateway'] == BooklyLite\Lib\Entities\Payment::TYPE_PAYPAL && $payment['status'] == 'error' ) : ?>
                <div class="bookly-label-error"><?php echo $payment['data'] ?></div>
            <?php endif ?>
        </div>
    <?php endif ?>

    <div class="bookly-box bookly-list" style="display: none">
        <input type="radio" class="bookly-js-coupon-free" name="payment-method-<?php echo $form_id ?>" value="coupon" />
    </div>

    <?php Proxy\Shared::renderPaymentGatewaySelector( $form_id, $payment ) ?>
</div>

<?php $this->render( '_info_block', compact( 'info_message' ) ) ?>

<?php if ( $pay_local ) : ?>
    <div class="bookly-gateway-buttons pay-local bookly-box bookly-nav-steps">
        <button class="bookly-back-step bookly-js-back-step bookly-btn ladda-button" data-style="zoom-in"  data-spinner-size="40">
            <span class="ladda-label"><?php echo Common::getTranslatedOption( 'bookly_l10n_button_back' ) ?></span>
        </button>
        <button class="bookly-next-step bookly-js-next-step bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php echo Common::getTranslatedOption( 'bookly_l10n_step_payment_button_next' ) ?></span>
        </button>
    </div>
<?php endif ?>

<?php if ( $pay_paypal ) : ?>
    <div class="bookly-gateway-buttons pay-paypal bookly-box bookly-nav-steps" style="display:none">
        <?php if ( $pay_paypal === BooklyLite\Lib\Payment\PayPal::TYPE_EXPRESS_CHECKOUT ) :
            BooklyLite\Lib\Payment\PayPal::renderECForm( $form_id );
        elseif ( $pay_paypal === BooklyLite\Lib\Payment\PayPal::TYPE_PAYMENTS_STANDARD ) :
            Proxy\PaypalPaymentsStandard::renderPaymentForm( $form_id, $page_url );
        endif ?>
    </div>
<?php endif ?>

<div class="bookly-gateway-buttons pay-card bookly-box bookly-nav-steps" style="display:none">
    <button class="bookly-back-step bookly-js-back-step bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo Common::getTranslatedOption( 'bookly_l10n_button_back' ) ?></span>
    </button>
    <button class="bookly-next-step bookly-js-next-step bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo Common::getTranslatedOption( 'bookly_l10n_step_payment_button_next' ) ?></span>
    </button>
</div>

<?php Proxy\Shared::renderPaymentGatewayForm( $form_id, $page_url) ?>

<div class="bookly-gateway-buttons pay-coupon bookly-box bookly-nav-steps" style="display: none">
    <button class="bookly-back-step bookly-js-back-step bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo Common::getTranslatedOption( 'bookly_l10n_button_back' ) ?></span>
    </button>
    <button class="bookly-next-step bookly-js-next-step bookly-js-coupon-payment bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo Common::getTranslatedOption( 'bookly_l10n_step_payment_button_next' ) ?></span>
    </button>
</div>
