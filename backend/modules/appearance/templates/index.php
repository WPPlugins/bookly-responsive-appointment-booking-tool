<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-title">
    <div class="alert" style="font-size: 14px; display: none;">
        <button type="button" class="close" onclick="jQuery('.alert').hide()">&times;</button>
        <?php _e( 'Settings saved.', 'ab' ); ?>
    </div>
    <?php _e( 'Appearance', 'ab' ) ?>
</div>

<input type=text class="wp-color-picker appearance-color-picker" name=color
       value="<?php echo get_option( 'ab_appearance_color' ) ?>"
       data-selected="<?php echo get_option( 'ab_appearance_color' ) ?>" />

<div style="max-width: 960px;">
    <form method=post id=common_settings style="margin-right: 15px">
        <legend id=main_form>
            <div>
                <input id=ab-progress-tracker-checkbox name=ab-progress-tracker-checkbox <?php if (get_option( 'ab_appearance_show_progress_tracker' )): ?>checked=checked<?php endif ?> type=checkbox />
                <label style="display: inline" for="progress_tracker">
                    <b><?php _e( 'Show form progress tracker', 'ab' ) ?></b>
                </label>
            </div>
        </legend>
    </form>
    <!-- Tabs -->
    <div class=tabbable style="margin-top: 20px;">
        <ul class="nav nav-tabs ab-nav-tabs">
            <?php foreach ( $steps as $step_id => $step_name ): ?>
                <li class="ab-step-tab-<?php echo $step_id ?> ab-step-tabs<?php if ( $step_id == 1 ): ?> active<?php endif ?>" data-step-id="<?php echo $step_id ?>">
                    <a href="#" data-toggle=tab><?php echo $step_id ?>. <span class="text_step_<?php echo $step_id ?>" ><?php echo esc_html( $step_name ) ?></span></a>
                </li>
            <?php endforeach ?>
        </ul>
        <!-- Tabs-Content -->
        <div class=tab-content>
            <?php foreach ( $steps as $step_id => $step_name ) : ?>
                <div class="tab-pane-<?php echo $step_id ?><?php if ( $step_id == 1 ): ?> active<?php endif ?>" data-step-id="<?php echo $step_id ?>">
                    <?php
                        // Render unique data per step
                        switch ( $step_id ) {
                            // Service
                            case 1:
                                include '_1_service.php';
                                break;
                            // Time
                            case 2:
                                include '_2_time.php';
                                break;
                            // Details
                            case 3:
                                include '_3_details.php';
                                break;
                            // Payment
                            case 4:
                                include '_4_payment.php';
                                break;
                            // Done
                            case 5:
                                include '_5_done.php';
                                break;
                        }
                    ?>
                </div>
            <?php endforeach ?>
        </div>
        <div style="float:right;margin-right:20px;">
                <p><?php _e('Click on the underlined text to edit.', 'ab') ?></p>
        </div>
        <div class="clear"></div>
        <!-- controls -->
        <div class=controls>
            <!-- spinner -->
            <span id="update_spinner" class="spinner"></span>
            <!-- update button -->
            <button id="update_button" class="btn btn-info ab-update-button ab-appearance-update">
                <?php _e( 'Update', 'ab' ) ?>
            </button>
            <!-- reset button -->
            <button id="reset_button" class="ab-reset-form ab-appearance-reset" type="reset">
                <?php _e( 'Reset', 'ab' ) ?>
            </button>
        </div>
    </div>
</div>