<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $color = get_option( 'ab_appearance_color', '#f4662f' );
    $checkbox_img = plugins_url( 'frontend/resources/images/checkbox.png', AB_PATH . '/main.php' );
?>
<style type="text/css">
    /* Service */
    .ab-label-error, label.ab-category-title, li.ab-step-tabs.active a {color: <?php echo $color ?>!important;}
    .ab-next-step, .ab-mobile-next-step, .ab-mobile-prev-step, li.ab-step-tabs.active div, .picker__frame, .ab-first-step .ab-week-days li label {background: <?php echo $color ?>!important;}
    .picker__header {border-bottom: 1px solid <?php echo $color ?>!important;}
    .picker__nav--next:before {border-left:  6px solid <?php echo $color ?>!important;}
    .picker__nav--prev:before {border-right: 6px solid <?php echo $color ?>!important;}
    .picker__nav--next, .pickadate__nav--prev, .picker__day:hover, .picker__day--selected:hover, .picker--opened .picker__day--selected, .picker__button--clear, .picker__button--today {color: <?php echo $color ?>!important;}
    .ab-first-step .ab-week-days li label.active {background: <?php echo $color ?> url(<?php echo $checkbox_img ?>) 0 0 no-repeat!important;}
    /* Time */
    .ab-columnizer .ab-available-day { background: <?php echo $color ?>!important; border: 1px solid <?php echo $color ?>!important; }
    .ab-columnizer .ab-available-hour:hover { border: 2px solid <?php echo $color ?>!important; color: <?php echo $color ?>!important; }
    .ab-columnizer .ab-available-hour:hover .ab-hour-icon { background: none; border: 2px solid <?php echo $color ?>!important; color: <?php echo $color ?>!important; }
    .ab-columnizer .ab-available-hour:hover .ab-hour-icon span, .ab-time-next, .ab-time-prev, .ab-to-first-step, .bookly-btn-submit,
    /* Payment */
    .btn-apply-coupon, .ab-to-third-step, .ab-final-step,
    /* Details */
    a.ab-to-second-step, .ab-to-second-step, .ab-to-fourth-step, a.ab-to-fourth-step {background: <?php echo $color ?>!important;}
    label.ab-formLabel, div.ab-error {color: <?php echo $color ?>!important;}
    input.ab-details-error, textarea.ab-details-error, div.ab-error select {border: 2px solid <?php echo $color ?>!important;}
</style>