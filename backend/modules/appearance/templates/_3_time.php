<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Backend\Modules\Appearance\Components;
/** @var BooklyLite\Backend\Modules\Appearance\Lib\Helper $editable */
?>
<div class="bookly-form">
    <?php include '_progress_tracker.php' ?>

    <div class="bookly-box">
        <?php $editable::renderText( 'bookly_l10n_info_time_step', Components::getInstance()->renderCodes( array( 'step' => 3 ), false ) ) ?>
        <div class="bookly-holder bookly-label-error bookly-bold">
            <?php $editable::renderText( 'bookly_l10n_step_time_slot_not_available', null, 'bottom', __( 'Visible when the chosen time slot has been already booked', 'bookly' ) ) ?>
        </div>
    </div>
    <!-- timeslots -->
    <div class="bookly-time-step">
        <div class="bookly-columnizer-wrap">
        <div class="bookly-columnizer">
            <div id="bookly-day-multi-columns" class="bookly-time-screen" style="display: <?php echo get_option( 'bookly_app_show_day_one_column' ) == 1 ? ' none' : 'block' ?>">
                <div class="bookly-input-wrap bookly-slot-calendar bookly-js-slot-calendar">
                    <span class="bookly-date-wrap">
                        <input style="display: none" class="bookly-js-selected-date bookly-form-element" type="text" data-value="<?php echo date( 'Y-m-d' ) ?>" />
                    </span>
                </div>
                <div class="bookly-column col1">
                    <button class="bookly-day bookly-js-first-child"><?php echo date_i18n( 'D, M d', current_time( 'timestamp' ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 57600; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i>
                                <?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="bookly-column col2">
                    <button class="bookly-hour ladda-button bookly-last-child">
                        <span class="ladda-label">
                            <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( 61200 ) ?>
                        </span>
                    </button>
                    <button class="bookly-day bookly-js-first-child" style="display: <?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : 'block' ?>"><?php echo date_i18n( 'D, M d', strtotime( '+1 day', current_time( 'timestamp' ) ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 54000; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>" style="display: <?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : 'block' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="bookly-column col3" style="display: <?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : 'inline-block' ?>">
                    <?php for ( $i = 57600; $i <= 61200; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                    <button class="bookly-day bookly-js-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+2 days', current_time('timestamp') ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 50400; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="bookly-column col4" style="display: <?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : 'inline-block' ?>">
                    <?php for ( $i = 54000; $i <= 61200; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                    <button class="bookly-day bookly-js-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+3 days', current_time( 'timestamp' ) ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 46800; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="bookly-column col5" style="display:<?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : ' inline-block' ?>">
                    <?php for ( $i = 50400; $i <= 61200; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                    <button class="bookly-day bookly-js-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+4 days', current_time( 'timestamp' ) ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 43200; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="bookly-column col6" style="display: <?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : 'inline-block' ?>">
                    <?php for ( $i = 46800; $i <= 61200; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                    <button class="bookly-day bookly-js-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+5 days', current_time( 'timestamp' ) ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 39600; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
                <div class="bookly-column col7" style="display:<?php echo get_option( 'bookly_app_show_calendar' ) == 1 ? ' none' : ' inline-block' ?>">
                    <?php for ( $i = 43200; $i <= 61200; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                    <button class="bookly-day bookly-js-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+6 days', current_time( 'timestamp' ) ) ) ?></button>
                    <?php for ( $i = 28800; $i <= 36000; $i += 3600 ) : ?>
                        <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $i ) ?>
                            </span>
                        </button>
                    <?php endfor ?>
                </div>
            </div>

            <div id="bookly-day-one-column" class="bookly-time-screen" style="display: <?php echo get_option( 'bookly_app_show_day_one_column' ) == 1 ? ' block' : 'none' ?>">
                <div class="bookly-input-wrap bookly-slot-calendar bookly-js-slot-calendar">
                    <span class="bookly-date-wrap">
                        <input style="display: none" class="bookly-js-selected-date bookly-form-element" type="text" data-value="<?php echo date( 'Y-m-d' ) ?>" />
                    </span>
                </div>
                <?php for ( $i = 1; $i <= 7; ++ $i ) : ?>
                    <div class="bookly-column col<?php echo $i ?>">
                        <button class="bookly-day bookly-js-first-child"><?php echo date_i18n( 'D, M d', strtotime( '+' . ( $i - 1 ) . ' days', current_time( 'timestamp' ) ) ) ?></button>
                        <?php for ( $j = 28800; $j <= 61200; $j += 3600 ) : ?>
                            <button class="bookly-hour ladda-button<?php if ( mt_rand( 0, 1 ) ) echo get_option( 'bookly_app_show_blocked_timeslots' ) == 1 ? ' booked' : ' no-booked' ?>">
                            <span class="ladda-label">
                                <i class="bookly-hour-icon"><span></span></i><?php echo \BooklyLite\Lib\Utils\DateTime::formatTime( $j ) ?>
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
        <button class="bookly-time-next bookly-btn bookly-right ladda-button">
            <span class="bookly-label">&gt;</span>
        </button>
        <button class="bookly-time-prev bookly-btn bookly-right ladda-button">
            <span class="bookly-label">&lt;</span>
        </button>
        <div class="bookly-back-step bookly-js-back-step bookly-btn">
            <?php $editable::renderString( array( 'bookly_l10n_button_back' ) ) ?>
        </div>
        <button class="bookly-go-to-cart bookly-js-go-to-cart bookly-round bookly-round-md ladda-button" data-style="zoom-in" data-spinner-size="30"><span class="ladda-label"><img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/frontend/resources/images/cart.png' ) ?>" /></span></button>
    </div>
</div>
