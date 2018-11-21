<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Settings;
?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'calendar' ) ) ?>">
    <?php if ( Bookly\Lib\Config::groupBookingActive() ) : ?>
        <div class="form-group"><label for="bookly_appointment_participants"><?php _e( 'Calendar', 'bookly' ) ?></label>
            <p class="help-block"><?php _e( 'Set order of the fields in calendar', 'bookly' ) ?></p>
            <select class="form-control" id="bookly_appointment_participants">
                <option value="bookly_cal_one_participant"><?php _e( 'Appointment with one participant', 'bookly' ) ?></option>
                <option value="bookly_cal_many_participants"><?php _e( 'Appointment with many participants', 'bookly' ) ?></option>
            </select>
        </div>
    <?php else : ?>
        <div class="form-group"><label for="bookly_appointment_participants"><?php _e( 'Calendar', 'bookly' ) ?></label>
            <p class="help-block"><?php _e( 'Set order of the fields in calendar', 'bookly' ) ?></p>
        </div>
        <input id="bookly_appointment_participants" type="hidden" name="bookly_appointment_participants" value="bookly_cal_one_participant">
    <?php endif ?>
    <div class="form-group" id="bookly_cal_one_participant">
        <?php Settings\Inputs::renderTextArea( 'bookly_cal_one_participant', '' ) ?>
        <?php $self::renderTemplate( '_calendar_codes', array( 'participants' => 'one' ) ) ?>
    </div>
    <div class="form-group" id="bookly_cal_many_participants">
        <?php Settings\Inputs::renderTextArea( 'bookly_cal_many_participants', '' ) ?>
        <?php $self::renderTemplate( '_calendar_codes', array( 'participants' => 'many' ) ) ?>
    </div>

    <div class="panel-footer">
        <?php Inputs::renderCsrf() ?>
        <?php Buttons::renderSubmit() ?>
        <?php Buttons::renderReset() ?>
    </div>
</form>