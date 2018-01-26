<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/** @var BooklyLite\Backend\Modules\Notifications\Forms\Notifications $form */
use BooklyLite\Lib\Entities\CustomerAppointment;
use BooklyLite\Lib\DataHolders\Notification\Settings;
use BooklyLite\Lib\Entities\Notification;

$id = $notification['id'];
$notification_settings = (array) json_decode( $notification['settings'], true );
?>
<div class="panel panel-default bookly-js-collapse">
    <div class="panel-heading" role="tab">
        <div class="checkbox bookly-margin-remove">
            <label>
                <input name="notification[<?php echo $id ?>][active]" value="0" type="checkbox" checked="checked" class="hidden">
                <input id="<?php echo $id ?>_active" name="notification[<?php echo $id ?>][active]" value="1" type="checkbox" <?php checked( $notification['active'] ) ?>>
                <a href="#collapse_<?php echo $id ?>" class="collapsed panel-title" role="button" data-toggle="collapse" data-parent="#bookly-js-custom-notifications">
                    <?php echo $notification['subject'] ?: __( 'Custom notification', 'bookly' ) ?>
                </a>
            </label>
            <button type="button" class="pull-right btn btn-link bookly-js-delete" style="margin-top: -5px" data-notification_id="<?php echo $id ?>" title="<?php esc_attr_e( 'Delete',  'bookly' ) ?>">
                <span class="ladda-label"><i class="glyphicon glyphicon-trash text-danger"></i></span>
            </button>
        </div>
    </div>
    <div id="collapse_<?php echo $id ?>" class="panel-collapse collapse">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="notification_<?php echo $id ?>_type"><?php _e( 'Type', 'bookly' ) ?></label>
                        <select class="form-control" name="notification[<?php echo $id ?>][type]" id="notification_<?php echo $id ?>_type">
                            <optgroup label="<?php esc_attr_e( 'Event notification', 'bookly' ) ?>">
                                <option value="<?php echo Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED ?>" data-set="<?php echo Settings::SET_AFTER_EVENT ?>" <?php selected( $notification['type'], Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED ) ?> data-to='["customer","staff","admin"]'><?php _e( 'Status changed', 'bookly' ) ?></option>
                                <option value="<?php echo Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED ?>" data-set="<?php echo Settings::SET_AFTER_EVENT ?>" <?php selected( $notification['type'], Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED ) ?> data-to='["customer","staff","admin"]'><?php _e( 'New booking', 'bookly' ) ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e( 'Reminder notification', 'bookly' ) ?>">
                                <option value="<?php echo Notification::TYPE_APPOINTMENT_START_TIME ?>" data-set="<?php echo Settings::SET_EXISTING_EVENT_WITH_DATE_AND_TIME ?>" <?php selected( $notification['type'], Notification::TYPE_APPOINTMENT_START_TIME ) ?> data-to='["customer","staff","admin"]'><?php _e( 'Appointment date and time', 'bookly' ) ?></option>
                                <option value="<?php echo Notification::TYPE_CUSTOMER_BIRTHDAY ?>" data-set="<?php echo Settings::SET_EXISTING_EVENT_WITH_DATE ?>" <?php selected( $notification['type'], Notification::TYPE_CUSTOMER_BIRTHDAY ) ?> data-to='["customer"]'><?php _e( 'Customer\'s birthday', 'bookly' ) ?></option>
                                <option value="<?php echo Notification::TYPE_LAST_CUSTOMER_APPOINTMENT ?>" data-set="<?php echo Settings::SET_EXISTING_EVENT_WITH_DATE_AND_TIME ?>" <?php selected( $notification['type'], Notification::TYPE_LAST_CUSTOMER_APPOINTMENT ) ?> data-to='["customer","staff","admin"]'><?php _e( 'Last client\'s appointment', 'bookly' ) ?></option>
                                <option value="<?php echo Notification::TYPE_STAFF_DAY_AGENDA ?>" data-set="<?php echo Settings::SET_EXISTING_EVENT_WITH_DATE_BEFORE ?>" <?php selected( $notification['type'], Notification::TYPE_STAFF_DAY_AGENDA ) ?> data-to='["staff","admin"]'><?php _e( 'Full day agenda', 'bookly' ) ?></option>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div>
                    <?php
                        $set      = Settings::SET_EXISTING_EVENT_WITH_DATE_AND_TIME;
                        $settings = @$notification_settings[ $set ];
                    ?>
                    <div class="bookly-js-settings bookly-js-<?php echo $set ?>">
                        <?php $name = 'notification[' . $id . '][settings][' . $set . ']' ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="notification_<?php echo $id ?>_status_1"><?php _e( 'With status', 'bookly' ) ?></label>
                                <select class="form-control" name="<?php echo $name ?>[status]" id="notification_<?php echo $id ?>_status_1">
                                    <option value="any"><?php _e( 'Any', 'bookly' ) ?></option>
                                    <?php foreach ( $statuses as $status ) : ?>
                                        <option value="<?php echo $status ?>" <?php selected( $settings['status'] == $status ) ?>><?php echo CustomerAppointment::statusToString( $status ) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="notification_<?php echo $id ?>_send_1"><?php _e( 'Send', 'bookly' ) ?></label>
                            <div class="form-inline bookly-margin-bottom-sm">
                                <div class="form-group">
                                    <label><input type="radio" name="<?php echo $name ?>[option]" value="1" checked id="notification_<?php echo $id ?>_send_1"></label>
                                    <select class="form-control" name="<?php echo $name ?>[offset_hours]">
                                        <?php foreach ( array_merge( range( 1, 24 ), range( 48, 336, 24 ), array( 504, 672 ) ) as $hour ) : ?>
                                            <option value="<?php echo $hour ?>" <?php selected( @$settings['offset_hours'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?></option>
                                        <?php endforeach ?>
                                        <option value="43200" <?php selected( @$settings['offset_hours'], 43200 ) ?>>30 <?php _e( 'days', 'bookly' ) ?></option>
                                    </select>
                                    <select class="form-control" name="<?php echo $name ?>[perform]">
                                        <option value="before"><?php _e( 'before', 'bookly' ) ?></option>
                                        <option value="after"<?php selected( @$settings['perform'] == 'after' ) ?>> <?php _e( 'after', 'bookly' ) ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-inline">
                                <div class="form-group">
                                    <label><input type="radio" name="<?php echo $name ?>[option]" value="2" <?php checked( @$settings['option'] == 2 ) ?>></label>
                                    <select class="form-control" name="<?php echo $name ?>[offset_bidirectional_hours]">
                                        <?php foreach ( array_merge( array( -672, -504 ), range( -336, -24, 24 ) ) as $hour ) : ?>
                                            <option value="<?php echo $hour ?>" <?php selected( @$settings['offset_bidirectional_hours'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( abs( $hour ) * HOUR_IN_SECONDS ) ?> <?php _e( 'before', 'bookly' ) ?></option>
                                        <?php endforeach ?>
                                        <option value="0" <?php selected( @$settings['offset_bidirectional_hours'], 0 ) ?>><?php _e( 'on the same day', 'bookly' ) ?></option>
                                        <?php foreach ( array_merge( range( 24, 336, 24 ), array( 504, 672 ) ) as $hour ) : ?>
                                            <option value="<?php echo $hour ?>" <?php selected( @$settings['offset_bidirectional_hours'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?> <?php _e( 'after', 'bookly' ) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                    <?php _e( 'at', 'bookly' ) ?>
                                    <select class="form-control" name="<?php echo $name ?>[at_hour]">
                                        <?php foreach ( range( 0, 23 ) as $hour ) : ?>
                                            <option value="<?php echo $hour ?>" <?php selected( @$settings['at_hour'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::buildTimeString( $hour * HOUR_IN_SECONDS, false ) ?></option>
                                        <?php endforeach ?>
                                    </select>

                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                        $set      = Settings::SET_EXISTING_EVENT_WITH_DATE;
                        $settings = @$notification_settings[ $set ];
                    ?>
                    <div class="bookly-js-settings bookly-js-<?php echo $set ?>">
                        <?php $name = 'notification[' . $id . '][settings][' . $set . ']' ?>
                        <div class="col-md-6">
                            <label for="notification_<?php echo $id ?>_send_2"><?php _e( 'Send', 'bookly' ) ?></label>
                            <div class="form-inline">
                                <div class="form-group">
                                    <select class="form-control" name="<?php echo $name ?>[offset_bidirectional_hours]">
                                        <?php foreach ( array_merge( array( -672, -504 ), range( -336, -24, 24 ) ) as $hour ) : ?>
                                            <option value="<?php echo $hour ?>" <?php selected( @$settings['offset_bidirectional_hours'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( abs( $hour ) * HOUR_IN_SECONDS ) ?> <?php _e( 'before', 'bookly' ) ?></option>
                                        <?php endforeach ?>
                                        <option value="0" <?php selected( @$settings['offset_bidirectional_hours'], 0 ) ?>><?php _e( 'on the same day', 'bookly' ) ?></option>
                                        <?php foreach ( array_merge( range( 24, 336, 24 ), array( 504, 672 ) ) as $hour ) : ?>
                                            <option value="<?php echo $hour ?>" <?php selected( @$settings['offset_bidirectional_hours'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?> <?php _e( 'after', 'bookly' ) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                    <?php _e( 'at', 'bookly' ) ?>
                                    <select class="form-control" name="<?php echo $name ?>[at_hour]">
                                        <?php foreach ( range( 0, 23 ) as $hour ) : ?>
                                            <option value="<?php echo $hour ?>" <?php selected( @$settings['at_hour'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::buildTimeString( $hour * HOUR_IN_SECONDS, false ) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                        $set      = Settings::SET_EXISTING_EVENT_WITH_DATE_BEFORE;
                        $settings = @$notification_settings[ $set ];
                    ?>
                    <div class="bookly-js-settings bookly-js-<?php echo $set ?>">
                        <?php $name = 'notification[' . $id . '][settings][' . $set . ']' ?>
                        <div class="col-md-6">
                            <label for="notification_<?php echo $id ?>_send_2"><?php _e( 'Send', 'bookly' ) ?></label>
                            <div class="form-inline">
                                <div class="form-group">
                                    <select class="form-control" name="<?php echo $name ?>[offset_bidirectional_hours]">
                                        <?php foreach ( array_merge( array( -672, -504 ), range( -336, -24, 24 ) ) as $hour ) : ?>
                                            <option value="<?php echo $hour ?>" <?php selected( @$settings['offset_bidirectional_hours'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( abs( $hour ) * HOUR_IN_SECONDS ) ?> <?php _e( 'before', 'bookly' ) ?></option>
                                        <?php endforeach ?>
                                        <option value="0" <?php selected( @$settings['offset_bidirectional_hours'], 0 ) ?>><?php _e( 'on the same day', 'bookly' ) ?></option>
                                    </select>
                                    <?php _e( 'at', 'bookly' ) ?>
                                    <select class="form-control" name="<?php echo $name ?>[at_hour]">
                                        <?php foreach ( range( 0, 23 ) as $hour ) : ?>
                                            <option value="<?php echo $hour ?>" <?php selected( @$settings['at_hour'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::buildTimeString( $hour * HOUR_IN_SECONDS, false ) ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $set      = Settings::SET_AFTER_EVENT;
                $settings = @$notification_settings[ $set ];
                ?>
                <div class="bookly-js-settings bookly-js-<?php echo $set ?>">
                    <?php $name = 'notification[' . $id . '][settings][' . $set . ']' ?>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="notification_<?php echo $id ?>_status_1" class="bookly-js-with"><?php _e( 'With status', 'bookly' ) ?></label>
                            <label for="notification_<?php echo $id ?>_status_1" class="bookly-js-to"><?php _e( 'To', 'bookly' ) ?></label>
                            <select class="form-control" name="<?php echo $name ?>[status]" id="notification_<?php echo $id ?>_status_1">
                                <option value="any"><?php _e( 'Any', 'bookly' ) ?></option>
                                <?php foreach ( $statuses as $status ) : ?>
                                    <option value="<?php echo $status ?>" <?php selected( $settings['status'] == $status ) ?>><?php echo CustomerAppointment::statusToString( $status ) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="notification_<?php echo $id ?>_send_1"><?php _e( 'Send', 'bookly' ) ?></label>
                        <div class="form-inline bookly-margin-bottom-sm">
                            <div class="form-group">
                                <label><input type="radio" name="<?php echo $name ?>[option]" value="1" checked></label>  <?php _e( 'Instantly', 'bookly' ) ?>
                            </div>
                        </div>

                        <div class="form-inline bookly-margin-bottom-sm">
                            <div class="form-group">
                                <label><input type="radio" name="<?php echo $name ?>[option]" value="2" <?php checked( @$settings['option'] == 2 ) ?>></label>
                                <select class="form-control" name="<?php echo $name ?>[offset_hours]">
                                    <?php foreach ( array_merge( range( 1, 24 ), range( 48, 336, 24 ), array( 504, 672 ) ) as $hour ) : ?>
                                        <option value="<?php echo $hour ?>" <?php selected( @$settings['offset_hours'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?> <?php _e( 'after', 'bookly' ) ?></option>
                                    <?php endforeach ?>
                                </select>
                                <input type="hidden" name="<?php echo $name ?>[perform]" value="after">
                            </div>
                        </div>

                        <div class="form-inline">
                            <div class="form-group">
                                <label><input type="radio" name="<?php echo $name ?>[option]" value="3" <?php checked( @$settings['option'] == 3 ) ?>></label>
                                <select class="form-control" name="<?php echo $name ?>[offset_bidirectional_hours]">
                                    <option value="0"><?php _e( 'on the same day', 'bookly' ) ?></option>
                                    <?php foreach ( array_merge( range( 24, 336, 24 ), array( 504, 672 ) ) as $hour ) : ?>
                                        <option value="<?php echo $hour ?>" <?php selected( @$settings['offset_bidirectional_hours'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?> <?php _e( 'after', 'bookly' ) ?></option>
                                    <?php endforeach ?>
                                </select>
                                <?php _e( 'at', 'bookly' ) ?>
                                <select class="form-control" name="<?php echo $name ?>[at_hour]">
                                    <?php foreach ( range( 0, 23 ) as $hour ) : ?>
                                        <option value="<?php echo $hour ?>" <?php selected( @$settings['at_hour'], $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::buildTimeString( $hour * HOUR_IN_SECONDS, false ) ?></option>
                                    <?php endforeach ?>
                                </select>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="notification_<?php echo $id ?>_subject"><?php _e( 'Notification name', 'bookly' ) ?></label>
                        <input type="text" class="form-control" id="notification_<?php echo $id ?>_subject" name="notification[<?php echo $id ?>][subject]" value="<?php echo esc_attr( $notification['subject'] ) ?>" />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php _e( 'Recipient', 'bookly' ) ?></label>
                        <br>
                        <label class="checkbox-inline">
                            <input type="hidden" name="notification[<?php echo $id ?>][to_customer]" value="0">
                            <input type="checkbox" name="notification[<?php echo $id ?>][to_customer]" value="1" <?php checked( $notification['to_customer'] ) ?>> <?php _e( 'Client', 'bookly' ) ?>
                        </label>
                        <label class="checkbox-inline">
                            <input type="hidden" name="notification[<?php echo $id ?>][to_staff]" value="0">
                            <input type="checkbox" name="notification[<?php echo $id ?>][to_staff]" value="1" <?php checked( $notification['to_staff'] ) ?>> <?php _e( 'Staff', 'bookly' ) ?>
                        </label>
                        <label class="checkbox-inline">
                            <input type="hidden" name="notification[<?php echo $id ?>][to_admin]" value="0">
                            <input type="checkbox" name="notification[<?php echo $id ?>][to_admin]" value="1" <?php checked( $notification['to_admin'] ) ?>> <?php _e( 'Administrators', 'bookly' ) ?>
                        </label>
                    </div>
                </div>
            </div>

            <?php $form->renderEditor( $id ) ?>

            <div class="form-group">
                <label><?php _e( 'Codes', 'bookly' ) ?></label>
                <?php foreach ( Notification::getCustomNotificationTypes() as $notification_type ) :
                    $form->renderCodes( $notification_type );
                endforeach ?>
            </div>
        </div>
    </div>
</div>