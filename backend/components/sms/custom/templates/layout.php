<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/** @var Bookly\Backend\Modules\Notifications\Forms\Notifications $form */
use Bookly\Lib\DataHolders\Notification\Settings;
use Bookly\Lib\Entities\Notification;
use Bookly\Backend\Modules\Notifications\Proxy as NotificationProxy;
use Bookly\Backend\Components\Sms\Custom;

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
            <button type="button" class="pull-right btn btn-link bookly-js-delete" style="margin-top: -5px" data-notification_id="<?php echo $id ?>" title="<?php esc_attr_e( 'Delete',  'bookly' ) ?>" data-style="zoom-in" data-spinner-size="20" data-spinner-color="#333">
                <span class="ladda-label"><i class="glyphicon glyphicon-trash text-danger"></i></span>
            </button>
        </div>
    </div>
    <div id="collapse_<?php echo $id ?>" class="panel-collapse collapse">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="notification_<?php echo $unique ?>_type"><?php esc_attr_e( 'Type', 'bookly' ) ?></label>
                        <select class="form-control" name="notification[<?php echo $id ?>][type]" id="notification_<?php echo $unique ?>_type">
                            <optgroup label="<?php esc_attr_e( 'Event notification', 'bookly' ) ?>">
                                <option
                                        value="<?php echo Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED ?>"
                                        data-set="<?php echo Settings::SET_AFTER_EVENT ?>"
                                        data-to='["customer","staff","admin"]'
                                        data-attach-show='["ics","invoice"]'
                                    <?php selected( $notification['type'], Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED ) ?>><?php esc_attr_e( 'Status changed', 'bookly' ) ?></option>
                                <option
                                        value="<?php echo Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED ?>"
                                        data-set="<?php echo Settings::SET_AFTER_EVENT ?>"
                                        data-to='["customer","staff","admin"]'
                                        data-attach-show='["ics","invoice"]'
                                    <?php selected( $notification['type'], Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED ) ?> ><?php esc_attr_e( 'New booking', 'bookly' ) ?></option>
                            </optgroup>
                            <optgroup label="<?php esc_attr_e( 'Reminder notification', 'bookly' ) ?>">
                                <option
                                        value="<?php echo Notification::TYPE_APPOINTMENT_START_TIME ?>"
                                        data-set="<?php echo Settings::SET_EXISTING_EVENT_WITH_DATE_AND_TIME ?>"
                                        data-to='["customer","staff","admin"]'
                                        data-attach-show='["ics"]'
                                    <?php selected( $notification['type'], Notification::TYPE_APPOINTMENT_START_TIME ) ?>><?php esc_attr_e( 'Appointment date and time', 'bookly' ) ?></option>
                                <option
                                        value="<?php echo Notification::TYPE_CUSTOMER_BIRTHDAY ?>"
                                        data-set="<?php echo Settings::SET_EXISTING_EVENT_WITH_DATE ?>"
                                        data-to='["customer"]'
                                        data-attach-show='[]'
                                    <?php selected( $notification['type'], Notification::TYPE_CUSTOMER_BIRTHDAY ) ?>><?php esc_attr_e( 'Customer\'s birthday', 'bookly' ) ?></option>
                                <option
                                        value="<?php echo Notification::TYPE_LAST_CUSTOMER_APPOINTMENT ?>"
                                        data-set="<?php echo Settings::SET_EXISTING_EVENT_WITH_DATE_AND_TIME ?>"
                                        data-to='["customer","staff","admin"]'
                                        data-attach-show='["ics"]'
                                    <?php selected( $notification['type'], Notification::TYPE_LAST_CUSTOMER_APPOINTMENT ) ?>><?php esc_attr_e( 'Last client\'s appointment', 'bookly' ) ?></option>
                                <option
                                        value="<?php echo Notification::TYPE_STAFF_DAY_AGENDA ?>"
                                        data-set="<?php echo Settings::SET_EXISTING_EVENT_WITH_DATE_BEFORE ?>"
                                        data-to='["staff","admin"]'
                                        data-attach-show='[]'
                                    <?php selected( $notification['type'], Notification::TYPE_STAFF_DAY_AGENDA ) ?>><?php esc_attr_e( 'Full day agenda', 'bookly' ) ?></option>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <?php foreach ( array( Settings::SET_EXISTING_EVENT_WITH_DATE_AND_TIME, Settings::SET_EXISTING_EVENT_WITH_DATE, Settings::SET_EXISTING_EVENT_WITH_DATE_BEFORE, Settings::SET_AFTER_EVENT ) as $set ) {
                    $settings = array_key_exists( $set, $notification_settings ) ? $notification_settings[ $set ] : array();
                    Custom\Notification::renderSet( $set, $id, $settings );
                }
                $unique = $self::getFromCache( 'unique' ) ?>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="notification_<?php echo ++$unique ?>_subject"><?php esc_attr_e( 'Subject', 'bookly' ) ?></label>
                        <input type="text" class="form-control" id="notification_<?php echo $unique ?>_subject" name="notification[<?php echo $id ?>][subject]" value="<?php echo esc_attr( $notification['subject'] ) ?>" />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php esc_attr_e( 'Recipient', 'bookly' ) ?></label>
                        <br>
                        <label class="checkbox-inline">
                            <input type="hidden" name="notification[<?php echo $id ?>][to_customer]" value="0">
                            <input type="checkbox" name="notification[<?php echo $id ?>][to_customer]" value="1"<?php checked( $notification['to_customer'] ) ?> /> <?php esc_attr_e( 'Client', 'bookly' ) ?>
                        </label>
                        <label class="checkbox-inline">
                            <input type="hidden" name="notification[<?php echo $id ?>][to_staff]" value="0">
                            <input type="checkbox" name="notification[<?php echo $id ?>][to_staff]" value="1"<?php checked( $notification['to_staff'] ) ?> /> <?php esc_attr_e( 'Staff', 'bookly' ) ?>
                        </label>
                        <label class="checkbox-inline">
                            <input type="hidden" name="notification[<?php echo $id ?>][to_admin]" value="0">
                            <input type="checkbox" name="notification[<?php echo $id ?>][to_admin]" value="1"<?php checked( $notification['to_admin'] ) ?> /> <?php esc_attr_e( 'Administrators', 'bookly' ) ?>
                        </label>
                    </div>
                </div>
            </div>

            <?php $form->renderEditor( $id ) ?>

            <?php NotificationProxy\Invoices::renderAttach( $notification ) ?>
            <div class="form-group">
                <label><?php esc_attr_e( 'Codes', 'bookly' ) ?></label>
                <?php foreach ( Notification::getCustomNotificationTypes() as $notification_type ) :
                    $form->renderCodes( $notification_type );
                endforeach ?>
            </div>
        </div>
    </div>
</div>
<?php $self::putInCache( 'unique', ++$unique ) ?>