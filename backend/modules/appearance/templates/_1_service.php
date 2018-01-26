<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Lib\Utils\Price;
use BooklyLite\Lib\Utils\DateTime;

/**
 * @var BooklyLite\Backend\Modules\Appearance\Lib\Helper $editable
 * @var WP_Locale $wp_locale
 */
global $wp_locale;
?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <div class="bookly-service-step">
        <div class="bookly-box">
            <span class="bookly-bold bookly-desc">
                <?php $editable::renderText( 'bookly_l10n_info_service_step' ) ?>
            </span>
        </div>
        <div class="bookly-mobile-step-1 bookly-js-mobile-step-1 bookly-box">
            <div class="bookly-js-chain-item bookly-table bookly-box">
                <?php if ( \BooklyLite\Lib\Config::locationsEnabled() ) : ?>
                    <div class="bookly-form-group">
                        <?php \BooklyLite\Lib\Proxy\Locations::renderAppearance() ?>
                    </div>
                <?php endif ?>
                <div class="bookly-form-group">
                    <?php $editable::renderLabel( array( 'bookly_l10n_label_category', 'bookly_l10n_option_category', ) ) ?>
                    <div>
                        <select class="bookly-select-mobile bookly-js-select-category">
                            <option value="" class="bookly-js-option bookly_l10n_option_category"><?php echo esc_html( get_option( 'bookly_l10n_option_category' ) ) ?></option>
                            <option value="1">Cosmetic Dentistry</option>
                            <option value="2">Invisalign</option>
                            <option value="3">Orthodontics</option>
                            <option value="4">Dentures</option>
                        </select>
                    </div>
                </div>
                <div class="bookly-form-group">
                    <?php $editable::renderLabel( array(
                        'bookly_l10n_label_service',
                        'bookly_l10n_option_service',
                        'bookly_l10n_required_service',
                    ) ) ?>
                    <div>
                        <select class="bookly-select-mobile bookly-js-select-service">
                            <option value="0" class="bookly-js-option bookly_l10n_option_service"><?php echo esc_html( get_option( 'bookly_l10n_option_service' ) ) ?></option>
                            <option value="1" class="service-name-duration">Crown and Bridge (<?php echo DateTime::secondsToInterval( 3600 ) ?>)</option>
                            <option value="-1" class="service-name">Crown and Bridge</option>
                            <option value="2" class="service-name-duration">Teeth Whitening (<?php echo DateTime::secondsToInterval( 3600 * 2 ) ?>)</option>
                            <option value="-2" class="service-name">Teeth Whitening</option>
                            <option value="3" class="service-name-duration">Veneers (<?php echo DateTime::secondsToInterval( 3600 * 12 ) ?>)</option>
                            <option value="-3" class="service-name">Veneers</option>
                            <option value="4" class="service-name-duration">Invisalign (invisable braces) (<?php echo DateTime::secondsToInterval( 3600 * 24 ) ?>)</option>
                            <option value="-4" class="service-name">Invisalign (invisable braces)</option>
                            <option value="5" class="service-name-duration">Orthodontics (braces) (<?php echo DateTime::secondsToInterval( 3600 * 8 ) ?>)</option>
                            <option value="-5" class="service-name">Orthodontics (braces)</option>
                            <option value="6" class="service-name-duration">Wisdom tooth Removal (<?php echo DateTime::secondsToInterval( 3600 * 6 ) ?>)</option>
                            <option value="-6" class="service-name">Wisdom tooth Removal</option>
                            <option value="7" class="service-name-duration">Root Canal Treatment (<?php echo DateTime::secondsToInterval( 3600 * 16 ) ?>)</option>
                            <option value="-7" class="service-name">Root Canal Treatment</option>
                            <option value="8" class="service-name-duration">Dentures (<?php echo DateTime::secondsToInterval( 3600 * 48 ) ?>)</option>
                            <option value="-8" class="service-name">Dentures</option>
                        </select>
                    </div>
                </div>
                <div class="bookly-form-group">
                    <?php $editable::renderLabel( array(
                        'bookly_l10n_label_employee',
                        'bookly_l10n_option_employee',
                        'bookly_l10n_required_employee',
                    ) ) ?>
                    <div>
                        <select class="bookly-select-mobile bookly-js-select-employee">
                            <option value="0" class="bookly-js-option bookly_l10n_option_employee"><?php echo esc_html( get_option( 'bookly_l10n_option_employee' ) ) ?></option>
                            <option value="1" class="employee-name-price">Nick Knight (<?php echo Price::format( 350 ) ?>)</option>
                            <option value="-1" class="employee-name">Nick Knight</option>
                            <option value="2" class="employee-name-price">Jane Howard (<?php echo Price::format( 375 ) ?>)</option>
                            <option value="-2" class="employee-name">Jane Howard</option>
                            <option value="3" class="employee-name-price">Ashley Stamp (<?php echo Price::format( 300 ) ?>)</option>
                            <option value="-3" class="employee-name">Ashley Stamp</option>
                            <option value="4" class="employee-name-price">Bradley Tannen (<?php echo Price::format( 400 ) ?>)</option>
                            <option value="-4" class="employee-name">Bradley Tannen</option>
                            <option value="5" class="employee-name-price">Wayne Turner (<?php echo Price::format( 350 ) ?>)</option>
                            <option value="-5" class="employee-name">Wayne Turner</option>
                            <option value="6" class="employee-name-price">Emily Taylor (<?php echo Price::format( 350 ) ?>)</option>
                            <option value="-6" class="employee-name">Emily Taylor</option>
                            <option value="7" class="employee-name-price">Hugh Canberg (<?php echo Price::format( 380 ) ?>)</option>
                            <option value="-7" class="employee-name">Hugh Canberg</option>
                            <option value="8" class="employee-name-price">Jim Gonzalez (<?php echo Price::format( 390 ) ?>)</option>
                            <option value="-8" class="employee-name">Jim Gonzalez</option>
                            <option value="9" class="employee-name-price">Nancy Stinson (<?php echo Price::format( 360 ) ?>)</option>
                            <option value="-9" class="employee-name">Nancy Stinson</option>
                            <option value="10" class="employee-name-price">Marry Murphy (<?php echo Price::format( 350 ) ?>)</option>
                            <option value="-10" class="employee-name">Marry Murphy</option>
                        </select>
                    </div>
                </div>
                <div class="bookly-form-group" style="display: none">
                    <?php $editable::renderLabel( array( 'bookly_l10n_label_number_of_persons', ) ) ?>
                    <div>
                        <select class="bookly-select-mobile bookly-js-select-number-of-persons">
                            <option>1</option>
                            <option>2</option>
                            <option>3</option>
                        </select>
                    </div>
                </div>
                <?php if ( \BooklyLite\Lib\Config::multiplyAppointmentsEnabled() ) : ?>
                    <div class="bookly-form-group">
                        <?php \BooklyLite\Lib\Proxy\MultiplyAppointments::renderAppearance() ?>
                    </div>
                <?php endif ?>
                <?php if ( \BooklyLite\Lib\Config::chainAppointmentsEnabled() ) : ?>
                    <div class="bookly-form-group">
                        <label></label>
                        <div>
                            <button class="bookly-round"><i class="bookly-icon-sm bookly-icon-plus"></i></button>
                        </div>
                    </div>
                <?php endif ?>
            </div>
            <div class="bookly-right bookly-mobile-next-step bookly-js-mobile-next-step bookly-btn bookly-none">
                <?php $editable::renderString( array( 'bookly_l10n_step_service_mobile_button_next' ) ) ?>
            </div>
        </div>
        <div class="bookly-mobile-step-2 bookly-js-mobile-step-2">
            <div class="bookly-box">
                <div class="bookly-left">
                    <div class="bookly-available-date bookly-js-available-date bookly-left">
                        <div class="bookly-form-group">
                            <?php $editable::renderLabel( array( 'bookly_l10n_label_select_date', ) ) ?>
                            <div>
                               <input class="bookly-date-from bookly-js-date-from" style="background-color: #fff;" type="text" data-value="<?php echo date( 'Y-m-d' ) ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="bookly-week-days bookly-js-week-days bookly-table bookly-left">
                        <?php foreach ( $wp_locale->weekday_abbrev as $weekday_abbrev ) : ?>
                            <div>
                                <div class="bookly-font-bold"><?php echo $weekday_abbrev ?></div>
                                <label class="active">
                                    <input class="bookly-js-week-day" value="1" checked="checked" type="checkbox">
                                </label>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
                <div class="bookly-time-range bookly-js-time-range bookly-left">
                    <div class="bookly-form-group bookly-time-from bookly-left">
                        <?php $editable::renderLabel( array( 'bookly_l10n_label_start_from', ) ) ?>
                        <div>
                            <select class="bookly-js-select-time-from">
                                <?php for ( $i = 28800; $i <= 64800; $i += 3600 ) : ?>
                                    <option><?php echo DateTime::formatTime( $i ) ?></option>
                                <?php endfor ?>
                            </select>
                        </div>
                    </div>
                    <div class="bookly-form-group bookly-time-to bookly-left">
                        <?php $editable::renderLabel( array( 'bookly_l10n_label_finish_by', ) ) ?>
                        <div>
                            <select class="bookly-js-select-time-to">
                                <?php for ( $i = 28800; $i <= 64800; $i += 3600 ) : ?>
                                    <option<?php selected( $i == 64800 ) ?>><?php echo DateTime::formatTime( $i ) ?></option>
                                <?php endfor ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bookly-box bookly-nav-steps">
                <div class="bookly-right bookly-mobile-prev-step bookly-js-mobile-prev-step bookly-btn bookly-none">
                    <?php $editable::renderString( array( 'bookly_l10n_button_back' ) ) ?>
                </div>
                <div class="bookly-next-step bookly-js-next-step bookly-btn">
                    <?php $editable::renderString( array( 'bookly_l10n_step_service_button_next' ) ) ?>
                </div>
                <button class="bookly-go-to-cart bookly-js-go-to-cart bookly-round bookly-round-md ladda-button"><span><img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/frontend/resources/images/cart.png' ) ?>" /></span></button>
            </div>
        </div>
    </div>
</div>
<div style="display: none">
    <?php foreach ( array( 'bookly_l10n_required_service', 'bookly_l10n_required_name', 'bookly_l10n_required_phone', 'bookly_l10n_required_email', 'bookly_l10n_required_employee', 'bookly_l10n_required_location' ) as $validator ) : ?>
        <div class="bookly-js-option <?php echo $validator ?>"><?php echo get_option( $validator ) ?></div>
    <?php endforeach ?>
</div>
<style id="bookly-pickadate-style"></style>
