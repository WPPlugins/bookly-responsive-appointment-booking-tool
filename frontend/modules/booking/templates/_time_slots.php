<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<button class="bookly-day" value="<?php echo esc_attr( $group ) ?>">
    <?php echo date_i18n( ( $duration_in_days ? 'M' : 'D, M d' ), strtotime( $group ) ) ?>
</button>
<?php foreach ( $slots as $slot ) :
    /** @var \BooklyLite\Lib\Slots\Range $slot */
    $data = $slot->buildSlotData();
    printf( '<button value="%s" data-group="%s" class="bookly-hour%s" %s>
        <span class="ladda-label%s"><i class="bookly-hour-icon"><span></span></i>%s</span>
    </button>',
        esc_attr( json_encode( $data ) ),
        $group,
        $slot->fullyBooked() ? ' booked' : '',
        disabled( $slot->fullyBooked(), true, false ),
        $data[0][2] == $selected_date ? ' bookly-bold' : '',
        $slot->start()->toClientTz()->formatI18n( $duration_in_days ? 'D, M d' : get_option( 'time_format' ) )
    );
endforeach ?>