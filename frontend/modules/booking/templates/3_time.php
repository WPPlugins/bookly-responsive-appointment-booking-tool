<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    echo $progress_tracker;
?>
<div class="ab-row">
    <div><?php echo $info_text ?></div>
    <div class="ab--holder ab-label-error ab-bold"></div>
</div>
<?php if ( \BooklyLite\Lib\Config::showCalendar() ) : ?>
    <style type="text/css">
        .picker__holder{top: 0;left: 0;}
        .ab-time-step {margin-left: 0;margin-right: 0;}
    </style>
    <div class="ab-input-wrap ab-slot-calendar">
         <input style="display: none" class="ab-selected-date" type="text" value="" data-value="<?php echo esc_attr( $date ) ?>" />
    </div>
<?php endif ?>
<?php if ( $has_slots ) : ?>
    <div class="ab-time-step">
        <div class="ab-columnizer-wrap">
            <div class="ab-columnizer">
                <?php /* here _time_slots */ ?>
            </div>
        </div>
    </div>
    <div class="ab-row ab-nav-steps ab-clear">
        <button class="ab-time-next ab-btn ab-right ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label">&gt;</span>
        </button>
        <button class="ab-time-prev ab-btn ab-right ladda-button" data-style="zoom-in" style="display: none" data-spinner-size="40">
            <span class="ladda-label">&lt;</span>
        </button>
<?php else : ?>
    <div class="ab-not-time-screen<?php if ( ! \BooklyLite\Lib\Config::showCalendar() ) : ?> ab-not-calendar<?php endif ?>">
        <?php _e( 'No time is available for selected criteria.', 'bookly' ) ?>
    </div>
    <div class="ab-row ab-nav-steps">
<?php endif ?>
        <button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ) ?></span>
        </button>
        <?php if ( $show_cart_btn ) : ?>
            <button class="ab-left ab-goto-cart bookly-round-button ladda-button" data-style="zoom-in" data-spinner-size="30">
                <span class="ladda-label"><img src="<?php echo plugins_url( 'appointment-booking/frontend/resources/images/cart.png' ) ?>" /></span>
            </button>
        <?php endif ?>
    </div>
