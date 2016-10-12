<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $working_start = new \BooklyLite\Backend\Modules\Staff\Forms\Widgets\TimeChoice( array( 'empty_value' => __( 'OFF', 'bookly' ), 'type' => 'from' ) );
    $working_end   = new \BooklyLite\Backend\Modules\Staff\Forms\Widgets\TimeChoice( array( 'use_empty' => false, 'type' => 'to' ) );
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

                <div class="list-group bookly-padding-top-md">
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-sm-7 col-lg-5">
                                <div class="bookly-flexbox">
                                    <div class="bookly-flex-cell" style="width: 50%">
                                        <?php
                                        $day_is_not_available = null === $item->get( 'start_time' );
                                        $bound = array( $item->get( 'start_time' ), $item->get( 'end_time' ) );
                                        echo $working_start->render(
                                            "start_time[{$item->get( 'day_index' )}]",
                                            $item->get( 'start_time' ),
                                            array( 'class' => 'working-start form-control' )
                                        );
                                        ?>
                                    </div>
                                    <div class="bookly-flex-cell text-center" style="width: 1%">
                                        <div class="bookly-margin-horizontal-lg hide-on-non-working-day <?php if ( $day_is_not_available ) : ?>hidden<?php endif ?>">
                                            <?php _e( 'to', 'bookly' ) ?>
                                        </div>
                                    </div>
                                    <div class="bookly-flex-cell" style="width: 50%">
                                        <?php
                                        $working_end_choices_attributes = array( 'class' => 'working-end form-control hide-on-non-working-day' );

                                        echo $working_end->render(
                                            "end_time[{$item->get( 'day_index' )}]",
                                            $item->get( 'end_time' ),
                                            $working_end_choices_attributes
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
                                <div class="ab-popup-wrapper hide-on-non-working-day <?php if ( $day_is_not_available ) : ?>hidden<?php endif; ?>">
                                    <button type="button"
                                            class="bookly-js-toggle-popover btn btn-link bookly-btn-unborder bookly-margin-vertical-screenxs-sm"
                                            data-popover-content=".bookly-js-popover-content-<?php echo $item->get( 'id' ) ?>">
                                        <?php _e( 'add break', 'bookly' ) ?>
                                    </button>

                                    <div class="bookly-js-popover-content-<?php echo $item->get( 'id' ) ?> hidden">
                                        <div class="error" style="display: none"></div>

                                        <div class="bookly-js-schedule-form">
                                            <?php
                                            $break_start = new \BooklyLite\Backend\Modules\Staff\Forms\Widgets\TimeChoice( array( 'use_empty' => false, 'type' => 'from',  'bound' => $bound ) );
                                            $break_end   = new \BooklyLite\Backend\Modules\Staff\Forms\Widgets\TimeChoice( array( 'use_empty' => false, 'type' => 'bound', 'bound' => $bound ) );
                                            $break_start_choices = $break_start->render( '', $item->get( 'start_time' ), array( 'class' => 'break-start form-control' ) );
                                            $break_end_choices   = $break_end->render( '', $item->get( 'end_time' ), array( 'class' => 'break-end form-control' ) );
                                            ?>

                                            <div class="bookly-flexbox" style="width: 260px">
                                                <div class="bookly-flex-cell" style="width: 48%;">
                                                    <?php echo $break_start_choices ?>
                                                </div>
                                                <div class="bookly-flex-cell" style="width: 4%;">
                                                    <div class="bookly-margin-horizontal-lg">
                                                        <?php _e( 'to', 'bookly' ) ?>
                                                    </div>
                                                </div>
                                                <div class="bookly-flex-cell" style="width: 48%;">
                                                    <?php echo $break_end_choices ?>
                                                </div>
                                            </div>

                                            <hr>
                                            <div class="text-right">
                                                <button type="submit"
                                                        class="bookly-js-save-break btn btn-success bookly-margin-right-sm"
                                                >
                                                    <?php _e( 'Save', 'bookly' ) ?>
                                                </button>
                                                <button type="button"
                                                        class="bookly-js-toggle-popover btn btn-default"
                                                >
                                                    <?php _e( 'Cancel', 'bookly' ) ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="breaks hide-on-non-working-day <?php if ( $day_is_not_available ) : ?>hidden<?php endif; ?>">
                                    <?php include '_breaks.php' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>

        <input type="hidden" name="action" value="ab_staff_schedule_update">

        <div class="panel-footer">
            <?php \BooklyLite\Lib\Utils\Common::submitButton( 'bookly-schedule-save' ) ?>
            <?php \BooklyLite\Lib\Utils\Common::resetButton( 'bookly-schedule-reset' ) ?>
        </div>
    </form>
</div>