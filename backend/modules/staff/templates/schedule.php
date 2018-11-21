<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Modules\Staff\Forms\Widgets\TimeChoice;
use Bookly\Backend\Modules\Staff\Proxy;
/** @var \Bookly\Lib\Entities\StaffScheduleItem[] $schedule_items */
$working_start  = new TimeChoice( array( 'empty_value' => __( 'OFF', 'bookly' ), 'type' => 'from' ) );
$working_end    = new TimeChoice( array( 'use_empty' => false, 'type' => 'to' ) );
$default_breaks = array( 'staff_id' => $staff_id );
$break_start   = new TimeChoice( array( 'use_empty' => false, 'type' => 'break_from' ) );
$break_end     = clone $working_end;
?>
<div>
    <form>
        <?php Proxy\Locations::renderLocationSwitcher( $staff_id, $location_id, 'custom_schedule' ) ?>
        <?php foreach ( $schedule_items as $item ) : ?>
            <div data-id="<?php echo $item->getDayIndex() ?>"
                data-staff_schedule_item_id="<?php echo $item->getId() ?>"
                class="staff-schedule-item-row panel panel-default bookly-panel-unborder">

                <div class="panel-heading bookly-padding-vertical-md">
                    <div class="row">
                        <div class="col-sm-7 col-lg-5">
                            <span class="panel-title"><?php esc_html_e( \Bookly\Lib\Utils\DateTime::getWeekDayByNumber( $item->getDayIndex() - 1 ) /* take translation from WP catalog */ ) ?></span>
                        </div>
                        <div class="col-sm-5 col-lg-7 hidden-xs hidden-sm">
                            <div class="bookly-font-smaller bookly-color-gray">
                                <?php esc_html_e( 'Breaks', 'bookly' ) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-body padding-lr-none">
                    <div class="row">
                        <div class="col-sm-7 col-lg-5">
                            <div class="bookly-flexbox form-group">
                                <div class="bookly-flex-cell" style="width: 50%">
                                    <?php
                                        $day_is_not_available = null === $item->getStartTime();
                                        echo $working_start->render(
                                            "start_time[{$item->getDayIndex()}]",
                                            $item->getStartTime(),
                                            array( 'class' => 'working-schedule-start form-control' )
                                        );
                                    ?>
                                </div>
                                <div class="bookly-flex-cell text-center" style="width: 1%">
                                    <div class="bookly-margin-horizontal-lg bookly-hide-on-off">
                                        <?php esc_html_e( 'to', 'bookly' ) ?>
                                    </div>
                                </div>
                                <div class="bookly-flex-cell" style="width: 50%">
                                    <?php
                                        echo $working_end->render(
                                            "end_time[{$item->getDayIndex()}]",
                                            $item->getEndTime(),
                                            array( 'class' => 'working-schedule-end form-control bookly-hide-on-off' )
                                        );
                                    ?>
                                </div>
                            </div>

                            <input type="hidden" name="days[<?php echo $item->getId() ?>]" value="<?php echo $item->getDayIndex() ?>">
                        </div>

                        <div class="col-sm-5 col-lg-7">
                            <div class="bookly-intervals-wrapper bookly-hide-on-off">
                                <button type="button"
                                        class="bookly-js-toggle-popover btn btn-link bookly-btn-unborder bookly-margin-vertical-screenxs-sm"
                                        data-popover-content=".bookly-js-content-break-<?php echo $item->getId() ?>">
                                    <?php esc_html_e( 'add break', 'bookly' ) ?>
                                </button>

                                <div class="bookly-js-content-break-<?php echo $item->getId() ?> hidden">
                                    <div class="error" style="display: none"></div>

                                    <div class="bookly-js-schedule-form">
                                        <div class="bookly-flexbox" style="width: 260px">
                                            <div class="bookly-flex-cell" style="width: 48%;">
                                                <?php echo $break_start->render( '', $item->getStartTime(), array( 'class' => 'break-start form-control' ) ) ?>
                                            </div>
                                            <div class="bookly-flex-cell" style="width: 4%;">
                                                <div class="bookly-margin-horizontal-lg">
                                                    <?php esc_html_e( 'to', 'bookly' ) ?>
                                                </div>
                                            </div>
                                            <div class="bookly-flex-cell" style="width: 48%;">
                                                <?php echo $break_end->render( '', $item->getEndTime(), array( 'class' => 'break-end form-control' ) ) ?>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="text-right">
                                            <?php Buttons::renderCustom( null, 'bookly-js-save-break btn-lg btn-success', __( 'Save', 'bookly' ) ) ?>
                                            <?php Buttons::renderCustom( null, 'bookly-popover-close btn-lg btn-default', __( 'Close', 'bookly' ) ) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="breaks bookly-hide-on-off">
                                <?php include '_breaks.php' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>

        <input type="hidden" name="action" value="bookly_staff_schedule_update">
        <?php Inputs::renderCsrf() ?>

        <div class="panel-footer">
            <?php Buttons::renderSubmit( 'bookly-schedule-save' ) ?>
            <?php Buttons::renderReset( 'bookly-schedule-reset', null, null, array( 'data-default-breaks' => json_encode( $default_breaks ), 'data-spinner-color' => 'rgb(62, 66, 74)' ) ) ?>
        </div>
    </form>
</div>