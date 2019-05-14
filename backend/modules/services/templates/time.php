<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Utils\DateTime;
use Bookly\Lib\Entities\Service;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Modules\Services\Proxy;
?>
<div class=" bookly-js-service-time-container">
    <div class="form-group">
        <label for="bookly-service-duration">
            <?php esc_html_e( 'Duration', 'bookly' ) ?>
        </label>
        <?php Proxy\CustomDuration::renderServiceDurationHelp() ?>
        <?php
        $options = Common::getDurationSelectOptions( $service['duration'] );
        $options = Proxy\CustomDuration::prepareServiceDurationOptions( $options, $service );
        ?>
        <select id="bookly-service-duration" class="bookly-js-duration form-control" name="duration">
            <?php foreach ( $options as $option ): ?>
                <option value="<?php echo $option['value'] ?>" <?php echo $option['selected'] ?>><?php echo $option['label'] ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <?php Proxy\CustomDuration::renderServiceDurationFields( $service ); ?>
    <div class="bookly-js-start-time-info"<?php if ( $service['duration'] < DAY_IN_SECONDS ) : ?> style="display:none;"<?php endif ?>>
        <div class="form-group bookly-js-service bookly-js-service-simple">
            <label for="bookly-service-start-time-info"><?php esc_html_e( 'Start and end times of the appointment', 'bookly' ) ?></label>
            <p class="help-block"><?php esc_html_e( 'Allows to set the start and end times for an appointment for services with the duration of 1 day or longer. This time will be displayed in notifications to customers, backend calendar and codes for booking form.', 'bookly' ) ?></p>
            <div class="row">
                <div class="col-xs-6">
                    <input id="bookly-service-start-time-info" class="form-control" type="text" name="start_time_info" value="<?php echo esc_attr( $service['start_time_info'] ) ?>"/>
                </div>
                <div class="col-xs-6">
                    <input class="form-control" type="text" name="end_time_info" value="<?php echo esc_attr( $service['end_time_info'] ) ?>"/>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group bookly-js-service bookly-js-service-simple">
        <label for="bookly-service-slot-length">
            <?php esc_html_e( 'Time slot length', 'bookly' ) ?>
        </label>
        <p class="help-block"><?php esc_html_e( 'The time interval which is used as a step when building all time slots for the service at the Time step. The setting overrides global settings in Settings → General. Use Default to apply global settings.', 'bookly' ) ?></p>
        <select id="bookly-service-slot-length" class="form-control" name="slot_length">
            <option value="<?php echo Service::SLOT_LENGTH_DEFAULT ?>"<?php selected( $service['slot_length'], Service::SLOT_LENGTH_DEFAULT ) ?>><?php esc_html_e( 'Default', 'bookly' ) ?></option>
            <option value="<?php echo Service::SLOT_LENGTH_AS_SERVICE_DURATION ?>"<?php selected( $service['slot_length'], Service::SLOT_LENGTH_AS_SERVICE_DURATION ) ?>><?php esc_html_e( 'Slot length as service duration', 'bookly' ) ?></option>
            <?php foreach ( array( 300, 600, 720, 900, 1200, 1800, 2700, 3600, 5400, 7200, 10800, 14400, 21600 ) as $duration ): ?>
                <option value="<?php echo $duration ?>"<?php selected( $service['slot_length'], $duration ) ?>><?php echo esc_html( DateTime::secondsToInterval( $duration ) ) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <?php Proxy\Pro::renderPadding( $service ) ?>
    <?php Proxy\Tasks::renderSubForm( $service ) ?>
</div>