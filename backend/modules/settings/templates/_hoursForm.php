<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $start_of_week = (int) get_option( 'start_of_week' );
?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'type', '_hours' ) ) ?>" class="ab-settings-form" id="business-hours">
    <?php $form = new AB_BusinessHoursForm() ?>
    <table class="form-inline">
        <tbody>
        <?php for( $i = 0; $i < 7; $i++):
            $day = strtolower( AB_DateTimeUtils::getWeekDayByNumber( ( $i + $start_of_week ) % 7 ) );
            ?>
            <tr>
                <td>
                    <label><?php _e( ucfirst( $day ) ) ?> </label>
                </td>
                <td>
                    <?php echo $form->renderField( 'ab_settings_' . $day ) ?>
                    <span>&nbsp;<?php _e( 'to', 'bookly' ) ?>&nbsp;</span>
                    <?php echo $form->renderField( 'ab_settings_' . $day, false ) ?>
                </td>
            </tr>
        <?php endfor ?>
        </tbody>
        <tr>
            <td></td>
            <td>
                <?php AB_Utils::submitButton() ?>
                <?php AB_Utils::resetButton( 'ab-hours-reset' ) ?>
            </td>
        </tr>
    </table>
</form>