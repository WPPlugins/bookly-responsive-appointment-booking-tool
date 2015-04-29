<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_Installer {
    private $notifications;
    private $options;

    /**
     * Constructor.
     */
    public function __construct() {
        // Load l10n for fixtures creating.
        load_plugin_textdomain( 'ab', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        /*
         * Notifications.
         */
        $this->notifications = array(
            'client_info' => array(
                'subject' => __( 'Your appointment information', 'ab' ),
                'message' => wpautop( __("Dear [[CLIENT_NAME]].\n\nThis is confirmation that you have booked [[SERVICE_NAME]].\n\nWe are waiting you at [[COMPANY_ADDRESS]] on [[APPOINTMENT_DATE]] at [[APPOINTMENT_TIME]].\n\nThank you for choosing our company.\n\n[[COMPANY_NAME]]\n[[COMPANY_PHONE]]\n[[COMPANY_WEBSITE]]", 'ab' ) ),
                'active'  => 1
            ),
            'provider_info' => array(
                'subject' => __( 'New booking information', 'ab' ),
                'message' => wpautop( __( "Hello.\n\nYou have new booking.\n\nService: [[SERVICE_NAME]]\nDate: [[APPOINTMENT_DATE]]\nTime: [[APPOINTMENT_TIME]]\nClient name: [[CLIENT_NAME]]\nClient phone: [[CLIENT_PHONE]]\nClient email: [[CLIENT_EMAIL]]\nClient notes: [[CLIENT_NOTES]]", 'ab' ) ),
                'active'  => 0
            ),
            'evening_next_day' => array(
                'subject' => __( 'Your appointment at [[COMPANY_NAME]]', 'ab' ),
                'message' => wpautop( __( "Dear [[CLIENT_NAME]].\n\nWe would like to remind you that you have booked [[SERVICE_NAME]] tomorrow on [[APPOINTMENT_TIME]]. We are waiting you at [[COMPANY_ADDRESS]].\n\nThank you for choosing our company.\n\n[[COMPANY_NAME]]\n[[COMPANY_PHONE]]\n[[COMPANY_WEBSITE]]", 'ab' ) ),
                'active'  => 0
            ),
            'evening_after' => array(
                'subject' => __( 'Your visit to [[COMPANY_NAME]]', 'ab' ),
                'message' => wpautop( __( "Dear [[CLIENT_NAME]].\n\nThank you for choosing [[COMPANY_NAME]]. We hope you were satisfied with your [[SERVICE_NAME]].\n\nThank you and we look forward to seeing you again soon.\n\n[[COMPANY_NAME]]\n[[COMPANY_PHONE]]\n[[COMPANY_WEBSITE]]", 'ab' ) ),
                'active'  => 0
            ),
            'event_next_day' => array(
                'subject' => __( 'Your agenda for [[TOMORROW_DATE]]', 'ab' ),
                'message' => wpautop( __( "Hello.\n\nYour agenda for tomorrow is:\n\n[[NEXT_DAY_AGENDA]]", 'ab' ) ),
                'active'  => 0
            )
        );
        /**
         * Options.
         */
        $this->options = array(
            'ab_data_loaded'                          => '0',
            // DB version.
            'ab_db_version'                           => ab_plugin_get_version(),
            // Timestamp when the plugin was installed.
            'ab_installation_time'                    => time(),
            // Settings.
            'ab_settings_company_name'                => '',
            'ab_settings_company_logo'                => '',
            'ab_settings_company_logo_path'           => '',
            'ab_settings_company_logo_url'            => '',
            'ab_settings_company_address'             => '',
            'ab_settings_company_phone'               => '',
            'ab_settings_company_website'             => '',
            'ab_settings_pay_locally'                 => '1',
            'ab_settings_sender_name'                 => get_option( 'blogname' ),
            'ab_settings_sender_email'                => get_option( 'admin_email' ),
            'ab_settings_time_slot_length'            => '15',
            'ab_settings_no_current_day_appointments' => '0',
            'ab_settings_use_client_time_zone'        => '0',
            'ab_settings_coupons'                     => '0',
            'ab_settings_google_client_id'            => '',
            'ab_settings_google_client_secret'        => '',
            // Business hours.
            'ab_settings_monday_start'                => '08:00',
            'ab_settings_monday_end'                  => '18:00',
            'ab_settings_tuesday_start'               => '08:00',
            'ab_settings_tuesday_end'                 => '18:00',
            'ab_settings_wednesday_start'             => '08:00',
            'ab_settings_wednesday_end'               => '18:00',
            'ab_settings_thursday_start'              => '08:00',
            'ab_settings_thursday_end'                => '18:00',
            'ab_settings_friday_start'                => '08:00',
            'ab_settings_friday_end'                  => '18:00',
            'ab_settings_saturday_start'              => '',
            'ab_settings_saturday_end'                => '',
            'ab_settings_sunday_start'                => '',
            'ab_settings_sunday_end'                  => '',
            // Cancel appointment page url.
            'ab_settings_cancel_page_url'             => home_url(),
            // Appearance.
            'ab_appearance_text_info_first_step'      => __( 'Please select service: ', 'ab' ),
            'ab_appearance_text_info_second_step'     => __( "Below you can find a list of available time slots for [[SERVICE_NAME]] by [[STAFF_NAME]].\nClick on a time slot to proceed with booking.", 'ab' ),
            'ab_appearance_text_info_third_step'      => __( "You selected a booking for [[SERVICE_NAME]] by [[STAFF_NAME]] at [[SERVICE_TIME]] on [[SERVICE_DATE]]. The price for the service is [[SERVICE_PRICE]].\nPlease provide your details in the form below to proceed with booking.", 'ab' ),
            'ab_appearance_text_info_fourth_step'     => __( 'Please tell us how you would like to pay: ', 'ab' ),
            'ab_appearance_text_info_fifth_step'      => __( 'Thank you! Your booking is complete. An email with details of your booking has been sent to you.', 'ab' ),
            'ab_appearance_text_info_coupon'          => __( 'The price for the service is [[SERVICE_PRICE]].', 'ab' ),
            'ab_appearance_color'                     => '#f4662f',  // booking form color
            'ab_appearance_text_step_service'         => __( "Service", "ab" ),
            'ab_appearance_text_step_time'            => __( "Time", "ab" ),
            'ab_appearance_text_step_details'         => __( "Details", "ab" ),
            'ab_appearance_text_step_payment'         => __( "Payment", "ab" ),
            'ab_appearance_text_step_done'            => __( "Done", "ab" ),
            'ab_appearance_text_label_category'       => __( "Category", "ab" ),
            'ab_appearance_text_label_service'        => __( "Service", "ab" ),
            'ab_appearance_text_label_employee'       => __( "Employee", "ab" ),
            'ab_appearance_text_label_select_date'    => __( 'I\'m available on or after', 'ab' ),
            'ab_appearance_text_label_start_from'     => __( 'Start from', 'ab' ),
            'ab_appearance_text_label_finish_by'      => __( 'Finish by', 'ab' ),
            'ab_appearance_text_label_name'           => __( 'Name', 'ab' ),
            'ab_appearance_text_label_phone'          => __( 'Phone', 'ab' ),
            'ab_appearance_text_label_email'          => __( 'Email', 'ab' ),
            'ab_appearance_text_label_notes'          => __( 'Notes (optional)', 'ab' ),
            'ab_appearance_text_label_coupon'         => __( 'Coupon', 'ab' ),
            'ab_appearance_text_option_service'       => __( 'Select service', 'ab' ),
            'ab_appearance_text_option_category'      => __( 'Select category', 'ab' ),
            'ab_appearance_text_option_employee'      => __( 'Any', 'ab' ),
            // Progress tracker.
            'ab_appearance_show_progress_tracker'     => '1',
            // Envato Marketplace Purchase Code.
            'ab_envato_purchase_code'                 => '',
            // PayPal.
            'ab_paypal_api_username'                  => '',
            'ab_paypal_api_password'                  => '',
            'ab_paypal_api_signature'                 => '',
            'ab_paypal_ec_mode'                       => '',  // ".sandbox" or ""
            'ab_paypal_type'                          => 'disabled',
            'ab_paypal_id'                            => '',
            'ab_paypal_currency'                      => 'USD',
            // Authorize.net
            'ab_authorizenet_api_login_id'            => '',
            'ab_authorizenet_transaction_key'         => '',
            'ab_authorizenet_sandbox'                 => 0,
            'ab_authorizenet_type'                    => 'disabled',
            // Stripe
            'ab_stripe'                               => '0',
            'ab_stripe_secret_key'                    => ''
        );
    }

    /**
     * Install.
     */
    public function install() {
        // Create tables and load data if it hasn't been loaded yet.
        if ( ! get_option( 'ab_data_loaded' ) ) {
            $this->_create_tables();
            $this->_load_data();
        }
        update_option( 'ab_data_loaded', '1' );
    }

    /**
     * Uninstall.
     */
    public function uninstall() {
        $this->_remove_data();
        $this->_drop_tables();
    }

    /**
     * Load data.
     */
    private function _load_data() {
        /** @global wpdb $wpdb */
        global $wpdb;

        // Insert notifications.
        foreach ( $this->notifications as $slug => $data ) {
            $wpdb->insert( 'ab_notifications', array(
                'slug' => $slug,
                'subject' => $data[ 'subject' ],
                'message' => $data[ 'message' ],
                'active'  => $data[ 'active' ],
            ) );
        }

        // Add options.
        foreach ( $this->options as $name => $value ) {
            add_option( $name, $value, '', 'yes' );
        }

	    // Create unique staff member
        $wpdb->insert( 'ab_staff', array( 'full_name' => 'Enter name' ) );

        for ($i = 1; $i <= 7; $i++){
            $wpdb->insert( 'ab_staff_schedule_item',
                array(
                    'staff_id' => 1,
                    'day_index' => $i,
                    'start_time' => '08:00:00',
                    'end_time' => '18:00:00',
                )
            );
         }
    }

    /**
     * Remove data.
     */
    private function _remove_data() {

        // Remove options.
        foreach ( $this->options as $name => $value ) {
            delete_option( $name );
        }

        // Remove user meta.
        delete_metadata( 'user', null, 'ab_dismiss_admin_notice', '', true );
    }

    /**
     * Create tables in database.
     */
    private function _create_tables() {
        /** @global wpdb $wpdb */
        global $wpdb;

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_staff (
                id int unsigned NOT NULL auto_increment PRIMARY KEY,
                wp_user_id bigint(20) unsigned,
                avatar_url varchar(255) default '',
                avatar_path varchar(255) default '',
                full_name varchar(128) default '',
                email varchar(128) default '',
                phone varchar(128) default '',
                google_data varchar(255) default '',
                google_calendar_id varchar(255) default ''
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_category (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR( 255 ) NOT NULL
             ) ENGINE = INNODB
             DEFAULT CHARACTER SET = utf8
             COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_service (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR( 255 ) DEFAULT '',
                duration INT NOT NULL DEFAULT 900,
                price DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                color VARCHAR( 255 ) NOT NULL DEFAULT '#FFFFFF',
                category_id INT UNSIGNED ,
                capacity INT NOT NULL DEFAULT '1',
                INDEX ab_service_category_id_idx (category_id),
                CONSTRAINT fk_ab_service_category_id
                    FOREIGN KEY ab_service_category_id_idx (category_id)
                    REFERENCES ab_category(id)
                    ON DELETE SET NULL
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_staff_service (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                staff_id INT UNSIGNED NOT NULL,
                service_id INT UNSIGNED NOT NULL,
                price DECIMAL( 10, 2 ) NOT NULL DEFAULT '0.00',
                capacity INT NOT NULL DEFAULT '1',
                UNIQUE KEY ab_staff_service_unique_ids_idx (staff_id, service_id),
                INDEX ab_staff_service_staff_id_idx (staff_id),
                INDEX ab_staff_service_service_id_idx (service_id),
                CONSTRAINT fk_ab_staff_service_staff_id
                    FOREIGN KEY ab_staff_service_staff_id_idx (staff_id)
                    REFERENCES ab_staff(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT fk_ab_staff_service_service_id
                    FOREIGN KEY ab_staff_service_service_id_idx (service_id)
                    REFERENCES ab_service(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_staff_schedule_item (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                staff_id INT UNSIGNED NOT NULL,
                day_index INT UNSIGNED NOT NULL,
                start_time TIME,
                end_time TIME,
                UNIQUE KEY ab_staff_schedule_item_unique_ids_idx (staff_id, day_index),
                INDEX ab_staff_schedule_item_staff_id_idx (staff_id),
                CONSTRAINT fk_ab_staff_schedule_item_staff_id
                    FOREIGN KEY ab_staff_schedule_item_staff_id_idx (staff_id)
                    REFERENCES ab_staff(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
             ) ENGINE = INNODB
             DEFAULT CHARACTER SET = utf8
             COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_schedule_item_break (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                staff_schedule_item_id INT UNSIGNED NOT NULL,
                start_time TIME,
                end_time TIME,
                INDEX ab_schedule_item_break_staff_schedule_item_id_idx (staff_schedule_item_id),
                CONSTRAINT fk_ab_schedule_item_break_staff_schedule_item_id
                    FOREIGN KEY ab_schedule_item_break_staff_schedule_item_id_idx (staff_schedule_item_id)
                    REFERENCES ab_staff_schedule_item(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
             ) ENGINE = INNODB
             DEFAULT CHARACTER SET = utf8
             COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_notifications (
                id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                slug        VARCHAR ( 255 ) NOT NULL DEFAULT '',
                active      TINYINT ( 1 ) NOT NULL DEFAULT '0',
                copy        TINYINT ( 1 ) NOT NULL DEFAULT '0',
                subject     VARCHAR ( 255 ) NOT NULL DEFAULT '',
                message     TEXT
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_customer (
                id      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name    VARCHAR ( 255 ) NOT NULL DEFAULT '',
                phone   VARCHAR ( 255 ) NOT NULL DEFAULT '',
                email   VARCHAR ( 255 ) NOT NULL DEFAULT '',
                notes   TEXT NOT NULL DEFAULT ''
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS  ab_email_notification (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                customer_id INT UNSIGNED,
                staff_id INT UNSIGNED,
                type VARCHAR( 60 ) NOT NULL,
                created DATETIME NOT NULL,
                INDEX ab_email_notification_customer_id_idx (customer_id),
                INDEX ab_email_notification_staff_id_idx (staff_id),
                CONSTRAINT      fk_ab_email_notification_customer_id
                    FOREIGN KEY ab_email_notification_customer_id_idx (customer_id)
                    REFERENCES  ab_customer(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT      fk_ab_email_notification_staff_id
                    FOREIGN KEY ab_email_notification_staff_id_idx (staff_id)
                    REFERENCES  ab_staff(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_appointment (
                id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                staff_id        INT UNSIGNED NOT NULL,
                service_id      INT UNSIGNED,
                start_date      DATETIME NOT NULL,
                end_date        DATETIME NOT NULL,
                google_event_id VARCHAR(255) DEFAULT NULL,
                INDEX ab_appointment_staff_id_idx (staff_id),
                INDEX ab_appointment_service_id_idx (service_id),
               CONSTRAINT fk_ab_appointment_staff_id
                   FOREIGN KEY ab_appointment_staff_id_idx (staff_id)
                   REFERENCES ab_staff(id)
                   ON DELETE CASCADE
                   ON UPDATE CASCADE,
               CONSTRAINT fk_ab_appointment_service_id
                   FOREIGN KEY ab_appointment_service_id_idx (service_id)
                   REFERENCES ab_service(id)
                   ON DELETE CASCADE
                   ON UPDATE CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_payment (
                id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                created         DATETIME NOT NULL,
                type            ENUM('local', 'paypal', 'authorizeNet', 'stripe') NOT NULL DEFAULT 'local',
                customer_id     INT UNSIGNED NOT NULL,
                appointment_id  INT UNSIGNED DEFAULT NULL,
                token           VARCHAR(255) NOT NULL,
                transaction     VARCHAR(255) NOT NULL,
                coupon          VARCHAR(255) DEFAULT NULL,
                total           DECIMAL(10, 2) NOT NULL DEFAULT  '0.00',
                INDEX           ab_payment_customer_id_idx (customer_id),
                INDEX           ab_payment_appointment_id_idx (appointment_id),
                CONSTRAINT      fk_ab_payment_customer_id
                    FOREIGN KEY ab_payment_customer_id_idx (customer_id)
                    REFERENCES  ab_customer(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT      fk_ab_payment_appointment_id
                    FOREIGN KEY ab_payment_appointment_id_idx (appointment_id)
                    REFERENCES  ab_appointment(id)
                    ON DELETE   SET NULL
                    ON UPDATE   CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_holiday (
                  id        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  staff_id  INT UNSIGNED NULL DEFAULT NULL,
                  parent_id  INT UNSIGNED NULL DEFAULT NULL,
                  holiday   DATETIME NOT NULL,
                  repeat_event TINYINT ( 1 ) NOT NULL DEFAULT '0',
                  title     VARCHAR(255) NOT NULL DEFAULT '',
                  CONSTRAINT fk_ab_holiday_staff_id FOREIGN KEY ab_holiday_staff_id_idx(staff_id) REFERENCES ab_staff(id) ON DELETE CASCADE
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_customer_appointment (
                id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                customer_id     INT UNSIGNED NOT NULL,
                appointment_id  INT UNSIGNED NOT NULL,
                notes           TEXT,
                token           VARCHAR(255) DEFAULT NULL,
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

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS ab_coupons (
                id        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                code      VARCHAR ( 255 ) NOT NULL DEFAULT '',
                discount  DECIMAL( 3, 0 ) NOT NULL DEFAULT  '0',
                used      TINYINT ( 1 ) NOT NULL DEFAULT '0'
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci"
        );
    }

    private function _drop_tables() {
        /** @var wpdb $wpdb */
        global $wpdb;

        $wpdb->query( 'ALTER TABLE ab_service DROP FOREIGN KEY fk_ab_service_category_id' );
        $wpdb->query( 'ALTER TABLE ab_staff_service DROP FOREIGN KEY fk_ab_staff_service_staff_id' );
        $wpdb->query( 'ALTER TABLE ab_staff_service DROP FOREIGN KEY fk_ab_staff_service_service_id' );
        $wpdb->query( 'ALTER TABLE ab_staff_schedule_item DROP FOREIGN KEY fk_ab_staff_schedule_item_staff_id' );
        $wpdb->query( 'ALTER TABLE ab_schedule_item_break DROP FOREIGN KEY fk_ab_schedule_item_break_staff_schedule_item_id' );
        $wpdb->query( 'ALTER TABLE ab_appointment DROP FOREIGN KEY fk_ab_appointment_staff_id' );
        $wpdb->query( 'ALTER TABLE ab_appointment DROP FOREIGN KEY fk_ab_appointment_service_id' );
        $wpdb->query( 'ALTER TABLE ab_payment DROP FOREIGN KEY fk_ab_payment_customer_id' );
        $wpdb->query( 'ALTER TABLE ab_payment DROP FOREIGN KEY fk_ab_payment_appointment_id' );
        $wpdb->query( 'ALTER TABLE ab_email_notification DROP FOREIGN KEY fk_ab_email_notification_customer_id' );
        $wpdb->query( 'ALTER TABLE ab_email_notification DROP FOREIGN KEY fk_ab_email_notification_staff_id' );
        $wpdb->query( 'ALTER TABLE ab_holiday DROP FOREIGN KEY fk_ab_holiday_staff_id' );
        $wpdb->query( 'ALTER TABLE ab_customer_appointment DROP FOREIGN KEY fk_ab_customer_appointment_customer_id' );
        $wpdb->query( 'ALTER TABLE ab_customer_appointment DROP FOREIGN KEY fk_ab_customer_appointment_appointment_id' );

        $wpdb->query( 'DROP TABLE IF EXISTS ab_category' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_service' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_staff' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_staff_service' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_staff_schedule_item' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_schedule_item_break' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_notifications' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_appointment' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_customer' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_customer_appointment' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_payment' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_holiday' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_email_notification' );
        $wpdb->query( 'DROP TABLE IF EXISTS ab_coupons' );
    }
}