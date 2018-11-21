<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $color = get_option( 'bookly_app_color', '#f4662f' );
    $checkbox_img = plugins_url( 'frontend/resources/images/checkbox.png', \Bookly\Lib\Plugin::getMainFile() );
    $custom_css = trim( get_option( 'bookly_app_custom_styles' ) );
?>
<style type="text/css">
    /* Color */
    .bookly-form-group > label,
    .bookly-label-error,
    .bookly-progress-tracker > .active,
    .bookly-form .picker__nav--next,
    .bookly-form .pickadate__nav--prev,
    .bookly-form .picker__day:hover,
    .bookly-form .picker__day--selected:hover,
    .bookly-form .picker--opened .picker__day--selected,
    .bookly-form .picker__button--clear,
    .bookly-form .picker__button--today,
    .bookly-columnizer .bookly-hour span.bookly-waiting-list {
        color: <?php echo $color ?>!important;
    }
    /* Background */
    .bookly-progress-tracker > .active .step,
    .bookly-form .picker__frame,
    .bookly-service-step .bookly-week-days label,
    .bookly-repeat-step .bookly-week-days label,
    .bookly-columnizer .bookly-hour:hover .bookly-hour-icon span,
    .bookly-btn,
    .bookly-btn:active,
    .bookly-btn:focus,
    .bookly-btn:hover,
    .bookly-btn-submit,
    .bookly-round,
    .bookly-square {
        background-color: <?php echo $color ?>!important;
    }
    .bookly-triangle {
        border-bottom-color: <?php echo $color ?>!important;
    }
    /* Border */
    .bookly-form input[type="text"].bookly-error,
    .bookly-form input[type="password"].bookly-error,
    .bookly-form select.bookly-error,
    .bookly-form textarea.bookly-error,
    .bookly-extra-step div.bookly-extras-thumb.bookly-extras-selected {
        border: 2px solid <?php echo $color ?>!important;
    }
    /* Other */
    .bookly-form .picker__header { border-bottom: 1px solid <?php echo $color ?>!important; }
    .bookly-form .picker__nav--next:before { border-left:  6px solid <?php echo $color ?>!important; }
    .bookly-form .picker__nav--prev:before { border-right: 6px solid <?php echo $color ?>!important; }
    .bookly-service-step .bookly-week-days label.active, .bookly-repeat-step .bookly-week-days label.active { background: <?php echo $color ?> url(<?php echo $checkbox_img ?>) 0 0 no-repeat!important; }
    .bookly-pagination > li.active, .bookly-columnizer .bookly-day, .bookly-schedule-date { background: <?php echo $color ?>!important; border: 1px solid <?php echo $color ?>!important; }
    .bookly-columnizer .bookly-hour:hover { border: 2px solid <?php echo $color ?>!important; color: <?php echo $color ?>!important; }
    .bookly-columnizer .bookly-hour:hover .bookly-hour-icon { background: none; border: 2px solid <?php echo $color ?>!important; color: <?php echo $color ?>!important; }
</style>

<?php if ( $custom_css != '' ) : ?>
    <style type="text/css">
        <?php echo $custom_css ?>
    </style>
<?php endif ?>