<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
$business  = 'finance@ladela.com';
$action    = 'https://www.paypal.com/cgi-bin/webscr';
$page_slug =  Bookly\Backend\Modules\Sms\Ajax::pageSlug();
?>
<div class="row">
    <div class="col-xs-12 col-md-3">
        <form action="<?php echo esc_url( $action ) ?>" method="post">
            <input type="hidden" name="item_name" value="Bookly SMS">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="amount" value="10">
            <input type="hidden" name="return" value="<?php echo Common::escAdminUrl( $page_slug, array( 'paypal_result' => 'success' ) ) ?>">
            <input type="hidden" name="cancel_return" value="<?php echo Common::escAdminUrl( $page_slug, array( 'paypal_result' => 'cancel', 'tab' => 'add_money' ) ) ?>">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="<?php echo esc_attr( $business ) ?>">
            <input type="hidden" name="custom" value="<?php echo esc_attr( $sms->getUserName() ) ?>">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="handling" value="0.77">
            <input type="hidden" name="landing_page" value="billing">
            <input type="image" class="img-responsive"
                   src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/buy10.png' ) ?>"
                   border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif"
                 width="1" height="1">
        </form>
    </div>
    <div class="col-xs-12 col-md-3">
        <form action="<?php echo esc_url( $action ) ?>" method="post">
            <input type="hidden" name="item_name" value="Bookly SMS">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="amount" value="25">
            <input type="hidden" name="return" value="<?php echo Common::escAdminUrl( $page_slug, array( 'paypal_result' => 'success' ) ) ?>">
            <input type="hidden" name="cancel_return" value="<?php echo Common::escAdminUrl( $page_slug, array( 'paypal_result' => 'cancel', 'tab' => 'add_money' ) ) ?>">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="<?php echo esc_attr( $business ) ?>">
            <input type="hidden" name="custom" value="<?php echo esc_attr( $sms->getUserName() ) ?>">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="handling" value="1.46">
            <input type="hidden" name="landing_page" value="billing">
            <input type="image" class="img-responsive"
                   src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/buy25.png' ) ?>"
                   border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif"
                 width="1" height="1">
        </form>
    </div>
    <div class="col-xs-12 col-md-3">
        <form action="<?php echo esc_url( $action ) ?>" method="post">
            <input type="hidden" name="item_name" value="Bookly SMS">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="amount" value="50">
            <input type="hidden" name="return" value="<?php echo Common::escAdminUrl( $page_slug, array( 'paypal_result' => 'success' ) ) ?>">
            <input type="hidden" name="cancel_return" value="<?php echo Common::escAdminUrl( $page_slug, array( 'paypal_result' => 'cancel', 'tab' => 'add_money' ) ) ?>">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="<?php echo esc_attr( $business ) ?>">
            <input type="hidden" name="custom" value="<?php echo esc_attr( $sms->getUserName() ) ?>">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="handling" value="2.61">
            <input type="hidden" name="landing_page" value="billing">
            <input type="image" class="img-responsive"
                   src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/buy50.png' ) ?>"
                   border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif"
                 width="1" height="1">
        </form>
    </div>
    <div class="col-xs-12 col-md-3">
        <form action="<?php echo esc_url( $action ) ?>" method="post">
            <input type="hidden" name="item_name" value="Bookly SMS">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="amount" value="100">
            <input type="hidden" name="return" value="<?php echo Common::escAdminUrl( $page_slug, array( 'paypal_result' => 'success' ) ) ?>">
            <input type="hidden" name="cancel_return" value="<?php echo Common::escAdminUrl( $page_slug, array( 'paypal_result' => 'cancel', 'tab' => 'add_money' ) ) ?>">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="business" value="<?php echo esc_attr( $business ) ?>">
            <input type="hidden" name="custom" value="<?php echo esc_attr( $sms->getUserName() ) ?>">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="handling" value="4.92">
            <input type="hidden" name="landing_page" value="billing">
            <input type="image" class="img-responsive"
                   src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/buy100.png' ) ?>"
                   border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
            <img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif"
                 width="1" height="1">
        </form>
    </div>
</div>






