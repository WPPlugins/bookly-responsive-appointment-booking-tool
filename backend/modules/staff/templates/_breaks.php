<?php use BooklyLite\Backend\Modules\Staff\Forms\Widgets;
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $breaks_list = $item->getBreaksList();
    $display     = count( $breaks_list ) ? 'inline-block' : 'none';
?>

<div class="breaks-list">
    <?php if ( $breaks_list ) : ?>
        <div class="bookly-font-smaller bookly-margin-bottom-xs bookly-color-gray visible-xs visible-sm">
            <?php _e( 'Breaks', 'bookly' ) ?>
        </div>
    <?php endif ?>

    <div class="breaks-list-content">
        <?php foreach ( $breaks_list as $break_interval ) : ?>
            <?php
            $formatted_start = \BooklyLite\Lib\Utils\DateTime::formatTime( \BooklyLite\Lib\Utils\DateTime::timeToSeconds( $break_interval['start_time'] ) );
            $formatted_end   = \BooklyLite\Lib\Utils\DateTime::formatTime( \BooklyLite\Lib\Utils\DateTime::timeToSeconds( $break_interval['end_time'] ) );
            if ( isset( $default_breaks ) ) {
                $default_breaks['breaks'][] = array(
                    'start_time'             => $break_interval['start_time'],
                    'end_time'               => $break_interval['end_time'],
                    'staff_schedule_item_id' => $break_interval['staff_schedule_item_id']
                );
            }

            $breakStart = new Widgets\TimeChoice( array( 'use_empty' => false, 'type' => 'from', 'bound' => $bound ) );
            $break_start_choices = $breakStart->render(
                '',
                $break_interval['start_time'],
                array( 'class' => 'break-start form-control' )
            );

            $breakEnd   = new Widgets\TimeChoice( array( 'use_empty' => false, 'type' => 'bound',  'bound' => $bound ) );
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
    </div>
</div>