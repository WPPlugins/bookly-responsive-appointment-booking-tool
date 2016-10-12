<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var WP_Locale $wp_locale */
    global $wp_locale;
?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <div class="ab-service-step">
        <div class="ab-row">
            <span data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_info_service_step' ) ) ?>"
                  class="ab_editable ab-bold ab-desc" id="ab-text-info-service"
                  data-rows="7" data-type="textarea"><?php echo esc_html( get_option( 'ab_appearance_text_info_service_step' ) ) ?></span>
        </div>
        <div class="ab-mobile-step_1 ab-row">
            <div class="bookly-js-chain-item bookly-table ab-row">
                <?php if ( \BooklyLite\Lib\Config::locationsEnabled() ) : ?>
                    <div class="ab-formGroup">
                        <?php do_action( 'bookly_locations_render_appearance' ) ?>
                    </div>
                <?php endif ?>
                <div class="ab-formGroup">
                    <label data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_category' ) ) ?>"
                           class="text_category_label"
                           id="ab-text-label-category" data-type="multiple"
                           data-option-id="ab-text-option-category"
                    ><?php echo esc_html( get_option( 'ab_appearance_text_label_category' ) ) ?></label>
                    <div>
                        <select class="ab-select-mobile ab-select-category">
                            <option value="" class="editable" id="ab-text-option-category" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_option_category' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_option_category' ) ) ?></option>
                            <option value="1">Cosmetic Dentistry</option>
                            <option value="2">Invisalign</option>
                            <option value="3">Orthodontics</option>
                            <option value="4">Dentures</option>
                        </select>
                    </div>
                </div>
                <div class="ab-formGroup">
                    <label>
                        <span
                            data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_service' ) ) ?>"
                            data-default-error="<?php echo esc_attr( get_option( 'ab_appearance_text_required_service' ) ) ?>"
                            data-error-id="ab_appearance_text_required_service"
                            id="ab-text-label-service"
                            data-mirror="text_services"
                            data-type="multiple"
                            data-option-id="ab-text-option-service"
                        ><?php echo esc_html( get_option( 'ab_appearance_text_label_service' ) ) ?></span>
                    </label>
                    <div>
                        <select class="ab-select-mobile ab-select-service">
                            <option class="editable" id="ab-text-option-service" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_option_service' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_option_service' ) ) ?></option>
                            <option>Crown and Bridge</option>
                            <option>Teeth Whitening</option>
                            <option>Veneers</option>
                            <option>Invisalign (invisable braces)</option>
                            <option>Orthodontics (braces)</option>
                            <option>Wisdom tooth Removal</option>
                            <option>Root Canal Treatment</option>
                            <option>Dentures</option>
                        </select>
                    </div>
                </div>
                <div class="ab-formGroup">
                    <label class="text_employee_label">
                        <span
                            data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_employee' ) ) ?>"
                            data-default-error="<?php echo esc_attr( get_option( 'ab_appearance_text_required_employee' ) ) ?>"
                            data-error-id="ab_appearance_text_required_employee"
                            id="ab-text-label-employee"
                            data-mirror="text_employee"
                            data-type="multiple"
                            data-option-id="ab-text-option-employee"
                        ><?php echo esc_html( get_option( 'ab_appearance_text_label_employee' ) ) ?></span>
                        </label>
                    <div>
                        <select class="ab-select-mobile ab-select-employee">
                            <option value="0" class="editable" id="ab-text-option-employee" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_option_employee' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_option_employee' ) ) ?></option>
                            <option value="1" class="employee-name-price">Nick Knight (<?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 350 ) ?>)</option>
                            <option value="-1" class="employee-name">Nick Knight</option>
                            <option value="2" class="employee-name-price">Jane Howard (<?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 375 ) ?>)</option>
                            <option value="-2" class="employee-name">Jane Howard</option>
                            <option value="3" class="employee-name-price">Ashley Stamp (<?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 300 ) ?>)</option>
                            <option value="-3" class="employee-name">Ashley Stamp</option>
                            <option value="4" class="employee-name-price">Bradley Tannen (<?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 400 ) ?>)</option>
                            <option value="-4" class="employee-name">Bradley Tannen</option>
                            <option value="5" class="employee-name-price">Wayne Turner (<?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 350 ) ?>)</option>
                            <option value="-5" class="employee-name">Wayne Turner</option>
                            <option value="6" class="employee-name-price">Emily Taylor (<?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 350 ) ?>)</option>
                            <option value="-6" class="employee-name">Emily Taylor</option>
                            <option value="7" class="employee-name-price">Hugh Canberg (<?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 380 ) ?>)</option>
                            <option value="-7" class="employee-name">Hugh Canberg</option>
                            <option value="8" class="employee-name-price">Jim Gonzalez (<?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 390 ) ?>)</option>
                            <option value="-8" class="employee-name">Jim Gonzalez</option>
                            <option value="9" class="employee-name-price">Nancy Stinson (<?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 360 ) ?>)</option>
                            <option value="-9" class="employee-name">Nancy Stinson</option>
                            <option value="10" class="employee-name-price">Marry Murphy (<?php echo \BooklyLite\Lib\Utils\Common::formatPrice( 350 ) ?>)</option>
                            <option value="-10" class="employee-name">Marry Murphy</option>
                        </select>
                    </div>
                </div>
                <div class="ab-formGroup">
                    <label data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_number_of_persons' ) ) ?>" class="ab_editable" data-type="text" id="ab-text-label-number-of-persons"><?php echo esc_html( get_option( 'ab_appearance_text_label_number_of_persons' ) ) ?></label>
                    <div>
                        <select class="ab-select-mobile ab-select-number-of-persons">
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                        </select>
                    </div>
                </div>
                <?php if ( \BooklyLite\Lib\Config::multiplyAppointmentsEnabled() ) : ?>
                    <div class="ab-formGroup">
                        <?php do_action( 'bookly_multiply_render_appearance' ) ?>
                    </div>
                <?php endif ?>
                <?php if ( \BooklyLite\Lib\Config::chainAppointmentsEnabled() ) : ?>
                    <div class="ab-formGroup">
                        <label></label>
                        <div class="bookly-js-actions">
                            <button type="button" class="bookly-round-button" data-action="plus"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/plus.png' ) ?>" /></button>
                        </div>
                    </div>
                <?php endif ?>
            </div>

            <div class="ab-right ab-mobile-next-step ab-btn ab-none" onclick="return false">
                <span class="text_next ab_editable" id="ab-text-button-next" data-mirror="text_next" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_next' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_next' ) ) ?></span>
            </div>
        </div>
        <div class="ab-mobile-step_2">
            <div class="ab-row">
                <div class="ab-left">
                    <div class="ab-available-date ab-left">
                        <div class="ab-formGroup">
                            <label data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_select_date' ) ) ?>" class="ab_editable" id="ab-text-label-select_date" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_select_date' ) ) ?></label>
                            <div>
                               <input class="ab-date-from" style="background-color: #fff;" type="text" data-value="<?php echo date( 'Y-m-d' ) ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="ab-week-days bookly-table ab-left">
                        <?php foreach ( $wp_locale->weekday_abbrev as $weekday_abbrev ) : ?>
                            <div>
                                <div class="bookly-font-bold"><?php echo $weekday_abbrev ?></div>
                                <label class="active">
                                    <input class="ab-week-day" value="1" checked="checked" type="checkbox">
                                </label>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
                <div class="ab-time-range ab-left">
                    <div class="ab-formGroup ab-time-from ab-left">
                        <label data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_start_from' ) ) ?>" class="ab_editable" id="ab-text-label-start_from" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_start_from' ) ) ?></label>
                        <div>
                            <select class="ab-select-time-from">
                                <?php for ( $i = 28800; $i <= 64800; $i += 3600 ) : ?>
                                    <option><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?></option>
                                <?php endfor ?>
                            </select>
                        </div>
                    </div>
                    <div class="ab-formGroup ab-time-to ab-left">
                        <label data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_label_finish_by' ) ) ?>" class="ab_editable" id="ab-text-label-finish_by" data-type="text"><?php echo esc_html( get_option( 'ab_appearance_text_label_finish_by' ) ) ?></label>
                        <div>
                            <select class="ab-select-time-to">
                                <?php for ( $i = 28800; $i <= 64800; $i += 3600 ) : ?>
                                    <option<?php selected( $i == 64800 ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?></option>
                                <?php endfor ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ab-row ab-nav-steps">
                <div class="ab-right ab-mobile-prev-step ab-btn ab-none">
                    <span class="text_back ab_editable" id="ab-text-button-back" data-mirror="text_back" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_back' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_back' ) ) ?></span>
                </div>
                <div class="ab-right ab-next-step ab-btn">
                    <span class="text_next ab_editable" id="ab-text-button-next" data-mirror="text_next" data-type="text" data-default="<?php echo esc_attr( get_option( 'ab_appearance_text_button_next' ) ) ?>"><?php echo esc_html( get_option( 'ab_appearance_text_button_next' ) ) ?></span>
                </div>
                <button class="ab-left ab-goto-cart bookly-round-button ladda-button"><span><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/cart.png' ) ?>" /></span></button>
            </div>
        </div>
    </div>
</div>
<div style="display: none">
    <?php foreach ( array( 'ab_appearance_text_required_service', 'ab_appearance_text_required_name', 'ab_appearance_text_required_phone', 'ab_appearance_text_required_email', 'ab_appearance_text_required_employee', 'ab_appearance_text_required_location' ) as $validator ) : ?>
        <div id="<?php echo $validator ?>"><?php echo get_option( $validator ) ?></div>
    <?php endforeach ?>
</div>
<style id="ab--style-arrow">
    .picker__nav--next:before { border-left: 6px solid <?php echo get_option( 'ab_appearance_color' ) ?>!important; }
    .picker__nav--prev:before { border-right: 6px solid <?php echo get_option( 'ab_appearance_color' ) ?>!important; }
</style>