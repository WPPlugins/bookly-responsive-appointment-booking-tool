<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var \BooklyLite\Lib\UserBookingData $userData */
    echo $progress_tracker;
?>
<div class="bookly-service-step">
    <div class="bookly-box ab-bold"><?php echo $info_text ?></div>
    <div class="ab-mobile-step_1">
        <div class="bookly-js-chain-item bookly-js-draft bookly-table bookly-box" style="display: none;">
            <?php do_action( 'bookly_render_chain_item_head' ) ?>
            <div class="ab-formGroup">
                <label><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_category' ) ?></label>
                <div>
                    <select class="ab-select-mobile ab-select-category">
                        <option value=""><?php echo esc_html( \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_option_category' ) ) ?></option>
                    </select>
                </div>
            </div>
            <div class="ab-formGroup">
                <label><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' ) ?></label>
                <div>
                    <select class="ab-select-mobile ab-select-service">
                        <option value=""><?php echo esc_html( \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_option_service' ) ) ?></option>
                    </select>
                </div>
                <div class="ab-select-service-error ab-label-error" style="display: none">
                    <?php echo esc_html( \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_required_service' ) ) ?>
                </div>
            </div>
            <div class="ab-formGroup">
                <label><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ?></label>
                <div>
                    <select class="ab-select-mobile ab-select-employee">
                        <option value=""><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_option_employee' ) ?></option>
                    </select>
                </div>
                <div class="ab-select-employee-error ab-label-error" style="display: none">
                    <?php echo esc_html( \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_required_employee' ) ) ?>
                </div>
            </div>
            <div class="ab-formGroup">
                <label><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_number_of_persons' ) ?></label>
                <div>
                    <select class="ab-select-mobile ab-select-number-of-persons">
                        <option value="1">1</option>
                    </select>
                </div>
            </div>
            <?php do_action( 'bookly_render_chain_item_tail' ) ?>
        </div>
        <div class="bookly-nav-steps bookly-box">
            <button class="ab-right ab-mobile-next-step ab-btn ab-none ladda-button" data-style="zoom-in" data-spinner-size="40">
                <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_button_next' ) ?></span>
            </button>
            <?php if ( $show_cart_btn ) : ?>
                <button class="bookly-go-to-cart bookly-round bookly-round-md ladda-button" data-style="zoom-in" data-spinner-size="30"><span class="ladda-label"><img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/frontend/resources/images/cart.png' ) ?>" /></span></button>
            <?php endif ?>
        </div>
    </div>
    <div class="ab-mobile-step_2">
        <div class="bookly-box">
            <div class="ab-left ab-mob-float-none">
                <div class="ab-available-date ab-left ab-mob-float-none">
                    <div class="ab-formGroup">
                        <span class="ab-bold"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_select_date' ) ?></span>
                        <div>
                           <input class="ab-date-from" type="text" value="" data-value="<?php echo esc_attr( $userData->get( 'date_from' ) ) ?>" />
                        </div>
                    </div>
                </div>
                <?php if ( ! empty ( $days ) ) : ?>
                    <div class="bookly-week-days bookly-table ab-left ab-mob-float-none">
                        <?php foreach ( $days as $key => $day ) : ?>
                            <div>
                                <span class="ab-bold"><?php echo $day ?></span>
                                <label<?php if ( in_array( $key, $days_checked ) ) : ?> class="active"<?php endif ?>>
                                    <input class="bookly-week-day bookly-week-day-<?php echo $key ?>" value="<?php echo $key ?>" <?php checked( in_array( $key, $days_checked ) ) ?> type="checkbox"/>
                                </label>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
            </div>
            <?php if ( ! empty ( $times ) ) : ?>
                <div class="ab-time-range ab-left ab-mob-float-none">
                    <div class="ab-formGroup ab-time-from ab-left">
                        <span class="ab-bold"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_start_from' ) ?></span>
                        <div>
                            <select class="ab-select-time-from">
                                <?php foreach ( $times as $key => $time ) : ?>
                                    <option value="<?php echo $key ?>"<?php selected( $userData->get( 'time_from' ) == $key ) ?>><?php echo $time ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="ab-formGroup ab-time-to ab-left">
                        <span class="ab-bold"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_finish_by' ) ?></span>
                        <div>
                            <select class="ab-select-time-to">
                                <?php foreach ( $times as $key => $time ) : ?>
                                    <option value="<?php echo $key ?>"<?php selected( $userData->get( 'time_to' ) == $key ) ?>><?php echo $time ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>
        <div class="bookly-box bookly-nav-steps">
            <button class="ab-left ab-mobile-prev-step ab-btn ab-none ladda-button" data-style="zoom-in" data-spinner-size="40">
                <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_button_back' ) ?></span>
            </button>
            <button class="bookly-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
                <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_button_next' ) ?></span>
            </button>
            <?php if ( $show_cart_btn ) : ?>
                <button class="bookly-go-to-cart bookly-round bookly-round-md ladda-button" data-style="zoom-in" data-spinner-size="30"><span class="ladda-label"><img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/frontend/resources/images/cart.png' ) ?>" /></span></button>
            <?php endif ?>
        </div>
    </div>
</div>