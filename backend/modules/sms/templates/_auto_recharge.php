<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<p class="alert alert-info">
    <?php _e( 'We will only charge your PayPal account when your balance falls below $10.', 'bookly' ) ?>
</p>

<div class="form-group">
    <label for="bookly-recharge-amount"><?php _e( 'Amount', 'bookly' ) ?></label>
    <select id="bookly-recharge-amount" class="form-control"<?php disabled( $sms->isAutoRechargeEnabled() ) ?>>
        <?php foreach ( array( 10, 25, 50, 100 ) as $amount ) : ?>
            <?php printf( '<option value="%1$s" %2$s>$%1$s</option>', $amount, selected( $sms->getAutoRechargeAmount() == $amount, true, false ) ) ?>
        <?php endforeach ?>
    </select>
</div>

<div class="panel-footer">
    <button id="bookly-auto-recharge-init" class="btn btn-lg btn-success ladda-button" data-style="zoom-in" data-spinner-size="40"<?php disabled( $sms->isAutoRechargeEnabled() ) ?>><span class="ladda-label"><?php _e( 'Enable Auto-Recharge', 'bookly') ?></span></button>
    <button id="bookly-auto-recharge-decline" class="btn btn-lg btn-default ladda-button" data-style="zoom-in" data-spinner-size="40"<?php disabled( $sms->isAutoRechargeEnabled(), false ) ?>><span class="ladda-label"><?php _e( 'Disable Auto-Recharge', 'bookly' ) ?></span></button>
</div>