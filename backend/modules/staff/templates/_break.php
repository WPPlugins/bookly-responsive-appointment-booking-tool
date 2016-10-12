<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="break-interval-wrapper" data-break_id="<?php echo $staff_schedule_item_break_id ?>">
    <div class="ab-popup-wrapper hide-on-non-working-day">
        <div class="btn-group btn-group-sm bookly-margin-top-sm">
            <button type="button" class="btn btn-info bookly-js-toggle-popover break-interval"
                    data-popover-content=".bookly-js-popover-content-<?php echo $staff_schedule_item_break_id ?>">
                <?php echo $formatted_interval ?>
            </button>
            <i role="button" title="<?php _e( 'Delete break', 'bookly' ) ?>"
                    class="btn btn-info delete-break">&times;</i>
        </div>

        <div class="bookly-js-popover-content-<?php echo $staff_schedule_item_break_id ?> hidden">
            <div class="error" style="display: none"></div>

            <div class="bookly-js-schedule-form">
                <div class="bookly-flexbox" style="width: 280px;">
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

                <div class="clearfix text-right">
                    <?php \BooklyLite\Lib\Utils\Common::submitButton( null, 'bookly-js-save-break' ) ?>
                    <?php \BooklyLite\Lib\Utils\Common::resetButton( null, 'bookly-js-toggle-popover' ) ?>
                </div>
            </div>
        </div>
    </div>

</div>