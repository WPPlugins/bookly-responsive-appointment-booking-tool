<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    echo $progress_tracker;
?>
<div class="ab-teaser ab-row-fluid">
    <div class="ab-desc"><?php _e( $info_text, 'bookly' ) ?></div>
</div>
<?php if ( AB_Config::showCalendar() ): ?>
    <div style="clear: both"></div>
    <style>.picker__holder{top: 0;left: 0;}</style>
    <div class="ab-input-wrap ab-slot-calendar">
      <span class="ab-date-wrap">
         <input style="display: none" class="ab-selected-date ab-formElement" type="text" value="" data-value="<?php echo esc_attr( $date ) ?>" />
      </span>
    </div>
<?php endif ?>
<?php if ( $has_slots ): ?>
    <div class="ab-second-step">
        <div class="ab-columnizer-wrap">
            <div class="ab-columnizer">
                <?php /* here _time_slots */ ?>
            </div>
        </div>
    </div>
    <div class="ab-row-fluid ab-nav-steps ab-clear">
        <button class="ab-time-next ab-btn ab-right ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label">&gt;</span>
        </button>
        <button class="ab-time-prev ab-btn ab-right ladda-button" data-style="zoom-in" style="display: none" data-spinner-size="40">
            <span class="ladda-label">&lt;</span>
        </button>
        <button class="ab-left ab-to-first-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php _e( 'Back', 'bookly' ) ?></span>
        </button>
    </div>
<?php else: ?>
    <div class="ab-not-time-screen<?php if ( !AB_Config::showCalendar() ): ?> ab-not-calendar<?php endif ?>">
        <?php _e( 'No time is available for selected criteria.', 'bookly' ) ?>
    </div>
    <div class="ab-row-fluid ab-nav-steps ab-clear">
        <button class="ab-left ab-to-first-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><?php _e( 'Back', 'bookly' ) ?></span>
        </button>
    </div>
<?php endif ?>