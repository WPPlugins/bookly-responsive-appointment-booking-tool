<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;

$start_of_week = (int) get_option( 'start_of_week' );
$form = new Bookly\Backend\Modules\Settings\Forms\BusinessHours()
?>
<p>
    <?php _e( 'Please note, the business hours below work as a template for all new staff members. To render a list of available time slots the system takes into account only staff members\' schedule, not the company business hours. Be sure to check the schedule of your staff members if you have some unexpected behavior of the booking system.', 'bookly' ) ?>
</p>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'business_hours' ) ) ?>" id="business-hours">
    <?php for ( $i = 0; $i < 7; $i ++ ) :
        $day = strtolower( Bookly\Lib\Utils\DateTime::getWeekDayByNumber( ( $i + $start_of_week ) % 7 ) );
        ?>
        <div class="row">
            <div class="form-group col-sm-7 col-xs-8">
                <label><?php _e( ucfirst( $day ) ) ?> </label>
                <div class="bookly-flexbox">
                    <div class="bookly-flex-cell" style="width: 45%">
                        <?php echo $form->renderField( 'bookly_bh_' . $day ) ?>
                    </div>
                    <div class="bookly-flex-cell text-center" style="width: 10%">
                        <div class="bookly-margin-horizontal-sm bookly-hide-on-off"><?php _e( 'to', 'bookly' ) ?></div>
                    </div>
                    <div class="bookly-flex-cell" style="width: 45%">
                        <?php echo $form->renderField( 'bookly_bh_' . $day, false ) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endfor ?>

    <div class="panel-footer">
        <?php Inputs::renderCsrf() ?>
        <?php Buttons::renderSubmit() ?>
        <?php Buttons::renderReset( 'bookly-hours-reset' ) ?>
    </div>
</form>