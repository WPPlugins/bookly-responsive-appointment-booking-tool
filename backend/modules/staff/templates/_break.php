<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="break-interval-wrapper" data-break_id="<?php echo $staff_schedule_item_break_id ?>">
    <div class="ab-popup-wrapper hide-on-non-working-day">
        <a class="ab-popup-trigger break-interval" href="javascript:void(0)"><?php echo $formatted_interval ?></a>
        <div class="ab-popup" style="display: none">
            <div class="ab-arrow"></div>
            <div class="error" style="display: none"></div>
            <div class="ab-content">
                <table cellspacing="0" cellpadding="0">
                    <tr>
                        <td><?php echo $break_start_choices ?> <span class="hide-on-non-working-day"><?php _e( 'to', 'bookly') ?></span> <?php echo $break_end_choices ?></td>
                    </tr>
                    <tr>
                        <td>
                            <a class="btn btn-info ab-popup-save ab-save-break"><?php _e( 'Save', 'bookly' ) ?></a>
                            <a class="ab-popup-close" href="#"><?php _e( 'Cancel', 'bookly' ) ?></a>
                        </td>
                    </tr>
                </table>
                <a class="ab-popup-close ab-popup-close-icon" href="javascript:void(0)"></a>
            </div>
        </div>
    </div>
    <i title="<?php _e( 'Delete break', 'bookly' ) ?>" class="delete-break glyphicon glyphicon-remove" role='button'></i>
</div>