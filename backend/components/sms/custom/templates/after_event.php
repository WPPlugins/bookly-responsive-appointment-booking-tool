<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\DataHolders\Notification\Settings;

$set  = Settings::SET_AFTER_EVENT;
$name = 'notification[' . $id . '][settings][' . $set . ']';
?>
<div class="bookly-js-settings bookly-js-<?php echo $set ?>">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="notification_<?php echo ++ $unique ?>_status_1" class="bookly-js-with"><?php esc_attr_e( 'With status', 'bookly' ) ?></label>
                <label for="notification_<?php echo $unique ?>_status_1" class="bookly-js-to"><?php esc_attr_e( 'To', 'bookly' ) ?></label>
                <select class="form-control" name="<?php echo $name ?>[status]" id="notification_<?php echo $unique ?>_status_1">
                    <option value="any"><?php esc_attr_e( 'Any', 'bookly' ) ?></option>
                    <?php foreach ( $statuses as $status ) : ?>
                        <option value="<?php echo $status ?>" <?php selected( $settings['status'] == $status ) ?>><?php echo CustomerAppointment::statusToString( $status ) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="col-md-5">
            <label><?php esc_attr_e( 'For service', 'bookly' ) ?></label>
            <div class="form-inline">
                <input type="hidden" name="<?php echo $name ?>[services][any]" value="0">
                <input type="checkbox" name="<?php echo $name ?>[services][any]" value="1" <?php checked( @$settings['services']['any'] ) ?>> <span class="bookly-checkbox-text"><?php esc_html_e( 'Any', 'bookly' ) ?></span>
                <ul class="bookly-js-services"
                    data-icon-class="glyphicon glyphicon-tag"
                    data-txt-select-all="<?php esc_attr_e( 'All services', 'bookly' ) ?>"
                    data-txt-all-selected="<?php esc_attr_e( 'All services', 'bookly' ) ?>"
                    data-txt-nothing-selected="<?php esc_attr_e( 'No service selected', 'bookly' ) ?>"
                >
                    <?php foreach ( $service_dropdown_data as $category_id => $category ): ?>
                        <li<?php if ( ! $category_id ) : ?> data-flatten-if-single<?php endif ?>><?php echo esc_html( $category['name'] ) ?>
                            <ul>
                                <?php foreach ( $category['items'] as $service ) : ?>
                                    <li
                                        data-input-name="<?php echo $name ?>[services][ids][]"
                                        data-value="<?php echo $service['id'] ?>"
                                        data-selected="<?php echo (int) isset( $settings['services']['ids'] ) && in_array( $service['id'], @$settings['services']['ids'] ) ?>"
                                        >
                                        <?php echo esc_html( $service['title'] ) ?>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 bookly-margin-left-sm bookly-margin-bottom-sm">
            <label for="notification_<?php echo ++ $unique ?>_send_1"><?php esc_attr_e( 'Send', 'bookly' ) ?></label>
            <div class="form-inline bookly-margin-bottom-sm">
                <div class="form-group">
                    <label><input type="radio" name="<?php echo $name ?>[option]" value="1" checked></label> <?php esc_attr_e( 'Instantly', 'bookly' ) ?>
                </div>
            </div>

            <div class="form-inline bookly-margin-bottom-sm">
                <div class="form-group">
                    <label><input type="radio" name="<?php echo $name ?>[option]" value="2" <?php checked( @$settings['option'] == 2 ) ?>></label>
                    <select class="form-control" name="<?php echo $name ?>[offset_hours]">
                        <?php foreach ( array_merge( range( 1, 24 ), range( 48, 336, 24 ), array( 504, 672 ) ) as $hour ) : ?>
                            <option value="<?php echo $hour ?>" <?php selected( @$settings['offset_hours'], $hour ) ?>><?php echo \Bookly\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?>&nbsp;<?php esc_attr_e( 'after', 'bookly' ) ?></option>
                        <?php endforeach ?>
                    </select>
                    <input type="hidden" name="<?php echo $name ?>[perform]" value="after">
                </div>
            </div>

            <div class="form-inline">
                <div class="form-group">
                    <label><input type="radio" name="<?php echo $name ?>[option]" value="3" <?php checked( @$settings['option'] == 3 ) ?>></label>
                    <select class="form-control" name="<?php echo $name ?>[offset_bidirectional_hours]">
                        <option value="0"><?php esc_attr_e( 'on the same day', 'bookly' ) ?></option>
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
</div>
<?php $self::putInCache( 'unique', ++$unique ) ?>