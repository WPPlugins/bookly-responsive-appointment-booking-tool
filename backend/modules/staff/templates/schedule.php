<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $default_breaks_json = json_encode( array( 'staff_id' => $staff_id ) );
    $working_start = new AB_TimeChoiceWidget( array( 'empty_value' => __( 'OFF', 'bookly' ), 'type' => 'from' ) );
    $working_end   = new AB_TimeChoiceWidget( array( 'use_empty' => false, 'type' => 'to' ) );
?>
<div id="ab-staff-schedule">

    <form class="form-inline">
        <div class="table-responsive">
            <table cellspacing="0" cellpadding="0">
                <tbody>
                <?php foreach ( $schedule_items as $item ) : ?>
                    <tr data-id="<?php echo $item->get( 'day_index' ) ?>" data-staff_schedule_item_id="<?php echo $item->get( 'id' ) ?>" class="staff-schedule-item-row">
                        <td class="first"><label><?php _e( AB_DateTimeUtils::getWeekDayByNumber( $item->get( 'day_index' ) - 1 ) /* take translation from WP catalog */ ) ?></label></td>
                        <td class="working-intervals">
                            <?php
                                $day_is_not_available = null === $item->get( 'start_time' );
                                $bound = array( $item->get( 'start_time' ), $item->get( 'end_time' ) );
                                echo $working_start->render(
                                    "start_time[{$item->get( 'day_index' )}]",
                                    $item->get( 'start_time' ),
                                    array( 'class' => 'working-start form-control' )
                                );
                            ?>
                            <span class="hide-on-non-working-day"<?php if ( $day_is_not_available ): ?> style="display: none"<?php endif ?>><?php _e( 'to', 'bookly' ) ?></span>
                            <?php
                                $working_end_choices_attributes = array( 'class' => 'working-end form-control hide-on-non-working-day' );
                                if ( $day_is_not_available ) {
                                    $working_end_choices_attributes['style'] = 'display:none';
                                }
                                echo $working_end->render(
                                    "end_time[{$item->get( 'day_index' )}]",
                                    $item->get( 'end_time' ),
                                    $working_end_choices_attributes
                                );
                            ?>
                            <input type="hidden" name="days[<?php echo $item->get( 'id' ) ?>]" value="<?php echo $item->get( 'day_index' ) ?>"/>
                        </td>
                        <td class="add-break">
                            <div class="ab-popup-wrapper hide-on-non-working-day"<?php if ( $day_is_not_available ) : ?> style="display: none"<?php endif; ?>>
                                <a class="ab-popup-trigger" href="javascript:void(0)"><?php _e( 'add break', 'bookly' ) ?></a>
                                <div class="ab-popup" style="display: none">
                                    <div class="ab-arrow"></div>
                                    <div class="error" style="display: none"></div>
                                    <div class="ab-content">
                                        <table cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td>
                                                    <?php
                                                        $break_start = new AB_TimeChoiceWidget( array( 'use_empty' => false, 'type' => 'from',  'bound' => $bound ) );
                                                        $break_end   = new AB_TimeChoiceWidget( array( 'use_empty' => false, 'type' => 'bound', 'bound' => $bound ) );
                                                        $break_start_choices = $break_start->render( '', $item->get( 'start_time' ), array( 'class' => 'break-start form-control' ) );
                                                        $break_end_choices   = $break_end->render( '', $item->get( 'end_time' ), array( 'class' => 'break-end form-control' ) );
                                                        echo $break_start_choices . ' <span>' . __( 'to', 'bookly' ) . '</span> ' . $break_end_choices;
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a class="btn btn-info ab-popup-save ab-save-break"><?php _e( 'Save', 'bookly' ) ?></a>
                                                    <a class="ab-popup-close ab-reset-form" href="#"><?php _e( 'Cancel', 'bookly' ) ?></a>
                                                </td>
                                            </tr>
                                        </table>
                                        <a class="ab-popup-close ab-popup-close-icon" href="javascript:void(0)"></a>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="breaks">
                            <?php include '_breaks.php' ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                <tr class="staff-schedule-item-row ab-last-row">
                    <td></td>
                    <td colspan="3">
                        <input type="hidden" name="action" value="ab_staff_schedule_update"/>
                        <?php AB_Utils::submitButton( 'ajax-send-staff-schedule' ) ?>
                        <?php AB_Utils::resetButton( 'ab-schedule-reset' ) ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </form>
</div>