<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Appearance', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <?php AB_Utils::notice( __( 'Settings saved.', 'bookly' ), 'notice-success', false ) ?>
        <input type=text class="wp-color-picker appearance-color-picker" name=color
               value="<?php echo esc_attr( get_option( 'ab_appearance_color' ) ) ?>"
               data-selected="<?php echo esc_attr( get_option( 'ab_appearance_color' ) ) ?>" />
        <div id="ab-appearance">
            <form method=post id=common_settings>
                <div class="row">
                    <div class="col-md-3">
                        <div id=main_form class="checkbox">
                            <label>
                                <input id=ab-progress-tracker-checkbox name=ab-progress-tracker-checkbox <?php checked( get_option( 'ab_appearance_show_progress_tracker' ) ) ?> type=checkbox />
                                <b><?php _e( 'Show form progress tracker', 'bookly' ) ?></b>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="checkbox">
                            <label>
                                <input id="ab-show-calendar-checkbox" name="ab-show-calendar-checkbox" <?php checked ( get_option( 'ab_appearance_show_calendar' ) ) ?> type="checkbox" />
                                <b><?php _e( 'Show calendar', 'bookly' ) ?></b>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="checkbox">
                            <label>
                                <input id="ab-blocked-timeslots-checkbox" name="ab-blocked-timeslots-checkbox" <?php checked( get_option( 'ab_appearance_show_blocked_timeslots' ) ) ?> type="checkbox" />
                                <b><?php _e( 'Show blocked timeslots', 'bookly' ) ?></b>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="checkbox">
                            <label>
                                <input id="ab-day-one-column-checkbox" name="ab-day-one-column-checkbox" <?php checked( get_option( 'ab_appearance_show_day_one_column' ) ) ?> type="checkbox" />
                                <b><?php _e( 'Show each day in one column', 'bookly' ) ?></b>
                            </label>
                        </div>
                    </div>
                </div>
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
                        <div class="tab-pane-<?php echo $step_id ?><?php if ( $step_id == 1 ): ?> active<?php endif ?>" data-step-id="<?php echo $step_id ?>"<?php if ( $step_id != 1 ): ?> style="display: none"<?php endif ?>>
                            <?php
                            // Render unique data per step
                            switch ( $step_id ) {
                                case 1:     // Service
                                    include '_1_service.php';   break;
                                case 2:     // Time
                                    include '_2_time.php';      break;
                                case 3:     // Details
                                    include '_3_details.php';   break;
                                case 4:     // Payment
                                    include '_4_payment.php';   break;
                                case 5:     // Done
                                    include '_5_done.php';      break;
                            }
                            ?>
                        </div>
                    <?php endforeach ?>
                </div>
                <div class="text-right">
                    <?php _e( 'Click on the underlined text to edit.', 'bookly' ) ?>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <?php AB_Utils::submitButton( 'ajax-send-appearance' ) ?>
        <?php AB_Utils::resetButton() ?>
    </div>
</div>