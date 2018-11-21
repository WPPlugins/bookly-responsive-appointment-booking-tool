<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
?>
<div class="bookly-intervals-wrapper bookly-hide-on-off" data-break_id="<?php echo $staff_schedule_item_break_id ?>">
    <div class="btn-group btn-group-sm bookly-margin-top-sm">
        <button type="button" class="btn btn-info bookly-js-toggle-popover break-interval"
                data-popover-content=".bookly-js-content-break-<?php echo $staff_schedule_item_break_id ?>">
            <?php echo $formatted_interval ?>
        </button>
        <button title="<?php _e( 'Delete break', 'bookly' ) ?>" type="button" class="btn btn-info delete-break ladda-button" data-style="zoom-in" data-spinner-size="20"><span class="ladda-label">&times;</span></button>
    </div>

    <div class="bookly-js-content-break-<?php echo $staff_schedule_item_break_id ?> hidden">
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
                <?php Buttons::renderSubmit( null, 'bookly-js-save-break' ) ?>
                <?php Buttons::renderCustom( null, 'bookly-popover-close btn-lg btn-default', __( 'Close', 'bookly' ) ) ?>
            </div>
        </div>
    </div>
</div>