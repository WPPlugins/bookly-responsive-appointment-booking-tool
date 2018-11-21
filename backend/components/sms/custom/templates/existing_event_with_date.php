<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\DataHolders\Notification\Settings;

$set  = Settings::SET_EXISTING_EVENT_WITH_DATE;
$name = 'notification[' . $id . '][settings][' . $set . ']';
?>
<div class="bookly-js-settings bookly-js-<?php echo $set ?>">
    <div class="col-md-6">
        <label for="notification_<?php echo ++$unique ?>_send_2"><?php esc_attr_e( 'Send', 'bookly' ) ?></label>
        <div class="form-inline">
            <div class="form-group">
                <select class="form-control" name="<?php echo $name ?>[offset_bidirectional_hours]" id="notification_<?php echo $unique ?>_send_2">
                    <?php foreach ( array_merge( array( -672, -504 ), range( -336, -24, 24 ) ) as $hour ) : ?>
                        <option value="<?php echo $hour ?>" <?php selected( @$settings['offset_bidirectional_hours'], $hour ) ?>><?php echo \Bookly\Lib\Utils\DateTime::secondsToInterval( abs( $hour ) * HOUR_IN_SECONDS ) ?>&nbsp;<?php esc_attr_e( 'before', 'bookly' ) ?></option>
                    <?php endforeach ?>
                    <option value="0" <?php selected( @$settings['offset_bidirectional_hours'], 0 ) ?>><?php esc_attr_e( 'on the same day', 'bookly' ) ?></option>
                    <?php foreach ( array_merge( range( 24, 336, 24 ), array( 504, 672 ) ) as $hour ) : ?>
                        <option value="<?php echo $hour ?>" <?php selected( @$settings['offset_bidirectional_hours'], $hour ) ?>><?php echo \Bookly\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?>&nbsp;<?php esc_attr_e( 'after', 'bookly' ) ?></option>
                    <?php endforeach ?>
                </select>
                <?php esc_attr_e( 'at', 'bookly' ) ?>
                <select class="form-control" name="<?php echo $name ?>[at_hour]">
                    <?php foreach ( range( 0, 23 ) as $hour ) : ?>
                        <option value="<?php echo $hour ?>" <?php selected( @$settings['at_hour'], $hour ) ?>><?php echo \Bookly\Lib\Utils\DateTime::buildTimeString( $hour * HOUR_IN_SECONDS, false ) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
    </div>
</div>
<?php $self::putInCache( 'unique', ++$unique ) ?>