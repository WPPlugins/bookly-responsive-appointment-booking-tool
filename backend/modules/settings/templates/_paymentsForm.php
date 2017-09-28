<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'payments' ) ) ?>">
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="bookly_pmt_currency"><?php _e( 'Currency', 'bookly' ) ?></label>
                <select id="bookly_pmt_currency" class="form-control" name="bookly_pmt_currency">
                    <?php foreach ( \BooklyLite\Lib\Config::getCurrencyCodes() as $code ) : ?>
                        <option value="<?php echo $code ?>" <?php selected( get_option( 'bookly_pmt_currency' ), $code ) ?> ><?php echo $code ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="col-lg-6">
            <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_coupons', __( 'Coupons', 'bookly' ) ) ?>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="bookly_pmt_local"><?php _e( 'Service paid locally', 'bookly' ) ?></label>
        </div>
        <div class="panel-body">
            <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_local', null, null, array( array( 'disabled', __( 'Disabled', 'bookly' ) ), array( '1', __( 'Enabled', 'bookly' ) ) ) ) ?>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="bookly_pmt_2checkout">2Checkout</label>
            <img style="margin-left: 10px; float: right" src="<?php echo plugins_url( 'frontend/resources/images/2Checkout.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>"/>
        </div>
        <div class="panel-body">
            <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_2checkout', null, null, array( array( 'disabled', __( 'Disabled', 'bookly' ) ), array( 'standard_checkout', __( '2Checkout Standard Checkout', 'bookly' ) ) ) ) ?>
            <div class="bookly-2checkout">
                <div class="form-group">
                    <h4><?php _e( 'Instructions', 'bookly' ) ?></h4>
                    <p>
                        <?php _e( 'In <b>Checkout Options</b> of your 2Checkout account do the following steps:', 'bookly' ) ?>
                    </p>
                    <ol>
                        <li><?php _e( 'In <b>Direct Return</b> select <b>Header Redirect (Your URL)</b>.', 'bookly' ) ?></li>
                        <li><?php _e( 'In <b>Approved URL</b> enter the URL of your booking page.', 'bookly' ) ?></li>
                    </ol>
                    <p>
                        <?php _e( 'Finally provide the necessary information in the form below.', 'bookly' ) ?>
                    </p>
                </div>
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_2checkout_api_seller_id', __( 'Account Number', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_2checkout_api_secret_word', __( 'Secret Word', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_2checkout_sandbox', __( 'Sandbox Mode', 'bookly' ), null, array( array( 0, __( 'No', 'bookly' ) ), array( 1, __( 'Yes', 'bookly' ) ) ) ) ?>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="bookly_pmt_paypal">PayPal</label>
            <img style="margin-left: 10px; float: right" src="<?php echo plugins_url( 'frontend/resources/images/paypal.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>" />
        </div>
        <div class="panel-body">
            <div class="form-group">
                <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_paypal', null, null,
                    array(
                        array( 'disabled', __( 'Disabled', 'bookly' ) ),
                        array( 'ec', 'PayPal Express Checkout' ),
                    ) ) ?>
            </div>
            <div class="bookly-paypal">
                <div class="bookly-paypal-ec">
                    <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_paypal_api_username',  __( 'API Username', 'bookly' ) ) ?>
                    <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_paypal_api_password',  __( 'API Password', 'bookly' ) ) ?>
                    <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_paypal_api_signature', __( 'API Signature', 'bookly' ) ) ?>
                </div>
                <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_paypal_sandbox', __( 'Sandbox Mode', 'bookly' ), null, array( array( 1, __( 'Yes', 'bookly' ) ), array( 0, __( 'No', 'bookly' ) ) ) ) ?>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="bookly_pmt_authorize_net">Authorize.Net</label>
            <img style="margin-left: 10px; float: right" src="<?php echo plugins_url( 'frontend/resources/images/authorize_net.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>"/>
        </div>
        <div class="panel-body">
            <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_authorize_net', null, null, array( array( 'disabled', __( 'Disabled', 'bookly' ) ), array( 'aim', 'Authorize.Net AIM' ) ) ) ?>
            <div class="authorize-net">
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_authorize_net_api_login_id', __( 'API Login ID', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_authorize_net_transaction_key', __( 'API Transaction Key', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_authorize_net_sandbox', __( 'Sandbox Mode', 'bookly' ), null, array( array( 1, __( 'Yes', 'bookly' ) ), array( 0, __( 'No', 'bookly' ) ) ) ) ?>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="bookly_pmt_stripe">Stripe</label>
            <img class="pull-right" src="<?php echo plugins_url( 'frontend/resources/images/stripe.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>">
        </div>
        <div class="panel-body">
            <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_stripe', null, null, array( array( 'disabled', __( 'Disabled', 'bookly' ) ), array( '1', __( 'Enabled', 'bookly' ) ) ) ) ?>
            <div class="bookly-stripe">
                <div class="form-group">
                    <h4><?php _e( 'Instructions', 'bookly' ) ?></h4>
                    <p>
                        <?php _e( 'If <b>Publishable Key</b> is provided then Bookly will use <a href="https://stripe.com/docs/stripe.js" target="_blank">Stripe.js</a><br/>for collecting credit card details.', 'bookly' ) ?>
                    </p>
                </div>
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_stripe_secret_key', __( 'Secret Key', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_stripe_publishable_key', __( 'Publishable Key', 'bookly' ) ) ?>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="bookly_pmt_payu_latam">PayU Latam</label>
            <img class="pull-right" src="<?php echo plugins_url( 'frontend/resources/images/payu_latam.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>"/>
        </div>
        <div class="panel-body">
            <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_payu_latam', null, null, array( array( 'disabled', __( 'Disabled', 'bookly' ) ), array( '1', __( 'Enabled', 'bookly' ) ) ) ) ?>
            <div class="bookly-payu_latam">
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_payu_latam_api_key', __( 'API Key', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_payu_latam_api_account_id', __( 'Account ID', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_payu_latam_api_merchant_id', __( 'Merchant ID', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_payu_latam_sandbox', __( 'Sandbox Mode', 'bookly' ), null, array( array( 0, __( 'No', 'bookly' ) ), array( 1, __( 'Yes', 'bookly' ) ) ) ) ?>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="bookly_pmt_payson">Payson</label>
            <img class="pull-right" src="<?php echo plugins_url( 'frontend/resources/images/payson.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>"/>
        </div>
        <div class="panel-body">
            <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_payson', null, null, array( array( 'disabled', __( 'Disabled', 'bookly' ) ), array( '1', __( 'Enabled', 'bookly' ) ) ) ) ?>
            <div class="bookly-payson">
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_payson_api_agent_id', __( 'Agent ID', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_payson_api_key', __( 'API Key', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_payson_api_receiver_email', __( 'Receiver Email (login)', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionFlags( 'bookly_pmt_payson_funding', array( array( 'CREDITCARD', __( 'Card', 'bookly' ) ), array( 'INVOICE', __( 'Invoice', 'bookly' ) ) ), __( 'Funding', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_payson_fees_payer', __( 'Fees Payer', 'bookly' ), null, array( array( 'PRIMARYRECEIVER', __( 'I am', 'bookly' ) ), array( 'SENDER', __( 'Client', 'bookly' ) ) ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_payson_sandbox', __( 'Sandbox Mode', 'bookly' ), null, array( array( 0, __( 'No', 'bookly' ) ), array( 1, __( 'Yes', 'bookly' ) ) ) ) ?>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <label for="bookly_pmt_mollie">Mollie</label>
            <img class="pull-right" src="<?php echo plugins_url( 'frontend/resources/images/mollie.png', \BooklyLite\Lib\Plugin::getMainFile() ) ?>"/>
        </div>
        <div class="panel-body">
            <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_pmt_mollie', null, null, array( array( 'disabled', __( 'Disabled', 'bookly' ) ), array( '1', __( 'Enabled', 'bookly' ) ) ) ) ?>
            <div class="bookly-mollie">
                <?php \BooklyLite\Lib\Utils\Common::optionText( 'bookly_pmt_mollie_api_key', __( 'API Key', 'bookly' ) ) ?>
            </div>
        </div>
    </div>

    <?php do_action( 'bookly_render_payment_settings' ) ?>

    <div class="panel-footer">
        <?php \BooklyLite\Lib\Utils\Common::submitButton() ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton( 'bookly-payments-reset' ) ?>
    </div>
</form>