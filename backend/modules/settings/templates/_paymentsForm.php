<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'type', '_payments' ) ) ?>" class="ab-settings-form">
    <table class="form-horizontal">
        <tr>
            <td style="width: 170px;">
                <label for="ab_paypal_currency"><?php _e( 'Currency', 'bookly' ) ?></label>
            </td>
            <td>
                <select id="ab_paypal_currency" class="form-control" name="ab_paypal_currency">
                    <?php foreach ( array( 'AUD', 'BRL', 'CAD', 'CHF', 'CLP', 'COP', 'CZK', 'DKK', 'EUR', 'GBP', 'GTQ', 'HKD', 'HUF', 'IDR', 'INR', 'ILS', 'JPY', 'KRW', 'KZT', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'QAR', 'RON', 'RMB', 'RUB', 'SAR', 'SEK', 'SGD', 'THB', 'TRY', 'TWD', 'UGX', 'USD', 'ZAR' ) as $code ): ?>
                        <option value="<?php echo $code ?>" <?php selected( get_option( 'ab_paypal_currency' ), $code ) ?> ><?php echo $code ?></option>
                    <?php endforeach ?>
                </select>
            </td>
        </tr>
        <tr>
            <td style="width: 170px;">
                <label for="ab_settings_coupons"><?php _e( 'Coupons', 'bookly' ) ?></label>
            </td>
            <td>
                <?php AB_Utils::optionToggle( 'ab_settings_coupons' ) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2"><div class="ab-payments-title"><?php _e( 'Service paid locally', 'bookly' ) ?></div></td>
        </tr>
        <tr>
            <td colspan="2">
                <?php AB_Utils::optionToggle( 'ab_settings_pay_locally' ) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2"><div class="ab-payments-title">PayPal</div></td>
        </tr>
        <tr>
            <td colspan="2">
                <?php AB_Utils::optionToggle( 'ab_paypal_type', array( 'f' => array( 'disabled', __( 'Disabled', 'bookly' ) ), 't' => array( 'ec', 'PayPal Express Checkout' ) ) ) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2"><div class="ab-payments-title">Authorize.Net</div></td>
        </tr>
        <tr>
            <td colspan="2">
                <?php AB_Utils::optionToggle( 'ab_authorizenet_type', array( 'f' => array( 'disabled', __( 'Disabled', 'bookly' ) ), 't' => array( 'aim', 'Authorize.Net AIM' ) ) ) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2"><div class="ab-payments-title">Stripe</div></td>
        </tr>
        <tr>
            <td colspan="2">
                <?php AB_Utils::optionToggle( 'ab_stripe' ) ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php AB_Utils::submitButton() ?>
                <?php AB_Utils::resetButton( 'ab-payments-reset' ) ?>
            </td>
            <td></td>
        </tr>
    </table>
</form>