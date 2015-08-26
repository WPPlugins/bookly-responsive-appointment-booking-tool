<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /**
     * @var AB_UserBookingData $userData
     * @var string $info_text
     */
    echo $progress_tracker;
?>

<div class="ab-first-step">
    <div class="ab-row-fluid">
        <div class="ab-bold ab-desc"><?php _e( $info_text, 'bookly' ) ?></div>
    </div>
    <form>
        <div class="ab-mobile-step_1 ab-row-fluid">
            <div class="ab-formGroup ab-category ab-left">
                <label class="ab-formLabel"><?php echo AB_Utils::getTranslatedOption( 'ab_appearance_text_label_category' ) ?></label>
                <div class="ab-formField">
                    <select class="ab-formElement ab-select-mobile ab-select-category">
                      <option value=""><?php echo esc_html( AB_Utils::getTranslatedOption( 'ab_appearance_text_option_category' ) ) ?></option>
                    </select>
                </div>
            </div>
            <div class="ab-formGroup ab-service ab-left">
                <label class="ab-formLabel">
                    <?php echo AB_Utils::getTranslatedOption( 'ab_appearance_text_label_service' ) ?>
                </label>
                <div class="ab-formField">
                    <select class="ab-formElement ab-select-mobile ab-select-service">
                        <option value=""><?php echo esc_html( AB_Utils::getTranslatedOption( 'ab_appearance_text_option_service' ) ) ?></option>
                    </select>
                </div>
                <div class="ab-select-service-error ab-label-error" style="padding-top: 5px; display: none"><?php _e( 'Please select a service', 'bookly' ) ?></div>
            </div>
            <div class="ab-formGroup ab-employee ab-left">
                <label class="ab-formLabel"><?php echo AB_Utils::getTranslatedOption( 'ab_appearance_text_label_employee' ) ?></label>
                <div class="ab-formField">
                    <select class="ab-formElement ab-select-mobile ab-select-employee">
                      <option value=""><?php echo AB_Utils::getTranslatedOption( 'ab_appearance_text_option_employee' ) ?></option>
                    </select>
                </div>
            </div>
            <div class="ab-formGroup ab-number-of-persons ab-left">
                <label class="ab-formLabel"><?php echo AB_Utils::getTranslatedOption( 'ab_appearance_text_label_number_of_persons' ) ?></label>
                <div class="ab-formField">
                    <select class="ab-formElement ab-select-mobile ab-select-number-of-persons">
                        <option value="1">1</option>
                    </select>
                </div>
            </div>

            <div class="ab-nav-steps ab-clear ab-row-fluid">
                <button class="ab-right ab-mobile-next-step ab-btn ab-none ladda-button" data-style="zoom-in" data-spinner-size="40">
                    <span class="ladda-label"><?php _e( 'Next', 'bookly' ) ?></span>
                </button>
            </div>
        </div>
        <div class="ab-mobile-step_2">
            <div class="ab-row-fluid">
                <div class="ab-left ab-available-date ab-formGroup ab-lastGroup" style="width: auto">
                    <label class="ab-nowrap ab-bold"><?php echo AB_Utils::getTranslatedOption( 'ab_appearance_text_label_select_date' ) ?></label>
                    <div class="ab-input-wrap ab-formField">
                        <span class="ab-date-wrap">
                           <input class="ab-date-from ab-formElement" type="text" value="" data-value="<?php echo esc_attr( $userData->get( 'date_from' ) ) ?>" />
                        </span>
                    </div>
                </div>
                <?php if ( ! empty ( $days ) ) : ?>
                    <div class="ab-left ab-available-days">
                        <ul class="ab-week-days">
                            <?php foreach ( $days as $key => $day ) : ?>
                                <li>
                                    <div class="ab-bold"><?php echo $day ?></div>
                                    <label<?php if ( in_array( $key, $days_checked ) ): ?> class="active"<?php endif ?>>
                                        <input class="ab-week-day ab-week-day-<?php echo $key ?>" value="<?php echo $key ?>" <?php checked( in_array( $key, $days_checked ) ) ?> type="checkbox"/>
                                    </label>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if ( ! empty ( $times ) ) : ?>
                    <div class="ab-left ab-time-range">
                        <div class="ab-left ab-time-from">
                            <label class="ab-bold"><?php echo AB_Utils::getTranslatedOption( 'ab_appearance_text_label_start_from' ) ?></label>
                            <div>
                                <select class="ab-select-time-from">
                                    <?php foreach ( $times as $key => $time ): ?>
                                        <option value="<?php echo $key ?>"<?php selected( $userData->get( 'time_from' ) == $key ) ?>><?php echo $time ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                        <div class="ab-left ab-time-to">
                            <label class="ab-bold"><?php echo AB_Utils::getTranslatedOption( 'ab_appearance_text_label_finish_by' ) ?></label>
                            <div>
                                <select class="ab-select-time-to">
                                    <?php foreach ( $times as $key => $time ) : ?>
                                        <option value="<?php echo $key ?>"<?php selected( $userData->get( 'time_to' ) == $key ) ?>><?php echo $time ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                        <div class="ab-select-time-error ab-label-error ab-clear" style="text-align:right; padding-top: 5px; display: none"><?php _e( 'The start time must be less than the end time', 'bookly' ) ?></div>
                    </div>
                <?php endif ?>
            </div>
            <div class="ab-nav-steps ab-clear ab-row-fluid">
                <button class="ab-left ab-mobile-prev-step ab-btn ab-none ladda-button" data-style="zoom-in" data-spinner-size="40">
                    <span class="ladda-label"><?php _e( 'Back', 'bookly' ) ?></span>
                </button>
                <button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
                    <span class="ladda-label"><?php _e( 'Next', 'bookly' ) ?></span>
                </button>
            </div>
        </div>
    </form>
</div>