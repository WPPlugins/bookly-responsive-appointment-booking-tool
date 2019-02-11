<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Entities\Notification;
$codes = new \Bookly\Backend\Modules\Notifications\Lib\Codes( 'sms' )
?>
<div class="form-group bookly-js-codes-container bookly-max-height-md">
    <label><?php esc_attr_e( 'Codes', 'bookly' ) ?></label>
    <?php foreach ( Notification::getTypes( 'sms' ) as $notification_type ) :
        if ( in_array( $notification_type, array(
            Notification::TYPE_NEW_BOOKING_RECURRING,
            Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED_RECURRING,
        ) ) ) {
            $codes->render( $notification_type, true );
        } else {
            $codes->render( $notification_type );
        }
    endforeach ?>
</div>