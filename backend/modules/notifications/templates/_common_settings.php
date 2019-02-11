<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Selects;

$bookly_email_sender_name = get_option( 'bookly_email_sender_name' ) == '' ?
    get_option( 'blogname' ) : get_option( 'bookly_email_sender_name' );
$bookly_email_sender      = get_option( 'bookly_email_sender' ) == '' ?
    get_option( 'admin_email' ) : get_option( 'bookly_email_sender' );
?>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="sender_name<?php echo $tail ?>"><?php esc_html_e( 'Sender name', 'bookly' ) ?></label>
            <input id="sender_name<?php echo $tail ?>" name="bookly_email_sender_name" class="form-control" type="text" value="<?php echo esc_attr( $bookly_email_sender_name ) ?>">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label for="sender_email<?php echo $tail ?>"><?php esc_html_e( 'Sender email', 'bookly' ) ?></label>
            <input id="sender_email<?php echo $tail ?>" name="bookly_email_sender" class="form-control bookly-sender" type="text" value="<?php echo esc_attr( $bookly_email_sender ) ?>">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?php Selects::renderRadios( 'bookly_email_send_as', __( 'Send emails as', 'bookly' ), __( 'HTML allows formatting, colors, fonts, positioning, etc. With Text you must use Text mode of rich-text editors below. On some servers only text emails are sent successfully.', 'bookly' ),
            array( array( 'html', __( 'HTML', 'bookly' ) ), array( 'text', __( 'Text', 'bookly' ) ) )
        ) ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?php Selects::renderRadios( 'bookly_email_reply_to_customers', __( 'Reply directly to customers', 'bookly' ), __( 'If this option is enabled then the email address of the customer is used as a sender email address for notifications sent to staff members and administrators.', 'bookly' ) ) ?>
    </div>
</div>