<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-progress-tracker<?php if ( $this->payment_disabled ) echo ' ab-progress-tracker-four-steps'?>">
    <?php
        // Show Progress Tracker if enabled in settings
        if ( intval( get_option( 'ab_appearance_show_progress_tracker' ) == 1 ) ) {
            _e( $this->progress_tracker, 'ab' ) ;
        }
    ?>
</div>
<div class="ab-wrapper-content">
    <div style="margin-bottom: 15px!important;" class="ab-row-fluid">
        <div class="ab-text-info-first-preview ab-bold"><?php echo $info_text ?></div>
    </div>
    <form class="ab-service-form">
        <div class="ab-mobile-step_1 ab-row-fluid">
            <div id="ab-category" class="ab-category-list ab-left">
                <label class="ab-category-title"><?php echo esc_html(get_option( 'ab_appearance_text_label_category' )); ?></label>
                <div class="ab-select-wrap">
                    <select class="select-list ab-select-mobile ab-select-category" style="width: 100%">
                      <option value=""><?php echo esc_html(get_option( 'ab_appearance_text_option_category' )); ?></option>
                    </select>
                </div>
            </div>
            <div id="ab-service" class="ab-category-list ab-category-list-center ab-left">
                <label class="ab-category-title"><?php echo esc_html(get_option( 'ab_appearance_text_label_service' )); ?></label>
                <div class="ab-select-wrap">
                    <select class="select-list ab-select-mobile ab-select-service" style="width: 100%" >
                        <option value=""><?php echo esc_html(get_option( 'ab_appearance_text_option_service' )); ?></option>
                    </select>
                </div>
                <div class="ab-select-service-error ab-bold" style="color: #f56530; padding-top: 5px; display: none"><?php _e( '* Please select a service', 'ab' ); ?></div>
            </div>
            <div id="ab-employee" class="ab-category-list ab-left">
                <label class="ab-category-title"><?php echo esc_html(get_option( 'ab_appearance_text_label_employee' )); ?></label>
                <div class="ab-select-wrap">
                    <select class="select-list ab-select-mobile ab-select-employee" style="width: 100%">
                      <option value=""><?php echo esc_html(get_option( 'ab_appearance_text_option_employee' )); ?></option>
                    </select>
                </div>
            </div>
            <button class="ab-right ab-mobile-next-step ladda-button orange zoom-in">
                <span class="ab_label"><?php _e( 'Next', 'ab' ) ?></span>
            </button>
        </div>
        <div class="ab-mobile-step_2">
            <div class="ab-row-fluid">
                <div class="ab-left ab-available-date">
                    <label><?php echo esc_html(get_option( 'ab_appearance_text_label_select_date' )); ?></label>
                    <div class="ab-input-wrap">
                        <span class="ab-requested-date-wrap">
                           <input class="ab-requested-date-from select-list" type="text" value="<?php echo $userData->getFormattedRequestedDateFrom() ?>" />
                        </span>
                    </div>
                </div>
                <?php if ( !empty( $work_day_time_data['available_days'] ) ) : ?>
                    <div class="ab-left ab-available-days">
                        <ul class="ab-week-days">
                            <?php foreach ( $work_day_time_data['available_days'] as $key => $day ) : ?>
                                <li>
                                    <div class="ab-bold"><?php echo $day ?></div>
                                    <!-- #11055: all days are checked by default -->
                                    <label class="active">
                                        <input class="ab-week-day ab-week-day-<?php echo $key ?>" value="<?php echo $key ?>" checked="checked" type="checkbox"/>
                                    </label>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if ( !empty( $work_day_time_data['time_range'] ) ) : ?>
                    <?php $time_list = $work_day_time_data['time_range'] ?>
                    <div class="ab-left ab-time-range">
                        <div class="ab-left ab-time-from">
                            <label><?php echo esc_html(get_option( 'ab_appearance_text_label_start_from' )); ?></label>
                            <div class="ab-select-wrap">
                                <select class="select-list ab-requested-time-from" style="width: auto">
                                    <?php foreach ($time_list as $key => $time) : ?>
                                        <option value="<?php echo $key ?>"<?php if ( $userData->getRequestedTimeFrom() == $key ) echo ' selected="selected"' ?>><?php echo $time ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                        <div class="ab-left ab-time-to">
                            <label><?php echo esc_html(get_option( 'ab_appearance_text_label_finish_by' )); ?></label>
                            <div class="ab-select-wrap">
                                <select class="select-list ab-requested-time-to" style="width: auto">
                                    <?php foreach ($time_list as $key => $time) : ?>
                                        <option value="<?php echo $key ?>"<?php if ( $userData->getRequestedTimeTo() == $key ) echo ' selected="selected"' ?>><?php echo $time ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                        <div class="ab-select-time-error ab-bold ab-clear" style="text-align:right; color: #f56530; padding-top: 5px; display: none"><?php _e( '* The start time must be less than the end time', 'ab' ); ?></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="ab-nav-steps ab-clear ab-row-fluid">
                <button class="ab-left ab-mobile-prev-step ladda-button orange zoom-in">
                    <span class="ab_label"><?php _e( 'Back', 'ab' ) ?></span><span class="spinner"></span>
                </button>
	            <button class="ab-right ab-next-step ladda-button orange zoom-in">
		            <span class="ab_label"><?php _e( 'Next', 'ab' ) ?></span><span class="spinner"></span>
	            </button>
            </div>
        </div>
    </form>
</div>