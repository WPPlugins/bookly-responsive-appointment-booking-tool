<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Backend\Modules\Appearance\Components;
/** @var BooklyLite\Backend\Modules\Appearance\Lib\Helper $editable */
?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>
    <?php \BooklyLite\Lib\Proxy\Coupons::renderAppearance() ?>

    <div class="bookly-payment-nav">
        <div class="bookly-box bookly-js-payment-single-app">
            <?php $editable::renderText( 'bookly_l10n_info_payment_step_single_app', Components::getInstance()->renderCodes( array( 'step' => 7 ), false ), 'right' ) ?>
        </div>
        <div class="bookly-box bookly-js-payment-several-apps" style="display:none">
            <?php $editable::renderText( 'bookly_l10n_info_payment_step_several_apps', Components::getInstance()->renderCodes( array( 'step' => 7, 'extra_codes' => 1 ), false ), 'right' ) ?>
        </div>

        <div class="bookly-box bookly-list">
            <label>
                <input type="radio" name="payment" checked="checked" />
                <?php $editable::renderString( array( 'bookly_l10n_label_pay_locally', ) ) ?>
            </label>
        </div>

        <div class="bookly-box bookly-list">
            <label>
                <input type="radio" name="payment" />
                <?php $editable::renderString( array( 'bookly_l10n_label_pay_paypal', ) ) ?>
                <img src="<?php echo plugins_url( 'frontend/resources/images/paypal.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>" alt="paypal" />
            </label>
        </div>

        <div class="bookly-box bookly-list"
            <?php if ( BooklyLite\Lib\Proxy\Shared::appearanceRequiredInterfaceCreditCard( false ) == false ) : ?>
             style="display: none"
            <?php endif ?>
        >
            <label>
                <input type="radio" name="payment" id="bookly-card-payment" />
                <?php $editable::renderString( array( 'bookly_l10n_label_pay_ccard', ) ) ?>
                <img src="<?php echo plugins_url( 'frontend/resources/images/cards.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>" alt="cards" />
            </label>
            <form class="bookly-card-form bookly-clear-bottom" style="margin-top:15px;display: none;">
                <?php include '_card_payment.php' ?>
            </form>
        </div>

        <?php \BooklyLite\Lib\Proxy\Shared::renderAppearancePaymentGatewaySelector() ?>
    </div>

    <?php \BooklyLite\Lib\Proxy\RecurringAppointments::renderAppearanceEditableInfoMessage() ?>

    <div class="bookly-box bookly-nav-steps">
        <div class="bookly-back-step bookly-js-back-step bookly-btn">
            <?php $editable::renderString( array( 'bookly_l10n_button_back' ) ) ?>
        </div>
        <div class="bookly-next-step bookly-js-next-step bookly-btn">
            <?php $editable::renderString( array( 'bookly_l10n_step_payment_button_next' ) ) ?>
        </div>
    </div>
</div>