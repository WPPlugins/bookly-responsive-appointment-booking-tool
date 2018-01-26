<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    echo $progress_tracker;
?>
<div class="bookly-box">
    <div><?php echo $info_text ?></div>
    <div class="bookly-holder bookly-label-error bookly-bold"></div>
</div>
<?php if ( \BooklyLite\Lib\Config::showCalendar() ) : ?>
    <style type="text/css">
        .picker__holder{top: 0;left: 0;}
        .bookly-time-step {margin-left: 0;margin-right: 0;}
    </style>
    <div class="bookly-input-wrap bookly-slot-calendar bookly-js-slot-calendar">
         <input style="display: none" class="bookly-js-selected-date" type="text" value="" data-value="<?php echo esc_attr( $date ) ?>" />
    </div>
<?php endif ?>
<?php if ( $has_slots ) : ?>
    <div class="bookly-time-step">
        <div class="bookly-columnizer-wrap">
            <div class="bookly-columnizer">
                <?php /* here _time_slots */ ?>
            </div>
        </div>
    </div>
    <div class="bookly-box bookly-nav-steps bookly-clear">
        <button class="bookly-time-next bookly-btn bookly-right ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label">&gt;</span>
        </button>
        <button class="bookly-time-prev bookly-btn bookly-right ladda-button" data-style="zoom-in" style="display: none" data-spinner-size="40">
            <span class="ladda-label">&lt;</span>
        </button>
<?php else : ?>
    <div class="bookly-not-time-screen<?php if ( ! \BooklyLite\Lib\Config::showCalendar() ) : ?> bookly-not-calendar<?php endif ?>">
        <?php _e( 'No time is available for selected criteria.', 'bookly' ) ?>
    </div>
    <div class="bookly-box bookly-nav-steps">
<?php endif ?>
        <button class="bookly-back-step bookly-js-back-step bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_button_back' ) ?></span>
        </button>
        <?php if ( $show_cart_btn ) : ?>
            <button class="bookly-go-to-cart bookly-js-go-to-cart bookly-round bookly-round-md ladda-button" data-style="zoom-in" data-spinner-size="30">
                <span class="ladda-label"><img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/frontend/resources/images/cart.png' ) ?>" /></span>
            </button>
        <?php endif ?>
    </div>
