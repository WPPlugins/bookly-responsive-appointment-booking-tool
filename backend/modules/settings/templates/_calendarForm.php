<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'calendar' ) ) ?>">
    <div class="form-group"><label for="bookly_appointment_participants"><?php _e( 'Calendar', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set order of the fields in calendar for', 'bookly' ) ?></p>
    </div>
    <input id="bookly_appointment_participants" type="hidden" name="bookly_appointment_participants" value="bookly_cal_one_participant">
    <div class="form-group" id="bookly_cal_one_participant">
        <textarea class="form-control" rows="9" name="bookly_cal_one_participant" placeholder="<?php _e( 'Enter a value', 'bookly' ) ?>"><?php echo esc_textarea( get_option( 'bookly_cal_one_participant' ) ) ?></textarea><br/>
        <?php $this->render( '_calendar_codes', array( 'participants' => 'one' ) ) ?>
    </div>
    <div class="form-group" id="bookly_cal_many_participants">
        <textarea class="form-control" rows="9" name="bookly_cal_many_participants" placeholder="<?php _e( 'Enter a value', 'bookly' ) ?>"><?php echo esc_textarea( get_option( 'bookly_cal_many_participants' ) ) ?></textarea><br/>
        <?php $this->render( '_calendar_codes', array( 'participants' => 'many' ) ) ?>
    </div>

    <div class="panel-footer">
        <?php \BooklyLite\Lib\Utils\Common::csrf() ?>
        <?php \BooklyLite\Lib\Utils\Common::submitButton() ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>
    </div>
</form>