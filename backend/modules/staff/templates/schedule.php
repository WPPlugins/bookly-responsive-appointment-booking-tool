<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $working_start  = new \BooklyLite\Backend\Modules\Staff\Forms\Widgets\TimeChoice( array( 'empty_value' => __( 'OFF', 'bookly' ), 'type' => 'from' ) );
    $working_end    = new \BooklyLite\Backend\Modules\Staff\Forms\Widgets\TimeChoice( array( 'use_empty' => false, 'type' => 'to' ) );
    $default_breaks = array( 'staff_id' => 1 );
    $break_start   = new \BooklyLite\Backend\Modules\Staff\Forms\Widgets\TimeChoice( array( 'use_empty' => false, 'type' => 'from' ) );
    $break_end     = clone $working_end;
?>
<div>
    <form>
        <?php foreach ( $schedule_items as $item ) : ?>
            <div data-id="<?php echo $item->get( 'day_index' ) ?>"
                data-staff_schedule_item_id="<?php echo $item->get( 'id' ) ?>"
                class="staff-schedule-item-row panel panel-default bookly-panel-unborder">

                <div class="panel-heading bookly-padding-vertical-md">
                    <div class="row">
                        <div class="col-sm-7 col-lg-5">
                            <span class="panel-title"><?php _e( \BooklyLite\Lib\Utils\DateTime::getWeekDayByNumber( $item->get( 'day_index' ) - 1 ) /* take translation from WP catalog */ ) ?></span>
                        </div>
                        <div class="col-sm-5 col-lg-7 hidden-xs hidden-sm">
                            <div class="bookly-font-smaller bookly-color-gray">
                                <?php _e( 'Breaks', 'bookly' ) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-body padding-lr-none">
                        <div class="row">
                            <div class="col-sm-7 col-lg-5">
                                <div class="bookly-flexbox">
                                    <div class="bookly-flex-cell" style="width: 50%">
                                        <?php
                                            $day_is_not_available = null === $item->get( 'start_time' );
                                            echo $working_start->render(
                                                "start_time[{$item->get( 'day_index' )}]",
                                                $item->get( 'start_time' ),
                                                array( 'class' => 'working-schedule-start form-control' )
                                            );
                                        ?>
                                    </div>
                                    <div class="bookly-flex-cell text-center" style="width: 1%">
                                        <div class="bookly-margin-horizontal-lg bookly-hide-on-off">
                                            <?php _e( 'to', 'bookly' ) ?>
                                        </div>
                                    </div>
                                    <div class="bookly-flex-cell" style="width: 50%">
                                        <?php
                                            echo $working_end->render(
                                                "end_time[{$item->get( 'day_index' )}]",
                                                $item->get( 'end_time' ),
                                                array( 'class' => 'working-schedule-end form-control bookly-hide-on-off' )
                                            );
                                        ?>
                                    </div>
                                </div>

                                <input type="hidden"
                                       name="days[<?php echo $item->get( 'id' ) ?>]"
                                       value="<?php echo $item->get( 'day_index' ) ?>"
                                >
                            </div>

                            <div class="col-sm-5 col-lg-7">
                                <div class="bookly-intervals-wrapper bookly-hide-on-off">
                                    <button type="button"
                                            class="bookly-js-toggle-popover btn btn-link bookly-btn-unborder bookly-margin-vertical-screenxs-sm"
                                            data-popover-content=".bookly-js-content-break-<?php echo $item->get( 'id' ) ?>">
                                        <?php _e( 'add break', 'bookly' ) ?>
                                    </button>

                                    <div class="bookly-js-content-break-<?php echo $item->get( 'id' ) ?> hidden">
                                        <div class="error" style="display: none"></div>

                                        <div class="bookly-js-schedule-form">
                                            <div class="bookly-flexbox" style="width: 260px">
                                                <div class="bookly-flex-cell" style="width: 48%;">
                                                    <?php echo $break_start->render( '', $item->get( 'start_time' ), array( 'class' => 'break-start form-control' ) ) ?>
                                                </div>
                                                <div class="bookly-flex-cell" style="width: 4%;">
                                                    <div class="bookly-margin-horizontal-lg">
                                                        <?php _e( 'to', 'bookly' ) ?>
                                                    </div>
                                                </div>
                                                <div class="bookly-flex-cell" style="width: 48%;">
                                                    <?php echo $break_end->render( '', $item->get( 'end_time' ), array( 'class' => 'break-end form-control' ) ) ?>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="text-right">
                                                <?php \BooklyLite\Lib\Utils\Common::submitButton( null, 'bookly-js-save-break' ) ?>
                                                <?php \BooklyLite\Lib\Utils\Common::customButton( null, 'bookly-popover-close btn-lg btn-default', __( 'Close', 'bookly' ) ) ?>
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
            </div>
        <?php endforeach ?>

        <input type="hidden" name="action" value="bookly_staff_schedule_update">

        <div class="panel-footer">
            <?php \BooklyLite\Lib\Utils\Common::submitButton( 'bookly-schedule-save' ) ?>
            <?php \BooklyLite\Lib\Utils\Common::customButton( 'bookly-schedule-reset', 'btn-lg btn-default', __( 'Reset', 'bookly' ), array( 'data-default-breaks' => esc_attr( json_encode( $default_breaks ) ), 'data-spinner-color' => 'rgb(62, 66, 74)' ) ) ?>
        </div>
    </form>
</div>