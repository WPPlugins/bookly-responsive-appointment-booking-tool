<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Frontend\Modules\Booking\Proxy;
/** @var Bookly\Lib\UserBookingData $userData */
echo $progress_tracker;
?>
<div class="bookly-service-step">
    <div class="bookly-box bookly-bold"><?php echo $info_text ?></div>
    <div class="bookly-mobile-step-1 bookly-js-mobile-step-1">
        <div class="bookly-js-chain-item bookly-js-draft bookly-table bookly-box" style="display: none;">
            <?php Proxy\Shared::renderChainItemHead() ?>
            <div class="bookly-form-group">
                <label><?php echo Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_category' ) ?></label>
                <div>
                    <select class="bookly-select-mobile bookly-js-select-category">
                        <option value=""><?php echo esc_html( Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_option_category' ) ) ?></option>
                    </select>
                </div>
            </div>
            <div class="bookly-form-group">
                <label><?php echo Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' ) ?></label>
                <div>
                    <select class="bookly-select-mobile bookly-js-select-service">
                        <option value=""><?php echo esc_html( \Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_option_service' ) ) ?></option>
                    </select>
                </div>
                <div class="bookly-js-select-service-error bookly-label-error" style="display: none">
                    <?php echo esc_html( Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_required_service' ) ) ?>
                </div>
            </div>
            <div class="bookly-form-group">
                <label><?php echo Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ?></label>
                <div>
                    <select class="bookly-select-mobile bookly-js-select-employee">
                        <option value=""><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_option_employee' ) ?></option>
                    </select>
                </div>
                <div class="bookly-js-select-employee-error bookly-label-error" style="display: none">
                    <?php echo esc_html( \Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_required_employee' ) ) ?>
                </div>
            </div>
            <?php Proxy\Shared::renderChainItemTail() ?>
            <?php Proxy\Shared::renderChainItemTailTip() ?>
        </div>
        <?php Proxy\ChainAppointments::renderBookMore() ?>
        <div class="bookly-nav-steps bookly-box">
            <?php if ( $show_cart_btn ) : ?>
                <?php Proxy\Cart::renderButton() ?>
            <?php endif ?>
            <div class="<?php echo get_option( 'bookly_app_align_buttons_left' ) ? 'bookly-left' : 'bookly-right' ?>">
                <button class="bookly-right bookly-mobile-next-step bookly-js-mobile-next-step bookly-btn bookly-none ladda-button" data-style="zoom-in" data-spinner-size="40">
                    <span class="ladda-label"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_service_mobile_button_next' ) ?></span>
                </button>
            </div>
        </div>
    </div>
    <div class="bookly-mobile-step-2 bookly-js-mobile-step-2">
        <div class="bookly-box">
            <div class="bookly-left bookly-mobile-float-none">
                <div class="bookly-available-date bookly-js-available-date bookly-left bookly-mobile-float-none">
                    <div class="bookly-form-group">
                        <span class="bookly-bold"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_select_date' ) ?></span>
                        <div>
                           <input class="bookly-date-from bookly-js-date-from" type="text" value="" data-value="<?php echo esc_attr( $userData->getDateFrom() ) ?>" />
                        </div>
                    </div>
                </div>
                <?php if ( ! empty ( $days ) ) : ?>
                    <div class="bookly-week-days bookly-js-week-days bookly-table bookly-left bookly-mobile-float-none">
                        <?php foreach ( $days as $key => $day ) : ?>
                            <div>
                                <span class="bookly-bold"><?php echo $day ?></span>
                                <label<?php if ( in_array( $key, $days_checked ) ) : ?> class="active"<?php endif ?>>
                                    <input class="bookly-js-week-day bookly-js-week-day-<?php echo $key ?>" value="<?php echo $key ?>" <?php checked( in_array( $key, $days_checked ) ) ?> type="checkbox"/>
                                </label>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
            </div>
            <?php if ( ! empty ( $times ) ) : ?>
                <div class="bookly-time-range bookly-js-time-range bookly-left bookly-mobile-float-none">
                    <div class="bookly-form-group bookly-time-from bookly-left">
                        <span class="bookly-bold"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_start_from' ) ?></span>
                        <div>
                            <select class="bookly-js-select-time-from">
                                <?php foreach ( $times as $key => $time ) : ?>
                                    <option value="<?php echo $key ?>"<?php selected( $userData->getTimeFrom() == $key ) ?>><?php echo $time ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="bookly-form-group bookly-time-to bookly-left">
                        <span class="bookly-bold"><?php echo \Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_finish_by' ) ?></span>
                        <div>
                            <select class="bookly-js-select-time-to">
                                <?php foreach ( $times as $key => $time ) : ?>
                                    <option value="<?php echo $key ?>"<?php selected( $userData->getTimeTo() == $key ) ?>><?php echo $time ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>
        <div class="bookly-box bookly-nav-steps">
            <button class="bookly-left bookly-mobile-prev-step bookly-js-mobile-prev-step bookly-btn bookly-none ladda-button" data-style="zoom-in" data-spinner-size="40">
                <span class="ladda-label"><?php echo Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_button_back' ) ?></span>
            </button>
            <?php if ( $show_cart_btn ) : ?>
                <?php Proxy\Cart::renderButton() ?>
            <?php endif ?>
            <div class="<?php echo get_option( 'bookly_app_align_buttons_left' ) ? 'bookly-left' : 'bookly-right' ?>">
                <button class="bookly-next-step bookly-js-next-step bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
                    <span class="ladda-label"><?php echo Bookly\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_service_button_next' ) ?></span>
                </button>
            </div>
        </div>
    </div>
</div>