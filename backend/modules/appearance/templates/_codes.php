<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<b>[[CATEGORY_NAME]]</b> - <?php _e( 'name of category', 'bookly' ) ?><br />
<?php if ( $step == 3 && $login ) : ?>
    <b>[[LOGIN_FORM]]</b> - <?php _e( 'login form', 'bookly' ) ?><br />
<?php endif ?>
<b>[[NUMBER_OF_PERSONS]]</b> - <?php _e( 'number of persons', 'bookly' ) ?><br />
<?php if ( $step > 2 ) : ?>
    <b>[[SERVICE_DATE]]</b> - <?php _e( 'date of service', 'bookly' ) ?><br />
<?php endif ?>
<b>[[SERVICE_NAME]]</b> - <?php _e( 'name of service', 'bookly' ) ?><br />
<b>[[SERVICE_PRICE]]</b> - <?php _e( 'price of service', 'bookly' ) ?><br />
<?php if ( $step > 2 ) : ?>
    <b>[[SERVICE_TIME]]</b> - <?php _e( 'time of service', 'bookly' ) ?><br />
<?php endif ?>
<b>[[STAFF_NAME]]</b> - <?php _e( 'name of staff', 'bookly' ) ?><br />
<b>[[TOTAL_PRICE]]</b> - <?php _e( 'total price of booking', 'bookly' ) ?>