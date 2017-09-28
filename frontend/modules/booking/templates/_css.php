<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $color = get_option( 'bookly_app_color', '#f4662f' );
    $checkbox_img = plugins_url( 'frontend/resources/images/checkbox.png', \BooklyLite\Lib\Plugin::getMainFile() );
?>
<style type="text/css">
    /* Color */
    .ab-formGroup > label,
    .ab-label-error,
    .ab-progress-tracker > .active,
    .bookly-form .picker__nav--next,
    .bookly-form .pickadate__nav--prev,
    .bookly-form .picker__day:hover,
    .bookly-form .picker__day--selected:hover,
    .bookly-form .picker--opened .picker__day--selected,
    .bookly-form .picker__button--clear,
    .bookly-form .picker__button--today {
        color: <?php echo $color ?>!important;
    }
    /* Background */
    .bookly-back-step,
    .bookly-next-step,
    .ab-mobile-next-step,
    .ab-mobile-prev-step,
    .ab-progress-tracker > .active .step,
    .bookly-form .picker__frame,
    .bookly-service-step .bookly-week-days label,
    .bookly-repeat-step .bookly-week-days label,
    .ab-add-item,
    .bookly-get-schedule,
    .btn-apply-coupon,
    .ab-columnizer .ab-hour:hover .ab-hour-icon span,
    .ab-time-next,
    .ab-time-prev,
    .bookly-btn-submit,
    .bookly-round,
    .bookly-square {
        background-color: <?php echo $color ?>!important;
    }
    .bookly-triangle {
        border-bottom-color: <?php echo $color ?>!important;
    }
    /* Border */
    .bookly-form input[type="text"].ab-error,
    .bookly-form select.ab-error,
    .bookly-form textarea.ab-error,
    .bookly-extra-step div.bookly-extras-thumb.bookly-extras-selected {
        border: 2px solid <?php echo $color ?>!important;
    }
    /* Other */
    .bookly-form .picker__header { border-bottom: 1px solid <?php echo $color ?>!important; }
    .bookly-form .picker__nav--next:before { border-left:  6px solid <?php echo $color ?>!important; }
    .bookly-form .picker__nav--prev:before { border-right: 6px solid <?php echo $color ?>!important; }
    .bookly-service-step .bookly-week-days label.active, .bookly-repeat-step .bookly-week-days label.active { background: <?php echo $color ?> url(<?php echo $checkbox_img ?>) 0 0 no-repeat!important; }
    .bookly-pagination > li.active, .ab-columnizer .ab-day, .bookly-schedule-date { background: <?php echo $color ?>!important; border: 1px solid <?php echo $color ?>!important; }
    .ab-columnizer .ab-hour:hover { border: 2px solid <?php echo $color ?>!important; color: <?php echo $color ?>!important; }
    .ab-columnizer .ab-hour:hover .ab-hour-icon { background: none; border: 2px solid <?php echo $color ?>!important; color: <?php echo $color ?>!important; }
</style>