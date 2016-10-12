<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var \BooklyLite\Lib\UserBookingData $userData */
    echo $progress_tracker;
?>
<div class="ab-service-step">
    <div class="ab-row ab-bold"><?php echo $info_text ?></div>
    <div class="ab-mobile-step_1">
        <div class="bookly-js-chain-item bookly-js-draft bookly-table ab-row" style="display: none;">
            <?php do_action( 'bookly_render_chain_item_head' ) ?>
            <div class="ab-formGroup">
                <label><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_category' ) ?></label>
                <div>
                    <select class="ab-select-mobile ab-select-category">
                        <option value=""><?php echo esc_html( \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_option_category' ) ) ?></option>
                    </select>
                </div>
            </div>
            <div class="ab-formGroup">
                <label><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_service' ) ?></label>
                <div>
                    <select class="ab-select-mobile ab-select-service">
                        <option value=""><?php echo esc_html( \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_option_service' ) ) ?></option>
                    </select>
                </div>
                <div class="ab-select-service-error ab-label-error" style="display: none">
                    <?php echo esc_html( \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_required_service' ) ) ?>
                </div>
            </div>
            <div class="ab-formGroup">
                <label><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_employee' ) ?></label>
                <div>
                    <select class="ab-select-mobile ab-select-employee">
                        <option value=""><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_option_employee' ) ?></option>
                    </select>
                </div>
                <div class="ab-select-employee-error ab-label-error" style="display: none">
                    <?php echo esc_html( \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_required_employee' ) ) ?>
                </div>
            </div>
            <div class="ab-formGroup">
                <label><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_number_of_persons' ) ?></label>
                <div>
                    <select class="ab-select-mobile ab-select-number-of-persons">
                        <option value="1">1</option>
                    </select>
                </div>
            </div>
            <?php do_action( 'bookly_render_chain_item_tail' ) ?>
        </div>
        <div class="ab-nav-steps ab-row">
            <button class="ab-right ab-mobile-next-step ab-btn ab-none ladda-button" data-style="zoom-in" data-spinner-size="40">
                <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) ?></span>
            </button>
            <?php if ( $show_cart_btn ) : ?>
                <button class="ab-left ab-goto-cart bookly-round-button ladda-button" data-style="zoom-in" data-spinner-size="30"><span class="ladda-label"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/cart.png' ) ?>" /></span></button>
            <?php endif ?>
        </div>
    </div>
    <div class="ab-mobile-step_2">
        <div class="ab-row">
            <div class="ab-left ab-mob-float-none">
                <div class="ab-available-date ab-left ab-mob-float-none">
                    <div class="ab-formGroup">
                        <span class="ab-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_select_date' ) ?></span>
                        <div>
                           <input class="ab-date-from" type="text" value="" data-value="<?php echo esc_attr( $userData->get( 'date_from' ) ) ?>" />
                        </div>
                    </div>
                </div>
                <?php if ( ! empty ( $days ) ) : ?>
                    <div class="ab-week-days bookly-table ab-left ab-mob-float-none">
                        <?php foreach ( $days as $key => $day ) : ?>
                            <div>
                                <span class="ab-bold"><?php echo $day ?></span>
                                <label<?php if ( in_array( $key, $days_checked ) ) : ?> class="active"<?php endif ?>>
                                    <input class="ab-week-day ab-week-day-<?php echo $key ?>" value="<?php echo $key ?>" <?php checked( in_array( $key, $days_checked ) ) ?> type="checkbox"/>
                                </label>
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
            </div>
            <?php if ( ! empty ( $times ) ) : ?>
                <div class="ab-time-range ab-left ab-mob-float-none">
                    <div class="ab-formGroup ab-time-from ab-left">
                        <span class="ab-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_start_from' ) ?></span>
                        <div>
                            <select class="ab-select-time-from">
                                <?php foreach ( $times as $key => $time ) : ?>
                                    <option value="<?php echo $key ?>"<?php selected( $userData->get( 'time_from' ) == $key ) ?>><?php echo $time ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="ab-formGroup ab-time-to ab-left">
                        <span class="ab-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_label_finish_by' ) ?></span>
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
        <div class="ab-row ab-nav-steps">
            <button class="ab-left ab-mobile-prev-step ab-btn ab-none ladda-button" data-style="zoom-in" data-spinner-size="40">
                <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) ?></span>
            </button>
            <button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
                <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ) ?></span>
            </button>
            <?php if ( $show_cart_btn ) : ?>
                <button class="ab-left ab-goto-cart bookly-round-button ladda-button" data-style="zoom-in" data-spinner-size="30"><span class="ladda-label"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/cart.png' ) ?>" /></span></button>
            <?php endif ?>
        </div>
    </div>
</div>