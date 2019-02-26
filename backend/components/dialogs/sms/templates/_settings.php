<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Entities\CustomerAppointment;
$statuses              = \Bookly\Lib\Entities\CustomerAppointment::getStatuses();
$service_dropdown_data = \Bookly\Lib\Utils\Common::getServiceDataForDropDown( 's.type <> "package"' );
?>
<div class="bookly-js-statuses-container">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="notification_status"><?php esc_attr_e( 'Appointment status', 'bookly' ) ?></label>
                <p class="help-block"><?php esc_html_e( 'Select what status an appointment should have for the notification to be sent.', 'bookly' ) ?></p>
                <select class="form-control" name="notification[settings][status]" id="notification_status">
                    <option value="any"><?php esc_attr_e( 'Any', 'bookly' ) ?></option>
                    <?php foreach ( $statuses as $status ) : ?>
                        <option value="<?php echo $status ?>"><?php echo CustomerAppointment::statusToString( $status ) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
    </div>
</div>
<div class="bookly-js-services-container">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group"><label><?php esc_html_e( 'Services', 'bookly' ) ?></label>
                <p class="help-block"><?php esc_html_e( 'Choose whether notification should be sent for specific services only or not.', 'bookly' ) ?></p>
                <div class="radio"><label><input type="radio" name="notification[settings][services][any]" value="any" checked="checked"> <?php esc_html_e( 'Any', 'bookly' ) ?></label></div>
                <div class="form-inline">
                    <div class="form-group">
                        <label><input type="radio" name="notification[settings][services][any]" value="selected"></label>
                        <div class="form-group">
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
                                                <li data-input-name="notification[settings][services][ids][]"
                                                    data-value="<?php echo $service['id'] ?>"
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
            </div>
        </div>
    </div>
</div>

<div class="row bookly-js-offset bookly-js-offset-exists">
    <div class="col-md-12">
        <div class="form-group bookly-margin-bottom-remove">
            <label><?php esc_attr_e( 'Send', 'bookly' ) ?></label>
        </div>
    </div>
</div>

<div class="bookly-js-offset bookly-js-offset-bidirectional">
    <div class="row bookly-js-offsets bookly-js-relative bookly-js-full">
        <div class="col-md-12 bookly-margin-bottom-md">
            <div class="form-inline">
                <div class="form-group">
                    <label><input type="radio" name="notification[settings][option]" value="1" checked></label>
                    <select class="form-control" name="notification[settings][offset_hours]">
                        <?php foreach ( array_merge( range( 1, 24 ), range( 48, 336, 24 ), array( 504, 672 ) ) as $hour ) : ?>
                            <option value="<?php echo $hour ?>"><?php echo \Bookly\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?></option>
                        <?php endforeach ?>
                        <option value="43200">30 <?php esc_attr_e( 'days', 'bookly' ) ?></option>
                    </select>
                    <select class="form-control" name="notification[settings][perform]">
                        <option value="before"><?php esc_attr_e( 'before', 'bookly' ) ?></option>
                        <option value="after"><?php esc_attr_e( 'after', 'bookly' ) ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row bookly-js-offsets bookly-js-at-time bookly-js-full">
        <div class="col-md-12">
            <div class="form-inline bookly-margin-bottom-sm">
                <div class="form-group">
                    <label><input type="radio" name="notification[settings][option]" value="2"></label>
                    <select class="form-control" name="notification[settings][offset_bidirectional_hours]">
                        <?php foreach ( array_merge( array( - 672, - 504 ), range( - 336, - 24, 24 ) ) as $hour ) : ?>
                            <option value="<?php echo $hour ?>"><?php echo \Bookly\Lib\Utils\DateTime::secondsToInterval( abs( $hour ) * HOUR_IN_SECONDS ) ?>&nbsp;<?php esc_attr_e( 'before', 'bookly' ) ?></option>
                        <?php endforeach ?>
                        <option value="0" selected><?php esc_attr_e( 'on the same day', 'bookly' ) ?></option>
                        <?php foreach ( array_merge( range( 24, 336, 24 ), array( 504, 672 ) ) as $hour ) : ?>
                            <option value="<?php echo $hour ?>"><?php echo \Bookly\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?>&nbsp;<?php esc_attr_e( 'after', 'bookly' ) ?></option>
                        <?php endforeach ?>
                    </select>
                    <?php esc_attr_e( 'at', 'bookly' ) ?>
                    <select class="form-control" name="notification[settings][at_hour]">
                        <?php foreach ( range( 0, 23 ) as $hour ) : ?>
                            <option value="<?php echo $hour ?>"><?php echo \Bookly\Lib\Utils\DateTime::buildTimeString( $hour * HOUR_IN_SECONDS, false ) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row bookly-js-offset bookly-js-offset-before">
    <div class="col-md-12">
        <div class="form-inline bookly-margin-bottom-sm">
            <div class="form-group">
                <label><input type="radio" name="notification[settings][option]" value="3"></label>
                <select class="form-control" name="notification[settings][offset_before_hours]" id="notification_send_2">
                    <?php foreach ( array_merge( array( - 672, - 504 ), range( - 336, - 24, 24 ) ) as $hour ) : ?>
                        <option value="<?php echo $hour ?>"><?php echo \Bookly\Lib\Utils\DateTime::secondsToInterval( abs( $hour ) * HOUR_IN_SECONDS ) ?>&nbsp;<?php esc_attr_e( 'before', 'bookly' ) ?></option>
                    <?php endforeach ?>
                    <option value="0" selected><?php esc_attr_e( 'on the same day', 'bookly' ) ?></option>
                </select>
                <?php esc_attr_e( 'at', 'bookly' ) ?>
                <select class="form-control" name="notification[settings][before_at_hour]">
                    <?php foreach ( range( 0, 23 ) as $hour ) : ?>
                        <option value="<?php echo $hour ?>"><?php echo \Bookly\Lib\Utils\DateTime::buildTimeString( $hour * HOUR_IN_SECONDS, false ) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
    </div>
</div>