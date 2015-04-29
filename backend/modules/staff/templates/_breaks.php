<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
$staffScheduleItem = new AB_StaffScheduleItem();
$staffScheduleItem->load($list_item->staff_schedule_item_id);

$breaks_list = $staffScheduleItem->getBreaksList();
$display     = count( $breaks_list ) ? 'inline-block' : 'none;';
?>
<table class="breaks-list hide-on-non-working-day" cellspacing="0" cellpadding="0"<?php if ( $day_is_not_available ) : ?> style="display: none"<?php endif; ?>>
    <tr>
        <td class="breaks-list-label">
            <span style="display: <?php echo $display ?>">
                <?php _e('breaks:','ab') ?>
            </span>
        </td>
        <td class="breaks-list-content">
            <?php foreach ( $breaks_list as $break_interval ) : ?>
                <?php
                $formatted_interval_start = date( $time_format, strtotime( $break_interval->start_time ) );
                $formatted_interval_end   = date( $time_format, strtotime( $break_interval->end_time ) );
                $formatted_interval       = $formatted_interval_start . ' - ' . $formatted_interval_end;
                if (isset($default_breaks)) {
                    $default_breaks['breaks'][] = array(
                        'start'            => $break_interval->start_time,
                        'end'              => $break_interval->end_time,
                        'schedule_item_id' => $break_interval->staff_schedule_item_id
                    );
                }
                ?>
                <div class="break-interval-wrapper" data-break_id="<?php echo $break_interval->id ?>">
                    <div class="ab-popup-wrapper">
                        <a class="ab-popup-trigger break-interval" href="javascript:void(0)"><?php echo $formatted_interval ?></a>
                        <div class="ab-popup" style="display: none">
                            <div class="ab-arrow"></div>
                            <div class="error" style="display: none"></div>
                            <div class="ab-content">
                                <table cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td>
                                            <?php
                                            $breakStart = new AB_TimeChoiceWidget( array( 'use_empty' => false ) );
                                            $break_start_choices = $breakStart->render(
                                            '',
                                            $break_interval->start_time,
                                            array(
                                            'class'              => 'break-start',
                                            'data-default_value' => $start_time_default_value
                                            )
                                            );
                                            $breakEnd = new AB_TimeChoiceWidget( array( 'use_empty' => false ) );
                                            $break_end_choices = $breakEnd->render(
                                            '',
                                            $break_interval->end_time,
                                            array(
                                            'class'              => 'break-end',
                                            'data-default_value' => $end_time_default_value
                                            )
                                            );
                                            echo $break_start_choices . ' <span>' . __( 'to','ab' ) . '</span> ' . $break_end_choices;
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <a class="btn btn-info ab-popup-save ab-save-break ab-update-button"><?php _e('Save break','ab') ?></a>
                                            <a class="ab-popup-close" href="#"><?php _e('Cancel','ab') ?></a>
                                        </td>
                                    </tr>
                                </table>
                                <a class="ab-popup-close ab-popup-close-icon" href="javascript:void(0)"></a>
                            </div>
                        </div>
                    </div>
                    <img class="delete-break" src="<?php echo plugins_url( 'backend/resources/images/delete_cross.png', AB_PATH . '/main.php' ) ?>" />
                </div>
            <?php endforeach; ?>
        </td>
    </tr>
</table>