<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    echo $progress_tracker;
?>
<?php if ( get_option( 'ab_settings_coupons' ) ) : ?>
    <div class="ab-row ab-info-text-coupon"><?php echo $info_text_coupon ?></div>
    <div class="ab-row ab-list">
        <?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_coupon' ) ?>
        <?php if ( $coupon_code ) : ?>
            <?php echo esc_attr( $coupon_code ) . ' ✓' ?>
        <?php else : ?>
            <input class="ab-user-coupon" name="ab_coupon" type="text" value="<?php echo esc_attr( $coupon_code ) ?>" />
            <button class="ab-btn ladda-button btn-apply-coupon" data-style="zoom-in" data-spinner-size="40">
                <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_apply' ) ?></span><span class="spinner"></span>
            </button>
        <?php endif ?>
        <div class="ab-label-error ab-coupon-error"></div>
    </div>
<?php endif ?>

<div class="ab-payment-nav">
    <div class="ab-row"><?php echo $info_text ?></div>
    <?php if ( $pay_local ) : ?>
        <div class="ab-row ab-list">
            <label>
                <input type="radio" class="ab-payment" checked="checked" name="payment-method-<?php echo $form_id ?>" value="local"/>
                <span><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_locally' ) ?></span>
            </label>
        </div>
    <?php endif ?>

    <?php if ( $pay_paypal ) : ?>
        <div class="ab-row ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local ) ?> name="payment-method-<?php echo $form_id ?>" value="paypal"/>
                <span><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_paypal' ) ?></span>
                <img src="<?php echo plugins_url( 'frontend/resources/images/paypal.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>" alt="paypal" />
            </label>
            <?php if ( $payment['gateway'] == BooklyLite\Lib\Entities\Payment::TYPE_PAYPAL && $payment['status'] == 'error' ) : ?>
                <div class="ab-label-error"><?php echo $payment['data'] ?></div>
            <?php endif ?>
        </div>
    <?php endif ?>

    <?php if ( $pay_authorizenet ) : ?>
        <div class="ab-row ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal ) ?> name="payment-method-<?php echo $form_id ?>" value="card" data-form="authorizenet" />
                <span><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_ccard' ) ?></span>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" alt="cards" />
            </label>
            <form class="ab-authorizenet" style="<?php if ( $pay_local || $pay_paypal ) echo "display: none;"; ?> margin-top: 15px;">
                <?php include '_card_payment.php' ?>
            </form>
        </div>
    <?php endif ?>

    <?php if ( $pay_stripe ) : ?>
        <div class="ab-row ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal && !$pay_authorizenet ) ?> name="payment-method-<?php echo $form_id ?>" value="card" data-form="stripe" />
                <span><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_ccard' ) ?></span>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" alt="cards" />
            </label>
            <?php if ( get_option( 'ab_stripe_publishable_key' ) != '' ) : ?>
                <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
            <?php endif ?>
            <form class="ab-stripe" style="<?php if ( $pay_local || $pay_paypal || $pay_authorizenet ) echo "display: none;"; ?> margin-top: 15px;">
                <input type="hidden" id="publishable_key" value="<?php echo get_option( 'ab_stripe_publishable_key' ) ?>">
                <?php include '_card_payment.php' ?>
            </form>
        </div>
    <?php endif ?>

    <?php if ( $pay_2checkout ) : ?>
        <div class="ab-row ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal && !$pay_authorizenet && !$pay_stripe ) ?> name="payment-method-<?php echo $form_id ?>" value="2checkout"/>
                <span><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_ccard' ) ?></span>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" alt="cards" />
            </label>
        </div>
    <?php endif ?>

    <?php if ( $pay_payulatam ) : ?>
        <div class="ab-row ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal && !$pay_authorizenet && !$pay_stripe && !$pay_2checkout ) ?> name="payment-method-<?php echo $form_id ?>" value="payulatam"/>
                <span><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_ccard' ) ?></span>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" alt="cards" />
            </label>
            <?php if ( $payment['gateway'] == BooklyLite\Lib\Entities\Payment::TYPE_PAYULATAM && $payment['status'] == 'error' ) : ?>
                <div class="ab-label-error" style="padding-top: 5px;">* <?php echo $payment['data'] ?></div>
            <?php endif ?>
        </div>
    <?php endif ?>
    <div class="ab-row ab-list" style="display: none">
        <input type="radio" class="ab-coupon-free" name="payment-method-<?php echo $form_id ?>" value="coupon" />
    </div>

    <?php if ( $pay_payson ) : ?>
        <div class="ab-row ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal && !$pay_authorizenet && !$pay_stripe && !$pay_payulatam ) ?> name="payment-method-<?php echo $form_id ?>" value="payson"/>
                <span><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_ccard' ) ?></span>
                <img src="<?php echo plugins_url( 'resources/images/cards.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" alt="cards" />
            </label>
            <?php if ( $payment['gateway'] == BooklyLite\Lib\Entities\Payment::TYPE_PAYSON && $payment['status'] == 'error' ) : ?>
                <div class="ab-label-error" style="padding-top: 5px;">* <?php echo $payment['data'] ?></div>
            <?php endif ?>
        </div>
    <?php endif ?>

    <?php if ( $pay_mollie ) : ?>
        <div class="ab-row ab-list">
            <label>
                <input type="radio" class="ab-payment" <?php checked( !$pay_local && !$pay_paypal && !$pay_authorizenet && !$pay_stripe && !$pay_payulatam && !$pay_payson ) ?> name="payment-method-<?php echo $form_id ?>" value="mollie"/>
                <span><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_pay_mollie' ) ?></span>
                <img src="<?php echo plugins_url( 'resources/images/mollie.png', dirname( dirname( dirname( __FILE__ ) ) ) ) ?>" alt="mollie" />
            </label>
            <?php if ( $payment['gateway'] == BooklyLite\Lib\Entities\Payment::TYPE_MOLLIE && $payment['status'] == 'error' ) : ?>
                <div class="ab-label-error" style="padding-top: 5px;">* <?php echo $payment['data'] ?></div>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>

<?php if ( $pay_local ) : ?>
    <div class="ab-local-payment-button ab-row ab-nav-steps">
        <button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in"  data-spinner-size="40">
            <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) ?></span>
        </button>
        <button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) ?></span>
        </button>
    </div>
<?php endif ?>

<?php if ( $pay_paypal ) : ?>
    <div class="ab-paypal-payment-button ab-row ab-nav-steps" <?php if ( $pay_local ) echo 'style="display:none"' ?>>
        <?php BooklyLite\Lib\Payment\PayPal::renderForm( $form_id ) ?>
    </div>
<?php endif ?>

<?php if ( $pay_2checkout ) : ?>
    <div class="ab-2checkout-payment-button ab-row ab-nav-steps" <?php if ( $pay_local || $pay_paypal ) echo 'style="display:none"' ?>>
        <?php BooklyLite\Lib\Payment\TwoCheckout::renderForm( $form_id ) ?>
    </div>
<?php endif ?>

<?php if ( $pay_payulatam ) : ?>
    <div class="ab-payulatam-payment-button ab-row ab-nav-steps" <?php if ( $pay_local || $pay_paypal || $pay_2checkout ) echo 'style="display:none"' ?>>
        <?php BooklyLite\Lib\Payment\PayuLatam::renderForm( $form_id ) ?>
    </div>
<?php endif ?>

<?php if ( $pay_authorizenet || $pay_stripe ) : ?>
    <div class="ab-card-payment-button ab-row ab-nav-steps" <?php if ( $pay_local || $pay_paypal || $pay_2checkout || $pay_payulatam ) echo 'style="display:none"' ?>>
        <button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) ?></span>
        </button>
        <button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) ?></span>
        </button>
    </div>
<?php endif ?>

<?php if ( $pay_payson ) : ?>
    <div class="ab-payson-payment-button ab-row ab-nav-steps" <?php if ( $pay_local || $pay_paypal || $pay_2checkout || $pay_payulatam || $pay_authorizenet || $pay_stripe ) echo 'style="display:none"' ?>>
        <?php BooklyLite\Lib\Payment\Payson::renderForm( $form_id ) ?>
    </div>
<?php endif ?>

<?php if ( $pay_mollie ) : ?>
    <div class="ab-mollie-payment-button ab-row ab-nav-steps" <?php if ( $pay_local || $pay_paypal || $pay_2checkout || $pay_payulatam || $pay_authorizenet || $pay_stripe || $pay_payson ) echo 'style="display:none"' ?>>
        <?php BooklyLite\Lib\Payment\Mollie::renderForm( $form_id ) ?>
    </div>
<?php endif ?>

<div class="ab-coupon-payment-button ab-row ab-nav-steps" style="display: none">
    <button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) ?></span>
    </button>
    <button class="ab-right ab-next-step ab-coupon-payment ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) ?></span>
    </button>
</div>
