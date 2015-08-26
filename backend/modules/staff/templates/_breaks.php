<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $breaks_list = $item->getBreaksList();
    $display     = count( $breaks_list ) ? 'inline-block' : 'none';
?>
<table class="breaks-list hide-on-non-working-day" cellspacing="0" cellpadding="0"<?php if ( $day_is_not_available ) : ?> style="display: none"<?php endif ?>>
    <tr>
        <td class="breaks-list-label">
            <span style="display: <?php echo $display ?>">
                <?php _e( 'breaks:', 'bookly' ) ?>
            </span>
        </td>
        <td class="breaks-list-content">
            <?php foreach ( $breaks_list as $break_interval ) : ?>
                <?php
                $formatted_start = AB_DateTimeUtils::formatTime( AB_DateTimeUtils::timeToSeconds( $break_interval['start_time'] ) );
                $formatted_end   = AB_DateTimeUtils::formatTime( AB_DateTimeUtils::timeToSeconds( $break_interval['end_time'] ) );
                if ( isset( $default_breaks ) ) {
                    $default_breaks['breaks'][] = array(
                        'start_time'            => $break_interval['start_time'],
                        'end_time'              => $break_interval['end_time'],
                        'staff_schedule_item_id'=> $break_interval['staff_schedule_item_id']
                    );
                }

                $breakStart = new AB_TimeChoiceWidget( array( 'use_empty' => false, 'type' => 'from', 'bound' => $bound ) );
                $break_start_choices = $breakStart->render(
                    '',
                    $break_interval['start_time'],
                    array( 'class' => 'break-start form-control' )
                );
                $breakEnd   = new AB_TimeChoiceWidget( array( 'use_empty' => false, 'type' => 'bound',  'bound' => $bound ) );
                $break_end_choices = $breakEnd->render(
                    '',
                    $break_interval['end_time'],
                    array( 'class' => 'break-end form-control' )
                );

                $this->render( '_break', array(
                    'staff_schedule_item_break_id' => $break_interval['id'],
                    'formatted_interval'           => $formatted_start . ' - ' . $formatted_end,
                    'break_start_choices'          => $break_start_choices,
                    'break_end_choices'            => $break_end_choices,
                ) );
                ?>
            <?php endforeach ?>
        </td>
    </tr>
</table>