<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $start_of_week = (int) get_option( 'start_of_week' );
    $form = new \BooklyLite\Backend\Modules\Settings\Forms\BusinessHours()
?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'business_hours' ) ) ?>" class="ab-settings-form" id="business-hours">
    <?php for ( $i = 0; $i < 7; $i ++ ) :
        $day = strtolower( \BooklyLite\Lib\Utils\DateTime::getWeekDayByNumber( ( $i + $start_of_week ) % 7 ) );
        ?>
        <div class="row">
            <div class="form-group col-sm-7 col-xs-8">
                <label><?php _e( ucfirst( $day ) ) ?> </label>
                <div class="bookly-flexbox">
                    <div class="bookly-flex-cell" style="width: 45%">
                        <?php echo $form->renderField( 'ab_settings_' . $day ) ?>
                    </div>
                    <div class="bookly-flex-cell text-center" style="width: 10%">
                        <div class="bookly-margin-horizontal-sm hide-on-non-working-day"><?php _e( 'to', 'bookly' ) ?></div>
                    </div>
                    <div class="bookly-flex-cell" style="width: 45%">
                        <?php echo $form->renderField( 'ab_settings_' . $day, false ) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endfor ?>

    <div class="panel-footer">
        <?php \BooklyLite\Lib\Utils\Common::submitButton() ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton( 'ab-hours-reset' ) ?>
    </div>
</form>