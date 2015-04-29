<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab-staff-schedule">
    <?php if ( count( $schedule_list ) ) : ?>
        <?php
            $time_format = get_option( 'time_format' );
            $one_hour_in_seconds = 1 * 60 * 60;

            $start_time_default_value = AB_StaffScheduleItem::WORKING_START_TIME;
            $end_time_default_value   = date( 'H:i:s', strtotime( AB_StaffScheduleItem::WORKING_START_TIME . ' + 1 hour' ) );
            $default_breaks = array('staff_id' => $staff_id);
        ?>
        <div class="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <!-- text -->
        </div>
        <form>
            <table cellspacing="0" cellpadding="0">
                <tbody>
                    <?php foreach ( $schedule_list as $list_item ) : ?>
                        <?php $day_is_not_available = null === $list_item->start_time ?>
                        <tr data-id="<?php echo $list_item->day_index ?>" data-staff_schedule_item_id="<?php echo $list_item->staff_schedule_item_id ?>" class="staff-schedule-item-row">
                            <td class="first"><?php echo __( $list_item->name, 'ab' ) ?></td>
                            <td class="working-intervals">
                                <?php
                                    $workingStart = new AB_TimeChoiceWidget( array( 'empty_value' => __('OFF', 'ab') ) );
                                    $working_start_choices = $workingStart->render(
                                        'start_time[' . $list_item->day_index . ']',
                                        $list_item->start_time,
                                        array( 'class' => 'working-start', 'style' => 'width:auto' )
                                    );
                                    $workingEnd = new AB_TimeChoiceWidget( array( 'use_empty' => false ) );
                                    $working_end_choices_attributes = array( 'class' => 'working-end hide-on-non-working-day', 'style' => 'width:auto' );
                                    if ( $day_is_not_available ) {
                                        $working_end_choices_attributes['style'] = 'display:none; width:auto';
                                    }
                                    $working_end_choices = $workingEnd->render(
                                        'end_time[' . $list_item->day_index . ']',
                                        $list_item->end_time,
                                        $working_end_choices_attributes
                                    );
                                    echo $working_start_choices . ' <span class="hide-on-non-working-day"' . ($day_is_not_available ? ' style="display: none"' : '') . '>' . __( 'to', 'ab') . '</span> ' . $working_end_choices;
                                ?>
                                <input type="hidden" name="days[<?php echo $list_item->staff_schedule_item_id ?>]" value="<?php echo $list_item->day_index ?>"/>
                            </td>
                            <td class="add-break">
                                <div class="ab-popup-wrapper hide-on-non-working-day"<?php if ( $day_is_not_available ) : ?> style="display: none"<?php endif; ?>>
                                    <a class="ab-popup-trigger" href="javascript:void(0)"><?php _e('add break', 'ab') ?></a>
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
                                                                null,
                                                                array(
                                                                    'class'              => 'break-start',
                                                                    'data-default_value' => $start_time_default_value
                                                                )
                                                            );
                                                            $breakEnd = new AB_TimeChoiceWidget( array( 'use_empty' => false ) );
                                                            $break_end_choices = $breakEnd->render(
                                                                '',
                                                                null,
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
                            </td>
                            <td class="breaks">
                                <?php include("_breaks.php");?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    <tr class="staff-schedule-item-row ab-last-row">
                        <td></td>
                        <td colspan="3">
                            <input type="hidden" name="action" value="ab_staff_schedule_update"/>
                            <span class="spinner left"></span>
                            <a id="ab-staff-schedule-update" href="javascript:void(0)" class="btn btn-info ab-update-button"><?php _e( 'Update', 'ab' ) ?></a>
                            <a id="ab-schedule-reset" class="ab-reset-form" href="javascript:void(0)" default-breaks='<?php echo json_encode($default_breaks); ?>'>
                                <?php _e( 'Reset', 'ab') ?>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    <?php else: ?>
        <h3 align="center"><?php _e('No result','ab') ?></h3>
    <?php endif; ?>
</div>