<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<tr><td><input value="[[APPOINTMENT_DATE]]" readonly="readonly" onclick="this.select()" /> - <?php _e('date of appointment', 'bookly') ?></td></tr>
<tr><td><input value="[[APPOINTMENT_TIME]]" readonly="readonly" onclick="this.select()" /> - <?php _e('time of appointment', 'bookly') ?></td></tr>
<tr><td><input value="[[CANCEL_APPOINTMENT]]" readonly="readonly" onclick="this.select()" /> - <?php _e('cancel appointment link', 'bookly') ?></td></tr>
<tr><td><input value="[[CANCEL_APPOINTMENT_URL]]" readonly="readonly" onclick="this.select()" /> - <?php echo esc_html( __('URL for cancel appointment link (to use inside <a> tag)', 'bookly') ) ?></td></tr>
<tr><td><input value="[[CATEGORY_NAME]]" readonly="readonly" onclick="this.select()" /> - <?php _e('name of category', 'bookly') ?></td></tr>
<tr><td><input value="[[CLIENT_EMAIL]]" readonly="readonly" onclick="this.select()" /> - <?php _e('email of client', 'bookly') ?></td></tr>
<tr><td><input value="[[CLIENT_NAME]]" readonly="readonly" onclick="this.select()" /> - <?php _e('name of client', 'bookly') ?></td></tr>
<tr><td><input value="[[CLIENT_PHONE]]" readonly="readonly" onclick="this.select()" /> - <?php _e('phone of client', 'bookly') ?></td></tr>
<tr><td><input value="[[COMPANY_NAME]]" readonly="readonly" onclick="this.select()" /> - <?php _e('name of your company', 'bookly') ?></td></tr>
<tr><td><input value="[[COMPANY_LOGO]]" readonly="readonly" onclick="this.select()" /> - <?php _e('your company logo', 'bookly') ?></td></tr>
<tr><td><input value="[[COMPANY_ADDRESS]]" readonly="readonly" onclick="this.select()" /> - <?php _e('address of your company', 'bookly') ?></td></tr>
<tr><td><input value="[[COMPANY_PHONE]]" readonly="readonly" onclick="this.select()" /> - <?php _e('your company phone', 'bookly') ?></td></tr>
<tr><td><input value="[[COMPANY_WEBSITE]]" readonly="readonly" onclick="this.select()" /> - <?php _e('this web-site address', 'bookly') ?></td></tr>
<tr><td><input value="[[CUSTOM_FIELDS]]" readonly="readonly" onclick="this.select()" /> - <?php _e('combined values of all custom fields', 'bookly') ?></td></tr>
<tr><td><input value="[[CUSTOM_FIELDS_2C]]" readonly="readonly" onclick="this.select()" /> - <?php _e('combined values of all custom fields (formatted in 2 columns)', 'bookly') ?></td></tr>
<tr><td><input value="[[NUMBER_OF_PERSONS]]" readonly="readonly" onclick="this.select()" /> - <?php _e('number of persons', 'bookly') ?></td></tr>
<tr><td><input value="[[SERVICE_NAME]]" readonly="readonly" onclick="this.select()" /> - <?php _e('name of service', 'bookly') ?></td></tr>
<tr><td><input value="[[SERVICE_PRICE]]" readonly="readonly" onclick="this.select()" /> - <?php _e('price of service', 'bookly') ?></td></tr>
<tr><td><input value="[[STAFF_EMAIL]]" readonly="readonly" onclick="this.select()" /> - <?php _e('email of staff', 'bookly') ?></td></tr>
<tr><td><input value="[[STAFF_NAME]]" readonly="readonly" onclick="this.select()" /> - <?php _e('name of staff', 'bookly') ?></td></tr>
<tr><td><input value="[[STAFF_PHONE]]" readonly="readonly" onclick="this.select()" /> - <?php _e('phone of staff', 'bookly') ?></td></tr>
<tr><td><input value="[[STAFF_PHOTO]]" readonly="readonly" onclick="this.select()" /> - <?php _e('photo of staff', 'bookly') ?></td></tr>
<tr><td><input value="[[TOTAL_PRICE]]" readonly="readonly" onclick="this.select()" /> - <?php _e('total price of booking (service price multiplied by the number of persons)', 'bookly') ?></td></tr>