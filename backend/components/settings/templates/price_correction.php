<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings;
use Bookly\Lib\Entities\Payment;
?>
<label for="bookly_<?php echo $gateway ?>_discount"><?php _e( 'Price correction', 'bookly' ) ?></label>
<?php if ( ! in_array( $gateway, array( Payment::TYPE_MOLLIE, Payment::TYPE_PAYSON, Payment::TYPE_STRIPE, Payment::TYPE_PAYUBIZ ) ) ) : ?>
    <?php if ( \Bookly\Lib\Config::taxesActive() ) :
        Settings\Proxy\Taxes::renderHelpMessage();
    else: ?>
        <p class="help-block"><?php esc_html_e( 'This setting affects the cost of the booking according to the payment gateway used. Specify a percentage or fixed amount. Use minus ("-") sign for decrease/discount.', 'bookly' ) ?></p>
    <?php endif ?>
<?php else: ?>
    <p class="help-block"><?php esc_html_e( 'This setting affects the cost of the booking according to the payment gateway used. Specify a percentage or fixed amount. Use minus ("-") sign for decrease/discount.', 'bookly' ) ?></p>
<?php endif ?>
<div class="row">
    <div class="col-md-6">
        <?php Settings\Inputs::renderNumber( 'bookly_' . $gateway . '_increase', __( 'Increase/Discount (%)', 'bookly' ), '', -100, 'any', 100 ) ?>
    </div>
    <div class="col-md-6">
        <?php Settings\Inputs::renderNumber( 'bookly_' . $gateway . '_addition', __( 'Addition/Deduction', 'bookly' ), '', null, 'any' ) ?>
    </div>
</div>
