<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var \Bookly\Lib\Entities\StaffScheduleItem $item */
    $breaks_list = $item->getBreaksList();
?>
<div class="breaks-list">
    <?php if ( $breaks_list ) : ?>
        <div class="bookly-font-smaller bookly-margin-bottom-xs bookly-color-gray visible-xs visible-sm">
            <?php _e( 'Breaks', 'bookly' ) ?>
        </div>
    <?php endif ?>

    <div class="breaks-list-content">
        <?php foreach ( $breaks_list as $break_interval ) :
            if ( isset ( $default_breaks ) ) {
                $default_breaks['breaks'][] = array(
                    'start_time'             => $break_interval['start_time'],
                    'end_time'               => $break_interval['end_time'],
                    'staff_schedule_item_id' => $break_interval['staff_schedule_item_id'],
                );
            }
            $break_start_choices = $break_start->render(
                '',
                $break_interval['start_time'],
                array( 'class' => 'break-start form-control' )
            );

            $break_end_choices = $break_end->render(
                '',
                $break_interval['end_time'],
                array( 'class' => 'break-end form-control' )
            );

            $self::renderTemplate( '_break', array(
                'break_start_choices'          => $break_start_choices,
                'break_end_choices'            => $break_end_choices,
                'formatted_interval'           => \Bookly\Lib\Utils\DateTime::formatInterval( $break_interval['start_time'], $break_interval['end_time'] ),
                'staff_schedule_item_break_id' => $break_interval['id'],
            ) );
        endforeach ?>
    </div>
</div>