<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var WP_Locale $wp_locale */
    global $wp_locale;
?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <div class="bookly-service-step">
        <div class="bookly-box">
            <span data-option-default="<?php form_option( 'bookly_l10n_info_service_step' ) ?>"
                  class="bookly-editable ab-bold ab-desc" id="bookly_l10n_info_service_step"
                  data-rows="7" data-type="textarea"><?php echo esc_html( get_option( 'bookly_l10n_info_service_step' ) ) ?></span>
        </div>
        <div class="ab-mobile-step_1 bookly-box">
            <div class="bookly-js-chain-item bookly-table bookly-box">
                <?php if ( \BooklyLite\Lib\Config::isLocationsEnabled() ) : ?>
                    <div class="ab-formGroup">
                        <?php do_action( 'bookly_locations_render_appearance' ) ?>
                    </div>
                <?php endif ?>
                <div class="ab-formGroup">
                    <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderLabel( array( 'bookly_l10n_label_category', 'bookly_l10n_option_category', ) ) ?>
                    <div>
                        <select class="ab-select-mobile ab-select-category">
                            <option value="" id="bookly_l10n_option_category"><?php echo esc_html( get_option( 'bookly_l10n_option_category' ) ) ?></option>
                            <option value="1">Cosmetic Dentistry</option>
                            <option value="2">Invisalign</option>
                            <option value="3">Orthodontics</option>
                            <option value="4">Dentures</option>
                        </select>
                    </div>
                </div>
                <div class="ab-formGroup">
                    <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderLabel( array(
                        'bookly_l10n_label_service',
                        'bookly_l10n_option_service',
                        'bookly_l10n_required_service',
                    ) ) ?>
                    <div>
                        <select class="ab-select-mobile ab-select-service">
                            <option id="bookly_l10n_option_service"><?php echo esc_html( get_option( 'bookly_l10n_option_service' ) ) ?></option>
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
                    <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderLabel( array(
                        'bookly_l10n_label_employee',
                        'bookly_l10n_option_employee',
                        'bookly_l10n_required_employee',
                    ) ) ?>
                    <div>
                        <select class="ab-select-mobile ab-select-employee">
                            <option value="0" id="bookly_l10n_option_employee"><?php echo esc_html( get_option( 'bookly_l10n_option_employee' ) ) ?></option>
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
                    <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderLabel( array( 'bookly_l10n_label_number_of_persons', ) ) ?>
                    <div>
                        <select class="ab-select-mobile ab-select-number-of-persons">
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                        </select>
                    </div>
                </div>
                <?php if ( \BooklyLite\Lib\Config::isMultiplyAppointmentsEnabled() ) : ?>
                    <div class="ab-formGroup">
                        <?php do_action( 'bookly_multiply_appointments_render_appearance' ) ?>
                    </div>
                <?php endif ?>
                <?php if ( \BooklyLite\Lib\Config::isChainAppointmentsEnabled() ) : ?>
                    <div class="ab-formGroup">
                        <label></label>
                        <div>
                            <button class="bookly-round" ><i class="bookly-icon-sm bookly-icon-plus"></i></button>
                        </div>
                    </div>
                <?php endif ?>
            </div>

            <div class="ab-right ab-mobile-next-step ab-btn ab-none" onclick="return false">
                <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_next' ), 'bookly-js-text-next' ) ?>
            </div>
        </div>
        <div class="ab-mobile-step_2">
            <div class="bookly-box">
                <div class="ab-left">
                    <div class="ab-available-date ab-left">
                        <div class="ab-formGroup">
                            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderLabel( array( 'bookly_l10n_label_select_date', ) ) ?>
                            <div>
                               <input class="ab-date-from" style="background-color: #fff;" type="text" data-value="<?php echo date( 'Y-m-d' ) ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="bookly-week-days bookly-table ab-left">
                        <?php foreach ( $wp_locale->weekday_abbrev as $weekday_abbrev ) : ?>
                            <div>
                                <div class="bookly-font-bold"><?php echo $weekday_abbrev ?></div>
                                <label class="active">
                                    <input class="bookly-week-day" value="1" checked="checked" type="checkbox">
                                </label>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
                <div class="ab-time-range ab-left">
                    <div class="ab-formGroup ab-time-from ab-left">
                        <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderLabel( array( 'bookly_l10n_label_start_from', ) ) ?>
                        <div>
                            <select class="ab-select-time-from">
                                <?php for ( $i = 28800; $i <= 64800; $i += 3600 ) : ?>
                                    <option><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?></option>
                                <?php endfor ?>
                            </select>
                        </div>
                    </div>
                    <div class="ab-formGroup ab-time-to ab-left">
                        <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderLabel( array( 'bookly_l10n_label_finish_by', ) ) ?>
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
            <div class="bookly-box bookly-nav-steps">
                <div class="ab-right ab-mobile-prev-step ab-btn ab-none">
                    <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_back' ), 'bookly-js-text-back' ) ?>
                </div>
                <div class="bookly-next-step ab-btn">
                    <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_next' ), 'bookly-js-text-next' ) ?>
                </div>
                <button class="bookly-go-to-cart bookly-round bookly-round-md ladda-button"><span><img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/frontend/resources/images/cart.png' ) ?>" /></span></button>
            </div>
        </div>
    </div>
</div>
<div style="display: none">
    <?php foreach ( array( 'bookly_l10n_required_service', 'bookly_l10n_required_name', 'bookly_l10n_required_phone', 'bookly_l10n_required_email', 'bookly_l10n_required_employee', 'bookly_l10n_required_location' ) as $validator ) : ?>
        <div id="<?php echo $validator ?>"><?php echo get_option( $validator ) ?></div>
    <?php endforeach ?>
</div>
<style id="ab--style-arrow">
    .picker__nav--next:before { border-left: 6px solid <?php echo get_option( 'bookly_app_color' ) ?>!important; }
    .picker__nav--prev:before { border-right: 6px solid <?php echo get_option( 'bookly_app_color' ) ?>!important; }
</style>