<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_Updates {

    function update_3_4() {
        /* @var WPDB $wpdb */
        global $wpdb;

        $wpdb->query("ALTER TABLE `ab_payment` DROP `status`;");
    }

    function update_3_2(){
        /* @var WPDB $wpdb */
        global $wpdb;

        // Google Calendar oAuth.
        $wpdb->query("ALTER TABLE `ab_staff` DROP `google_user`, DROP `google_PASS`;");
        $wpdb->query("ALTER TABLE `ab_staff` ADD `google_data` VARCHAR(255) DEFAULT NULL, ADD `google_calendar_id` VARCHAR(255) DEFAULT NULL;");
        $wpdb->query("ALTER TABLE `ab_appointment` ADD `google_event_id` VARCHAR(255) DEFAULT NULL;");

        // Coupons
        $wpdb->query("
            CREATE TABLE IF NOT EXISTS ab_coupons (
                id        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                code      VARCHAR ( 255 ) NOT NULL DEFAULT '',
                discount  DECIMAL( 3, 0 ) NOT NULL DEFAULT  '0',
                used      TINYINT ( 1 ) NOT NULL DEFAULT '0'
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci;");

        $wpdb->query("ALTER TABLE `ab_payment` ADD `coupon` VARCHAR(255) DEFAULT NULL;");

        add_option('ab_appearance_text_label_coupon', __( 'Coupon', 'ab' ), '', 'yes');
        add_option('ab_appearance_text_info_coupon', __( 'The price for the service is [[SERVICE_PRICE]].', 'ab' ), '', 'yes');
        add_option('ab_settings_coupons', '0', '', 'yes');
        add_option('ab_settings_google_client_id', '', '', 'yes');
        add_option('ab_settings_google_client_secret', '', '', 'yes');
    }
    
    function update_3_0(){
        /* @var WPDB $wpdb */
        global $wpdb;

        // Create new table with foreign keys
        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_customer_appointment (
                id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                customer_id     INT UNSIGNED NOT NULL,
                appointment_id  INT UNSIGNED NOT NULL,
                notes TEXT,
                token VARCHAR(255) DEFAULT NULL,
                INDEX ab_customer_appointment_customer_id_idx (customer_id),
                INDEX ab_customer_appointment_appointment_id_idx (appointment_id),
                CONSTRAINT fk_ab_customer_appointment_customer_id
                  FOREIGN KEY ab_customer_appointment_customer_id_idx (customer_id)
                  REFERENCES  ab_customer(id)
                  ON DELETE   CASCADE
                  ON UPDATE   CASCADE,
                CONSTRAINT fk_ab_customer_appointment_appointment_id
                  FOREIGN KEY ab_customer_appointment_appointment_id_idx (appointment_id)
                  REFERENCES  ab_appointment(id)
                  ON DELETE   CASCADE
                  ON UPDATE   CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci"
        );

        // Create relation between customer and appointment
        $appointments = $wpdb->get_results('SELECT * from `ab_appointment` ');
        foreach ($appointments as $appointment){
            $wpdb->insert('ab_customer_appointment', array(
                'customer_id'   => $appointment->customer_id,
                'appointment_id'=> $appointment->id,
                'notes'         => $appointment->notes,
                'token'         => $appointment->token
            ));
        }

        // Refactor binding from customer to appointment (many - many)
        $wpdb->query("ALTER TABLE ab_appointment DROP FOREIGN KEY fk_ab_appointment_customer_id;");
        $wpdb->query("ALTER TABLE ab_appointment DROP customer_id, DROP notes, DROP token;");

        // Add Service and Staff capacity
        $wpdb->query("ALTER TABLE ab_service ADD capacity INT NOT NULL DEFAULT '1';");
        $wpdb->query("ALTER TABLE ab_staff_service ADD capacity INT NOT NULL DEFAULT '1';");

        // Delete table ab_payment_appointment
        $wpdb->query("ALTER TABLE ab_payment ADD appointment_id INT UNSIGNED DEFAULT NULL;");

        $payments_appointment = $wpdb->get_results('SELECT * from ab_payment_appointment ');
        foreach ($payments_appointment as $payment_appointment) {
            $wpdb->update('ab_payment', array('appointment_id' => $payment_appointment->appointment_id), array('id' => $payment_appointment->payment_id));
        }

        $wpdb->query('DROP TABLE ab_payment_appointment');

        $wpdb->query('
            ALTER TABLE `ab_payment`
            ADD INDEX ab_payment_appointment_id_idx ( `appointment_id` ),
            ADD CONSTRAINT fk_ab_payment_appointment_id
            FOREIGN KEY ab_payment_appointment_id_idx (appointment_id)
            REFERENCES  ab_appointment(id)
            ON DELETE   SET NULL
            ON UPDATE   CASCADE;');

        $wpdb->query( 'ALTER TABLE ab_staff_schedule_item DROP FOREIGN KEY fk_ab_staff_schedule_item_schedule_item_id' );
        $wpdb->query( 'ALTER TABLE ab_staff_schedule_item DROP INDEX ab_staff_schedule_item_unique_ids_idx' );
        $wpdb->query( 'ALTER TABLE ab_staff_schedule_item DROP INDEX ab_staff_schedule_item_schedule_item_id_idx' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_schedule_item' );

        $wpdb->query( 'ALTER TABLE ab_staff_schedule_item CHANGE COLUMN schedule_item_id day_index int(10) UNSIGNED NOT NULL AFTER staff_id');
        $wpdb->query( 'ALTER TABLE ab_staff_schedule_item ADD UNIQUE KEY ab_staff_schedule_item_unique_ids_idx (staff_id, day_index) ' );
    }

    function update_2_2_2() {
        // No updates in this version.
    }

    function update_2_2_1() {
        // No updates in this version.
    }

    function update_2_2_0(){
        /* @var WPDB $wpdb */
        global $wpdb;

        // stripe.com
        $wpdb->query( "ALTER TABLE ab_payment CHANGE `type` `type` ENUM('local', 'paypal', 'authorizeNet', 'stripe') NOT NULL DEFAULT 'local'" );
        add_option( 'ab_stripe', '0', '', 'yes' );
        add_option( 'ab_stripe_secret_key', '', '', 'yes' );

        // Remove old options.
        delete_option( 'ab_appearance_progress_tracker_type' );
    }

    function update_2_1_0() {
        /* @var WPDB $wpdb */
        global $wpdb;

        add_option( 'ab_installation_time', time() );

        // Rename some old options.
        add_option( 'ab_settings_pay_locally', get_option( 'ab_local_mode' ) );
        delete_option( 'ab_local_mode' );

        // Add Authorize.net option
        $wpdb->query( "ALTER TABLE ab_payment CHANGE `type` `type` ENUM('local', 'paypal', 'authorizeNet') NOT NULL DEFAULT 'local'" );
        add_option( 'ab_authorizenet_api_login_id',   '', '', 'yes' );
        add_option( 'ab_authorizenet_transaction_key',   '', '', 'yes' );
        add_option( 'ab_authorizenet_sandbox',  0, '', 'yes' );
        add_option( 'ab_authorizenet_type',  'disabled', '', 'yes' );
    }

    function update_2_0_1() {
        global $wpdb;

        // In previous migration there was a problem with adding these 2 fields. The problem has been resolved,
        // but we need to take care of users who have already run the previous migration script.
        $wpdb->query( 'ALTER TABLE `ab_staff` ADD `google_user` VARCHAR(255) DEFAULT NULL ;' );
        $wpdb->query( 'ALTER TABLE `ab_staff` ADD `google_pass` VARCHAR(255) DEFAULT NULL ;' );

        delete_option( 'ab_fixtures' );
        delete_option( 'ab_send_notifications_cron_sh_path' );
    }

    function update_2_0() {
        global $wpdb;

        add_option( 'ab_settings_time_slot_length', '15', '', 'yes' );
        add_option( 'ab_settings_no_current_day_appointments', '0', '', 'yes' );
        add_option( 'ab_settings_use_client_time_zone', '0', '', 'yes' );
        add_option( 'ab_settings_cancel_page_url', home_url(), '', 'yes' );

        // Add new appearance text options.
        add_option( 'ab_appearance_text_step_service', __( "Service", "ab" ), '', 'yes' );
        add_option( 'ab_appearance_text_step_time', __( "Time", "ab" ), '', 'yes' );
        add_option( 'ab_appearance_text_step_details', __( "Details", "ab" ), '', 'yes' );
        add_option( 'ab_appearance_text_step_payment', __( "Payment", "ab" ), '', 'yes' );
        add_option( 'ab_appearance_text_step_done', __( "Done", "ab" ), '', 'yes' );
        add_option( 'ab_appearance_text_label_category', __( "Category", "ab" ), '', 'yes' );
        add_option( 'ab_appearance_text_label_service', __( "Service", "ab" ), '', 'yes' );
        add_option( 'ab_appearance_text_label_employee', __( "Employee", "ab" ), '', 'yes' );
        add_option( 'ab_appearance_text_label_select_date', __( 'I\'m available on or after', 'ab' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_start_from', __( 'Start from', 'ab' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_finish_by', __( 'Finish by', 'ab' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_name', __( 'Name', 'ab' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_phone', __( 'Phone', 'ab' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_email', __( 'Email', 'ab' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_notes', __( 'Notes (optional)', 'ab' ), '', 'yes' );
        add_option( 'ab_appearance_text_option_service', __( 'Select service', 'ab' ), '', 'yes' );
        add_option( 'ab_appearance_text_option_category', __( 'Select category', 'ab' ), '', 'yes' );
        add_option( 'ab_appearance_text_option_employee', __( 'Any', 'ab' ), '', 'yes' );

        // Rename some old options.
        add_option( 'ab_appearance_color', get_option( 'ab_appearance_booking_form_color' ) );
        delete_option( 'ab_appearance_booking_form_color' );
        add_option( 'ab_appearance_text_info_first_step',  strip_tags( get_option( 'ab_appearance_first_step_booking_info' ) ) );
        delete_option( 'ab_appearance_first_step_booking_info' );
        add_option( 'ab_appearance_text_info_second_step', strip_tags( get_option( 'ab_appearance_second_step_booking_info' ) ) );
        delete_option( 'ab_appearance_second_step_booking_info' );
        add_option( 'ab_appearance_text_info_third_step',  strip_tags( get_option( 'ab_appearance_third_step_booking_info' ) ) );
        delete_option( 'ab_appearance_third_step_booking_info' );
        add_option( 'ab_appearance_text_info_fourth_step', strip_tags( get_option( 'ab_appearance_fourth_step_booking_info' ) ) );
        delete_option( 'ab_appearance_fourth_step_booking_info' );
        add_option( 'ab_appearance_text_info_fifth_step',  strip_tags( get_option( 'ab_appearance_fifth_step_booking_info' ) ) );
        delete_option( 'ab_appearance_fifth_step_booking_info' );

        $wpdb->query( 'ALTER TABLE `ab_staff` ADD `google_user` VARCHAR(255) DEFAULT NULL ;' );
        $wpdb->query( 'ALTER TABLE `ab_staff` ADD `google_pass` VARCHAR(255) DEFAULT NULL ;' );

        $wpdb->query( 'ALTER TABLE `ab_customer` ADD `notes` TEXT NOT NULL ;' );
        $wpdb->query( 'ALTER TABLE `ab_appointment` ADD `token` varchar(255) DEFAULT NULL ;' );
        $wpdb->query( 'ALTER TABLE `ab_notifications` DROP `name`;' );
    }
}

function ab_plugin_get_version() {
    if ( ! function_exists( 'get_plugin_data' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_data = get_plugin_data( __DIR__ . DIRECTORY_SEPARATOR . 'main.php' );

    return $plugin_data['Version'];
}

function ab_plugin_update_db() {

    $db_version     = get_option( 'ab_db_version' );
    $plugin_version = ab_plugin_get_version();
    $update_class   = new AB_Updates();

    if ( $plugin_version > $db_version ) {

        $db_version_underscored = 'update_' . str_replace( '.', '_', $db_version );
        $plugin_version_underscored = 'update_' . str_replace( '.', '_', $plugin_version );

        // sort the update methods ascending
        $updates = array_filter(
            get_class_methods( $update_class ),
            function( $method ) { return strstr( $method, 'update_' ); }
        );
        usort( $updates, 'strnatcmp' );

        foreach ($updates as $method) {
            if ( $method > $db_version_underscored && $method <= $plugin_version_underscored ) {
                call_user_func( array( $update_class, $method ) );
            }
        }

        update_option( 'ab_db_version', $plugin_version );
    }
}