<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <div class="bookly-box">
        <span data-notes="<?php echo esc_attr( $this->render( '_codes', array( 'step' => 3 ), false ) ) ?>" data-placement="bottom" data-option-default="<?php form_option( 'bookly_l10n_info_time_step' ) ?>" class="bookly-editable" id="bookly_l10n_info_time_step" data-type="textarea"><?php echo esc_html( get_option( 'bookly_l10n_info_time_step' ) ) ?></span>
    </div>
    <!-- timeslots -->
    <div class="ab-time-step">
        <div class="ab-columnizer-wrap">
        <div class="ab-columnizer">
            <div class="ab-time-screen ab-day-columns" style="display: <?php echo get_option( 'bookly_app_show_day_one_column' ) == 1 ? ' none' : 'block' ?>">
                <div class="ab-input-wrap ab-slot-calendar">
                    <span class="ab-date-wrap">
                        <input style="display: none" class="ab-selected-date ab-formElement" type="text" data-value="<?php echo date( 'Y-m-d' ) ?>" />
                    </span>
                </div>
                <div class="ab-column col1">
                    <button class="ab-day ab-first-child"><?php echo date_i18n( 'D, M d', current_time( 'timestamp' ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 57600; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i>
                                <?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="ab-column col2">
                    <button class="ab-hour ladda-button ab-last-child">
                        <span class="ladda-label">
                            <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( 61200 ) ?>
                        </span>
                    </button>
                    <button class="ab-day ab-first-child" style="display: <?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : 'block' ?>"><?php echo date_i18n( 'D, M d', strtotime( '+1 day', current_time( 'timestamp' ) ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 54000; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>" style="display: <?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : 'block' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="ab-column col3" style="display: <?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : 'inline-block' ?>">
                    <?php for ( $i = 57600; $i <= 61200; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                    <button class="ab-day ab-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+2 days', current_time('timestamp') ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 50400; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="ab-column col4" style="display: <?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : 'inline-block' ?>">
                    <?php for ( $i = 54000; $i <= 61200; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                    <button class="ab-day ab-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+3 days', current_time( 'timestamp' ) ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 46800; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="ab-column col5" style="display:<?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : ' inline-block' ?>">
                    <?php for ( $i = 50400; $i <= 61200; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                    <button class="ab-day ab-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+4 days', current_time( 'timestamp' ) ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 43200; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="ab-column col6" style="display: <?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : 'inline-block' ?>">
                    <?php for ( $i = 46800; $i <= 61200; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                    <button class="ab-day ab-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+5 days', current_time( 'timestamp' ) ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 39600; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="ab-column col7" style="display:<?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : ' inline-block' ?>">
                    <?php for ( $i = 43200; $i <= 61200; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                    <button class="ab-day ab-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+6 days', current_time( 'timestamp' ) ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 36000; $i += 3600 ) : ?>
                        <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
            </div>

            <div class="ab-time-screen ab-day-one-column" style="display: <?php echo get_option( 'bookly_app_show_day_one_column' ) == 1 ? ' block' : 'none' ?>">
                <div class="ab-input-wrap ab-slot-calendar">
                    <span class="ab-date-wrap">
                        <input style="display: none" class="ab-selected-date ab-formElement" type="text" data-value="<?php echo date( 'Y-m-d' ) ?>" />
                    </span>
                </div>
                <?php for ( $i = 1; $i <= 7; ++ $i ) : ?>
                    <div class="ab-column col<?php echo $i ?>">
                        <button class="ab-day ab-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+' . ( $i - 1 ) . ' days', current_time( 'timestamp' ) ) ) ?></button>
                        <?php for ( $j = 28800; $j <= 61200; $j += 3600 ) : ?>
                            <button class="ab-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="ab-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $j ) ?>
                            </span>
                            </button>
                        <?php endfor ?>
                    </div>
                <?php endfor ?>
            </div>
        </div>
    </div>
    </div>
    <div class="bookly-box bookly-nav-steps">
        <button class="ab-time-next ab-btn ab-right ladda-button">
            <span class="ab_label">&gt;</span>
        </button>
        <button class="ab-time-prev ab-btn ab-right ladda-button">
            <span class="ab_label">&lt;</span>
        </button>
        <div class="bookly-back-step ab-btn">
            <?php \BooklyLite\Backend\Modules\Appearance\Lib\Helper::renderSpan( array( 'bookly_l10n_button_back' ), 'bookly-js-text-back' ) ?>
        </div>
        <button class="bookly-go-to-cart bookly-round bookly-round-md ladda-button" data-style="zoom-in" data-spinner-size="30"><span class="ladda-label"><img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/frontend/resources/images/cart.png' ) ?>" /></span></button>
    </div>
</div>
