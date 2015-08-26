<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<button class="ab-available-day" value="<?php echo esc_attr( $group ) ?>">
    <?php echo date_i18n( ( $is_whole_day_service ? 'M' : 'D, M d' ), strtotime( $group ) ) ?>
</button>
<?php foreach ( $slots as $client_timestamp => $slot ): ?>
    <button <?php disabled( $slot['booked'], true ) ?>
        data-staff_id="<?php echo esc_attr( $slot['staff_id'] ) ?>"
        data-group="<?php echo esc_attr( $group ) ?>"
        class="ab-available-hour ladda-button<?php if ( $slot['booked'] ) echo ' booked' ?>"
        value="<?php echo esc_attr( date( $is_whole_day_service ? 'Y-m-d' : 'Y-m-d H:i:s', $slot['timestamp'] ) ) ?>"
        data-style="zoom-in"
        data-spinner-color="#333"
        data-spinner-size="40"
        >
        <span class="ladda-label"><i class="ab-hour-icon"><span></span></i>
            <?php echo date_i18n( ( $is_whole_day_service ? 'D, M d' : get_option( 'time_format' ) ), $client_timestamp ) ?>
        </span>
    </button>
<?php endforeach ?>