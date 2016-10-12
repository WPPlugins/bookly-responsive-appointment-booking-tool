<?php
namespace BooklyLite\Lib;

/**
 * Class Updater
 * @package Bookly
 */
class Updater extends Base\Updater
{
    function update_11_1()
    {
        add_option( 'ab_lite_uninstall_remove_bookly_data', '1' );
        add_option( 'ab_sms_notify_week_summary', '0' );
        add_option( 'ab_sms_notify_week_summary_sent', date( 'W' ) );

        delete_option( 'ab_sms_username' );
        delete_option( 'ab_sms_auto_recharge_balance' );
        delete_option( 'ab_sms_auto_recharge_amount' );

        do_action( 'wpml_register_single_string', 'bookly', 'ab_woocommerce_cart_info_name',  get_option( 'ab_woocommerce_cart_info_name' ) );
        do_action( 'wpml_register_single_string', 'bookly', 'ab_woocommerce_cart_info_value', get_option( 'ab_woocommerce_cart_info_value' ) );
    }

    function update_11_0()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $wpdb->query( 'ALTER TABLE `' . Entities\Payment::getTableName(). '` ADD COLUMN paid DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total' );
        $wpdb->query( 'UPDATE `' . Entities\Payment::getTableName(). '` SET paid = total' );

        $option = get_option( 'ab_cart_show_columns' );
        $option['deposit'] = array( 'show' => 1 );
        update_option( 'ab_cart_show_columns', $option );
    }

    function update_10_9()
    {
        global $wpdb;

        add_option( 'ab_appearance_staff_name_with_price', (int) ! Config::isPaymentDisabled() );
        $wpdb->query( 'ALTER TABLE `' . Entities\CustomerAppointment::getTableName(). '` ADD COLUMN `location_id` INT UNSIGNED NULL DEFAULT NULL AFTER `appointment_id`' );
        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\CouponService::getTableName() . '` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `coupon_id`   INT UNSIGNED NOT NULL,
                `service_id`  INT UNSIGNED NOT NULL,
                CONSTRAINT
                    FOREIGN KEY (coupon_id)
                    REFERENCES  ' . Entities\Coupon::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES  ' . Entities\Service::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $coupon_ids = array_keys( Entities\Coupon::query()->select( 'id' )->indexBy( 'id' )->fetchArray() );
        if ( ! empty( $coupon_ids ) ) {
            $service_ids = array_keys( Entities\Service::query()->select( 'id' )->indexBy( 'id' )->fetchArray() );
            if ( ! empty( $service_ids ) ) {
                foreach ( $coupon_ids as $coupon_id ) {
                    foreach ( $service_ids as $service_id ) {
                        $coupon_service = new Entities\CouponService();
                        $coupon_service->set( 'coupon_id', $coupon_id )->set( 'service_id', $service_id )->save();
                    }
                }
            }
        }
    }

    function update_10_0()
    {
        global $wpdb;
        global $wp_rewrite;

        $wpdb->query( 'ALTER TABLE `' . Entities\StaffService::getTableName() . '` ADD COLUMN `deposit` VARCHAR(100) NOT NULL DEFAULT "100%" AFTER `price`' );
        if ( get_option( 'bookly_service_extras_step_extras_enabled', 'missing' ) != 'missing' ) {
            $this->rename_options( array( 'bookly_service_extras_step_extras_enabled' => 'bookly_service_extras_enabled' ) );
        }
        $wpdb->query( 'ALTER TABLE ' . Entities\Staff::getTableName() . ' ADD COLUMN `attachment_id` INT UNSIGNED DEFAULT NULL AFTER `wp_user_id`' );
        require_once  ABSPATH . 'wp-admin/includes/image.php';
        $support_types = array(
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
        );
        $attachment_id = '';
        $media_path = get_option( 'ab_settings_company_logo_path' );
        if ( file_exists( $media_path ) ) {
            if ( ! isset( $wp_rewrite ) ) {
                require_once ABSPATH . WPINC . '/rewrite.php';
                $wp_rewrite = new \WP_Rewrite();
            }
            $ext = strtolower( pathinfo( $media_path, PATHINFO_EXTENSION ) );
            if ( isset( $support_types, $ext ) ) {
                $post_data = array(
                    'post_title'     => basename( $media_path ),
                    'guid'           => get_option( 'ab_settings_company_logo_url' ),
                    'post_status'    => 'publish',
                    'ping_status'    => 'closed',
                    'post_type'      => 'attachment',
                    'post_mime_type' => $support_types[ $ext ],
                );
                $attachment_id   = wp_insert_attachment( $post_data, $media_path );
                $attachment_data = wp_generate_attachment_metadata( $attachment_id, $media_path );
                wp_update_attachment_metadata( $attachment_id, $attachment_data );
            }
        }
        add_option( 'ab_settings_company_logo_attachment_id', $attachment_id );
        delete_option( 'ab_settings_company_logo_path' );
        delete_option( 'ab_settings_company_logo_url' );

        foreach ( Entities\Staff::query()->where( 'attachment_id', null )->fetchArray() as $item ) {
            $media_path = $item['avatar_path'];
            if ( file_exists( $media_path ) ) {
                $ext = strtolower( pathinfo( $media_path, PATHINFO_EXTENSION ) );
                if ( isset( $support_types, $ext ) ) {
                    $post_data = array(
                        'post_title'     => basename( $media_path ),
                        'guid'           => $item['avatar_url'],
                        'post_status'    => 'publish',
                        'ping_status'    => 'closed',
                        'post_type'      => 'attachment',
                        'post_mime_type' => $support_types[ $ext ],
                    );
                    $attachment_id   = wp_insert_attachment( $post_data, $media_path );
                    $attachment_data = wp_generate_attachment_metadata( $attachment_id, $media_path );
                    wp_update_attachment_metadata( $attachment_id, $attachment_data );
                    $staff = new Entities\Staff( $item );
                    $staff->set( 'attachment_id', $attachment_id )->save();
                }
            }
        }
        $this->drop_columns( Entities\Staff::getTableName(), array( 'avatar_url', 'avatar_path' ) );

        $wpdb->query( 'UPDATE ' . Entities\Customer::getTableName() . ' SET `wp_user_id` = NULL WHERE `wp_user_id` = 0' );
    }

    function update_9_3()
    {
        global $wpdb;

        if ( Entities\Notification::query()->where( 'type', 'client_pending_appointment_cart' )->count() == 0 ) {
            $wpdb->query( 'ALTER TABLE ' . Entities\CustomerAppointment::getTableName() . ' ADD COLUMN `payment_id` INT UNSIGNED DEFAULT NULL, ADD COLUMN `compound_service_id` INT UNSIGNED DEFAULT NULL, ADD COLUMN `compound_token` VARCHAR(255) DEFAULT NULL' );
            $wpdb->query( 'ALTER TABLE ' . Entities\Service::getTableName() . ' ADD COLUMN `type` ENUM("simple","compound") NOT NULL DEFAULT "simple", ADD COLUMN `sub_services` TEXT NOT NULL' );
            $wpdb->query( 'UPDATE ' . Entities\Service::getTableName() . ' SET `sub_services` = \'[]\'' );
            $wpdb->query( 'UPDATE ' . Entities\CustomerAppointment::getTableName() . ' SET `extras` = \'[]\' WHERE extras IS NULL' );
            $wpdb->query( 'UPDATE ' . Entities\CustomerAppointment::getTableName() . ' ca JOIN ' . Entities\Payment::getTableName() . ' p ON p.customer_appointment_id = ca.id SET ca.payment_id = p.id' );
            $this->drop_columns( Entities\Payment::getTableName(), array( 'customer_appointment_id' ) );
            $wpdb->query( 'ALTER TABLE ' . Entities\Payment::getTableName() . ' ADD COLUMN `details` TEXT' );
            $wpdb->query( 'ALTER TABLE ' . Entities\Appointment::getTableName() . ' ADD COLUMN `internal_note` TEXT' );
            $wpdb->query( 'ALTER TABLE ' . Entities\CustomerAppointment::getTableName() . ' ADD CONSTRAINT FOREIGN KEY (payment_id) REFERENCES ' . Entities\Payment::getTableName() . '(id) ON DELETE SET NULL ON UPDATE CASCADE' );
            if ( get_option( 'ab_db_version' ) != '9.2.1' ) {
                $wpdb->query( 'ALTER TABLE ' . Entities\Service::getTableName() . ' ADD COLUMN `visibility` ENUM("public","private") NOT NULL DEFAULT "public"' );
                $wpdb->query( 'ALTER TABLE ' . Entities\Staff::getTableName() . ' ADD COLUMN `visibility` ENUM("public","private") NOT NULL DEFAULT "public"' );
            }
            $notifications = array(
                array(
                    'gateway' => 'email',
                    'type'    => 'client_pending_appointment_cart',
                    'subject' => __( 'Your appointment information', 'bookly' ),
                    'message' => wpautop( __( "Dear {client_name}.\n\nThis is a confirmation that you have booked the following items:\n\n{cart_info}\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                    'active'  => 0,
                ),
                array(
                    'gateway' => 'email',
                    'type'    => 'client_approved_appointment_cart',
                    'subject' => __( 'Your appointment information', 'bookly' ),
                    'message' => wpautop( __( "Dear {client_name}.\n\nThis is a confirmation that you have booked the following items:\n\n{cart_info}\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                    'active'  => 0,
                ),

                array(
                    'gateway' => 'sms',
                    'type'    => 'client_pending_appointment_cart',
                    'subject' => '',
                    'message' => __( "Dear {client_name}.\nThis is a confirmation that you have booked the following items:\n{cart_info}\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                    'active'  => 0,
                ),
                array(
                    'gateway' => 'sms',
                    'type'    => 'client_approved_appointment_cart',
                    'subject' => '',
                    'message' => __( "Dear {client_name}.\nThis is a confirmation that you have booked the following items:\n{cart_info}\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                    'active'  => 1,
                ),
            );
            foreach ( $notifications as $data ) {
                $notification = new Entities\Notification( $data );
                $notification->save();
            }
            add_option( 'ab_settings_approve_page_url', home_url() );
            add_option( 'ab_settings_cart_notifications_combined', '0' );
        }

        $appointments = Entities\CustomerAppointment::query( 'ca' )
            ->select( 'ca.*' )
            ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
            ->where( 'p.details', null )
            ->order( 'DESC' )
            ->fetchArray();
        foreach ( $appointments as $fields ) {
            $payment = new Entities\Payment();
            if ( $payment->load( $fields['payment_id'] ) ) {
                $coupon = null;
                if ( $fields['coupon_code'] ) {
                    $coupon = new Entities\Coupon();
                    if ( ! $coupon->loadBy( array( 'code' => $fields['coupon_code'] ) ) ) {
                        $coupon = null;
                    }
                }
                $payment->setDetails( array( new Entities\CustomerAppointment( $fields ) ), $coupon )->save();
            }
        }
        $this->drop_columns( Entities\CustomerAppointment::getTableName(), array( 'coupon_code', 'coupon_discount', 'coupon_deduction' ) );
    }

    function update_9_2()
    {
        add_option( 'ab_appearance_required_employee', '0' );
        $this->register_l10n_options( array( 'ab_appearance_text_required_employee' => __( 'Please select an employee', 'bookly' ) ) );
    }

    function update_9_1()
    {
        add_option( 'ab_settings_client_cancel_appointment_action', 'delete' );
    }

    function update_9_0()
    {
        global $wpdb;

        add_option( 'ab_settings_default_appointment_status', 'approved' );
        $wpdb->query( 'ALTER TABLE ' . Entities\CustomerAppointment::getTableName() . ' ADD COLUMN `status` ENUM("pending","approved","cancelled") NOT NULL DEFAULT "approved"' );

        $notifications = array(
            array(
                'gateway' => 'email',
                'type'    => 'client_pending_appointment',
                'subject' => __( 'Your appointment information', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nThis is confirmation that you have booked {service_name}.\n\nWe are waiting you at {company_address} on {appointment_date} at {appointment_time}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'staff_pending_appointment',
                'subject' => __( 'New booking information', 'bookly' ),
                'message' => wpautop( __( "Hello.\n\nYou have new booking.\n\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'client_cancelled_appointment',
                'subject' => __( 'Booking cancellation', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nYou have cancelled your booking of {service_name} on {appointment_date} at {appointment_time}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),

            array(
                'gateway' => 'sms',
                'type'    => 'client_pending_appointment',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nThis is confirmation that you have booked {service_name}.\nWe are waiting you at {company_address} on {appointment_date} at {appointment_time}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 1,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'staff_pending_appointment',
                'subject' => '',
                'message' => __( "Hello.\nYou have new booking.\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_cancelled_appointment',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nYou have cancelled your booking of {service_name} on {appointment_date} at {appointment_time}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 0,
            )
        );
        foreach ( $notifications as $data ) {
            $notification = new Entities\Notification( $data );
            $notification->save();
        }
        $notification_types = array(
            'client_new_appointment' => 'client_approved_appointment',
            'staff_new_appointment'  => 'staff_approved_appointment',
        );
        foreach ( $notification_types as $deprecated => $name ) {
            $wpdb->update( Entities\Notification::getTableName(), array( 'type' => $name ), array( 'type' => $deprecated ) );
        }

        $l10n_strings = array(
            'email_client_new_appointment'         => 'email_client_approved_appointment',
            'email_client_new_appointment_subject' => 'email_client_approved_appointment_subject',
            'email_staff_new_appointment'          => 'email_staff_approved_appointment',
            'email_staff_new_appointment_subject'  => 'email_staff_approved_appointment_subject',
            'sms_client_new_appointment'           => 'sms_client_approved_appointment',
            'sms_staff_new_appointment'            => 'sms_staff_approved_appointment',
        );
        $this->rename_l10n_strings( $l10n_strings );

        $ab_cart_show_columns = array(
            'service'  => array( 'show' => 0 ),
            'date'     => array( 'show' => 0 ),
            'time'     => array( 'show' => 0 ),
            'employee' => array( 'show' => 0 ),
            'price'    => array( 'show' => 0 ),
        );
        foreach ( (array) get_option( 'ab_cart_show_columns' ) as $column ) {
            $ab_cart_show_columns[ $column ]['show'] = 1;
        }
        update_option( 'ab_cart_show_columns', $ab_cart_show_columns );
    }

    function update_8_5()
    {
        global $wpdb;
        $wpdb->query( 'ALTER TABLE ' . Entities\Service::getTableName() . ' ADD COLUMN info TEXT NULL' );

        // Mollie - online payments system.
        add_option( 'ab_mollie', 'disabled' );
        add_option( 'ab_mollie_api_key', '' );
        $wpdb->query( 'ALTER TABLE ' . Entities\Payment::getTableName() . " CHANGE COLUMN `type` `type` ENUM('local','coupon','paypal','authorizeNet','stripe','2checkout','payulatam','payson','mollie') NOT NULL DEFAULT 'local'" );

        add_option( 'ab_settings_cron_reminder', array( 'client_follow_up' => 21, 'client_reminder' => 18, 'staff_agenda' => 18 ) );
        add_option( 'ab_cart_show_columns', array( 'service', 'date', 'time', 'employee', 'price' ) );
        $wpdb->query( 'ALTER TABLE ' . Entities\CustomerAppointment::getTableName() . ' ADD COLUMN extras TEXT NULL' );
        $wpdb->query( 'ALTER TABLE ' . Entities\Appointment::getTableName() . ' ADD COLUMN extras_duration INT NOT NULL DEFAULT 0' );
        $wpdb->query( 'ALTER TABLE ' . Entities\Holiday::getTableName() . ' DROP COLUMN title' );
        $this->rename_options( array( 'ab_settings_cart_enabled' => 'ab_settings_step_cart_enabled' ) );
        $this->register_l10n_options( array( 'ab_appearance_text_label_pay_mollie' => __( 'I will pay now with Mollie', 'bookly' ) ) );
    }

    function update_8_4()
    {
        global $wpdb;
        if ( get_option( 'ab_custom_fields_per_service', null ) === null ) {
            $ab_custom_fields = (array) json_decode( get_option( 'ab_custom_fields' ), true );
            foreach ( $ab_custom_fields as &$field ) {
                $field['services'] = array();
            }
            update_option( 'ab_custom_fields', json_encode( $ab_custom_fields ) );

            add_option( 'ab_custom_fields_per_service', '0' );
        }
        $options = array(
            'ab_appearance_text_required_service'  => __( 'Please select a service',   'bookly' ),
            'ab_appearance_text_required_name'     => __( 'Please tell us your name',  'bookly' ),
            'ab_appearance_text_required_phone'    => __( 'Please tell us your phone', 'bookly' ),
            'ab_appearance_text_required_email'    => __( 'Please tell us your email', 'bookly' )
        );
        foreach ( $options as $option_name => $option_value ) {
            add_option( $option_name, $option_value );
            do_action( 'wpml_register_single_string', 'bookly', $option_name, $option_value );
        }

        $wpdb->query( 'ALTER TABLE ' . Entities\Staff::getTableName() . ' ADD COLUMN info TEXT NULL' );
    }

    function update_8_3()
    {
        $options = array(
            'ab_appearance_text_button_next'  => __( 'Next', 'bookly' ),
            'ab_appearance_text_button_back'  => __( 'Back', 'bookly' ),
            'ab_appearance_text_button_apply' => __( 'Apply', 'bookly' ),
            'ab_appearance_text_button_book_more' => __( 'Book More', 'bookly' )
        );
        foreach ( $options as $option_name => $option_value ) {
            add_option( $option_name, $option_value );
            do_action( 'wpml_register_single_string', 'bookly', $option_name, $option_value );
        }
    }

    function update_8_1()
    {
        add_option( 'ab_payson_funding', array( 'CREDITCARD' ) );
        add_option( 'ab_settings_cart_enabled', '0' );
        add_option( 'ab_appearance_text_step_cart', __( 'Cart', 'bookly' ) );
        add_option( 'ab_appearance_text_info_cart_step', __( "Below you can find a list of services selected for booking.\nClick BOOK MORE if you want to add more services.", 'bookly' ) );
        do_action( 'wpml_register_single_string', 'bookly', 'ab_appearance_text_step_cart', get_option( 'ab_appearance_text_step_cart' ) );
        do_action( 'wpml_register_single_string', 'bookly', 'ab_appearance_text_info_cart_step', get_option( 'ab_appearance_text_info_cart_step' ) );
        $options = array(
            'ab_appearance_text_info_first_step'       => 'ab_appearance_text_info_service_step',
            'ab_appearance_text_info_second_step'      => 'ab_appearance_text_info_time_step',
            'ab_appearance_text_info_third_step'       => 'ab_appearance_text_info_details_step',
            'ab_appearance_text_info_third_step_guest' => 'ab_appearance_text_info_details_step_guest',
            'ab_appearance_text_info_fourth_step'      => 'ab_appearance_text_info_payment_step',
            'ab_appearance_text_info_fifth_step'       => 'ab_appearance_text_info_complete_step',
            'ab_woocommerce'                           => 'ab_woocommerce_enabled'
        );
        $this->rename_options( $options );
        unset( $options['ab_woocommerce'] );
        $this->rename_l10n_strings( $options );
    }

    function update_8_0()
    {
        global $wpdb;

        $wpdb->query( 'ALTER TABLE ' . Entities\CustomerAppointment::getTableName() . ' ADD COLUMN `locale` VARCHAR(8) NULL' );

        add_option( 'ab_settings_minimum_time_prior_cancel', '0' );
        add_option( 'ab_settings_cancel_denied_page_url', home_url() );

        add_option( 'ab_sms_auto_recharge_balance', '0' );
        add_option( 'ab_sms_auto_recharge_amount', '0' );
        add_option( 'ab_sms_notify_low_balance', 1 );

        foreach ( json_decode( get_option( 'ab_custom_fields', array() ) ) as $custom_field ) {
            switch ( $custom_field->type ) {
                case 'textarea':
                case 'text-field':
                case 'captcha':
                    do_action( 'wpml_register_single_string', 'bookly', 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ), $custom_field->label );
                    break;
                case 'checkboxes':
                case 'radio-buttons':
                case 'drop-down':
                    do_action( 'wpml_register_single_string', 'bookly', 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ), $custom_field->label );
                    foreach ( $custom_field->items as $label ) {
                        do_action( 'wpml_register_single_string', 'bookly', 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ) . '=' . sanitize_title( $label ), $label );
                    }
                    break;
            }
        }
    }

    function update_7_8_2()
    {
        global $wpdb;

        $wpdb->query( 'UPDATE ' . Entities\CustomerAppointment::getTableName() .' SET custom_fields = "[]" WHERE custom_fields IS NULL or custom_fields = ""' );
    }

    function update_7_8()
    {
        global $wpdb;
        $wpdb->query( 'ALTER TABLE ' . Entities\Payment::getTableName() . " ADD COLUMN `status` ENUM('completed','pending') NOT NULL DEFAULT 'completed'" );
        $wpdb->query( 'ALTER TABLE ' . Entities\Payment::getTableName() . " CHANGE COLUMN `type` `type` ENUM('local','coupon','paypal','authorizeNet','stripe','2checkout','payulatam','payson') NOT NULL DEFAULT 'local'" );

        // PayU Latam - online payments system.
        add_option( 'ab_payulatam', 'disabled' );
        add_option( 'ab_payulatam_sandbox', '0' );
        add_option( 'ab_payulatam_api_account_id',  '' );
        add_option( 'ab_payulatam_api_key', '' );
        add_option( 'ab_payulatam_api_merchant_id', '' );

        // Payson - online payments system.
        add_option( 'ab_payson', 'disabled' );
        add_option( 'ab_payson_sandbox', '0' );
        add_option( 'ab_payson_fees_payer', 'PRIMARYRECEIVER' );
        add_option( 'ab_payson_api_agent_id', '' );
        add_option( 'ab_payson_api_key', '' );
        add_option( 'ab_payson_api_receiver_email', '' );
    }

    function update_7_7_2()
    {
        if ( get_option( 'ab_settings_pay_locally' ) == 0 ) {
            update_option( 'ab_settings_pay_locally', 'disabled' );
        }
    }

    function update_7_7()
    {
        global $wpdb;

        $wpdb->query( "ALTER TABLE " . Entities\Payment::getTableName() . " CHANGE COLUMN `type` `type` ENUM('local','coupon','paypal','authorizeNet','stripe','2checkout') NOT NULL DEFAULT 'local'" );
        $wpdb->query( "ALTER TABLE " . Entities\Payment::getTableName() . " CHANGE COLUMN `transaction` `transaction_id` VARCHAR(255) NOT NULL" );

        add_option( 'ab_currency', get_option( 'ab_paypal_currency', 'USD' ) );
        add_option( 'ab_2checkout', 'disabled' );
        add_option( 'ab_2checkout_sandbox', '0' );
        add_option( 'ab_2checkout_api_seller_id', '' );
        add_option( 'ab_2checkout_api_secret_word', '' );
        add_option( 'ab_stripe_publishable_key', '' );
        if ( get_option( 'ab_stripe' ) == 0 ) {
            update_option( 'ab_stripe', 'disabled' );
        }
        delete_option( 'ab_paypal_currency' );

        add_option( 'ab_appearance_text_label_pay_paypal',   __( 'I will pay now with PayPal', 'bookly' ) );
        add_option( 'ab_appearance_text_label_pay_ccard',    __( 'I will pay now with Credit Card', 'bookly' ) );
        add_option( 'ab_appearance_text_label_ccard_number', __( 'Credit Card Number',  'bookly' ) );
        add_option( 'ab_appearance_text_label_ccard_expire', __( 'Expiration Date',     'bookly' ) );
        add_option( 'ab_appearance_text_label_ccard_code',   __( 'Card Security Code',  'bookly' ) );
        foreach ( array( 'ab_appearance_text_label_pay_paypal', 'ab_appearance_text_label_pay_ccard', 'ab_appearance_text_label_ccard_number', 'ab_appearance_text_label_ccard_expire', 'text_label_ccard_code' ) as $option_name ) {
            do_action( 'wpml_register_single_string', 'bookly', $option_name, get_option( $option_name ) );
        }
    }

    function update_7_6()
    {
        global $wpdb;

        $wpdb->query( 'ALTER TABLE ' . Entities\Service::getTableName() . ' ADD COLUMN `padding_left` INT NOT NULL DEFAULT 0, ADD COLUMN `padding_right` INT NOT NULL DEFAULT 0' );
    }

    function update_7_4()
    {
        add_option( 'ab_email_content_type', 'html' );
        add_option( 'ab_email_notification_reply_to_customers', '1' );
    }

    function update_7_3()
    {
        add_option( 'ab_appearance_text_info_third_step_guest', '' );

        $staff_members = Entities\Staff::query( 's' )->select( 's.id, s.full_name' )->fetchArray();
        foreach ( $staff_members as $staff ) {
            do_action( 'wpml_register_single_string', 'bookly', 'staff_' . $staff['id'], $staff['full_name'] );
        }
        $categories = Entities\Category::query( 'c' )->select( 'c.id, c.name' )->fetchArray();
        foreach ( $categories as $category ) {
            do_action( 'wpml_register_single_string', 'bookly', 'category_' . $category['id'], $category['name'] );
        }
        $services = Entities\Service::query( 's' )->select( 's.id, s.title' )->fetchArray();
        foreach ( $services as $service ) {
            do_action( 'wpml_register_single_string', 'bookly', 'service_' . $service['id'], $service['title'] );
        }
    }

    function update_7_1()
    {
        global $wpdb;

        // Register notifications for translate in WPML.
        $notifications = Entities\Notification::query( 'n' )->select( 'n.gateway, n.type, n.subject, n.message');
        foreach( $notifications->fetchArray() as $notification ){
            do_action( 'wpml_register_single_string', 'bookly', $notification['gateway'].'_'.$notification['type'], $notification['message'] );
            if ( $notification['gateway'] == 'email' ) {
                do_action( 'wpml_register_single_string', 'bookly', $notification['gateway'].'_'.$notification['type'].'_subject', $notification['subject'] );
            }
        }
        $options = $wpdb->get_results( 'SELECT option_value, option_name FROM '.$wpdb->options.' WHERE option_name LIKE \'ab_appearance_text_%\'' );
        foreach ( $options as $option ) {
            do_action( 'wpml_register_single_string', 'bookly', $option->option_name, $option->option_value );
        }

        add_option( 'ab_settings_phone_default_country', 'auto' );
    }

    function update_7_0_1()
    {
        global $wpdb;
        // Recreate tables with constraints if they do not exist due to "Identifier name 'XXX' is too long".
        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Service::getTableName() . '` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `title`       VARCHAR(255) DEFAULT "",
                `duration`    INT NOT NULL DEFAULT 900,
                `price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `color`       VARCHAR(255) NOT NULL DEFAULT "#FFFFFF",
                `category_id` INT UNSIGNED,
                `capacity`    INT NOT NULL DEFAULT 1,
                `position`    INT NOT NULL DEFAULT 9999,
                CONSTRAINT
                    FOREIGN KEY (category_id)
                    REFERENCES ' . Entities\Category::getTableName() . '(id)
                    ON DELETE SET NULL
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\StaffService::getTableName() . '` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`   INT UNSIGNED NOT NULL,
                `service_id` INT UNSIGNED NOT NULL,
                `price`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `capacity`   INT NOT NULL DEFAULT 1,
                UNIQUE KEY unique_ids_idx (staff_id, service_id),
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . Entities\Staff::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES ' . Entities\Service::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\StaffScheduleItem::getTableName() . '` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`   INT UNSIGNED NOT NULL,
                `day_index`  INT UNSIGNED NOT NULL,
                `start_time` TIME,
                `end_time`   TIME,
                UNIQUE KEY unique_ids_idx (staff_id, day_index),
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . Entities\Staff::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
             ) ENGINE = INNODB
             DEFAULT CHARACTER SET = utf8
             COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\ScheduleItemBreak::getTableName() . '` (
                `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_schedule_item_id` INT UNSIGNED NOT NULL,
                `start_time`             TIME,
                `end_time`               TIME,
                CONSTRAINT
                    FOREIGN KEY (staff_schedule_item_id)
                    REFERENCES ' . Entities\StaffScheduleItem::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
             ) ENGINE = INNODB
             DEFAULT CHARACTER SET = utf8
             COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Appointment::getTableName() . '` (
                `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`        INT UNSIGNED NOT NULL,
                `service_id`      INT UNSIGNED,
                `start_date`      DATETIME NOT NULL,
                `end_date`        DATETIME NOT NULL,
                `google_event_id` VARCHAR(255) DEFAULT NULL,
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . Entities\Staff::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES ' . Entities\Service::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Holiday::getTableName() . '` (
                  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  `staff_id`     INT UNSIGNED NULL DEFAULT NULL,
                  `parent_id`    INT UNSIGNED NULL DEFAULT NULL,
                  `date`         DATE NOT NULL,
                  `repeat_event` TINYINT(1) NOT NULL DEFAULT 0,
                  `title`        VARCHAR(255) DEFAULT "",
                  CONSTRAINT
                      FOREIGN KEY (staff_id)
                      REFERENCES ' . Entities\Staff::getTableName() . '(id)
                      ON DELETE CASCADE
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\CustomerAppointment::getTableName() . '` (
                `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `customer_id`       INT UNSIGNED NOT NULL,
                `appointment_id`    INT UNSIGNED NOT NULL,
                `number_of_persons` INT UNSIGNED NOT NULL DEFAULT 1,
                `custom_fields`     TEXT,
                `coupon_code`       VARCHAR(255) DEFAULT NULL,
                `coupon_discount`   DECIMAL(10,2) DEFAULT NULL,
                `coupon_deduction`  DECIMAL(10,2) DEFAULT NULL,
                `token`             VARCHAR(255) DEFAULT NULL,
                `time_zone_offset`  INT,
                CONSTRAINT
                    FOREIGN KEY (customer_id)
                    REFERENCES  ' . Entities\Customer::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT
                    FOREIGN KEY (appointment_id)
                    REFERENCES  ' . Entities\Appointment::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Payment::getTableName() . '` (
                `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `created`                 DATETIME NOT NULL,
                `type`                    ENUM("local","coupon","paypal","authorizeNet","stripe") NOT NULL DEFAULT "local",
                `customer_appointment_id` INT UNSIGNED NOT NULL,
                `token`                   VARCHAR(255) NOT NULL,
                `transaction`             VARCHAR(255) NOT NULL,
                `total`                   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                CONSTRAINT
                    FOREIGN KEY (customer_appointment_id)
                    REFERENCES  ' . Entities\CustomerAppointment::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\SentNotification::getTableName() . '` (
                `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `customer_appointment_id` INT UNSIGNED,
                `staff_id`                INT UNSIGNED,
                `gateway`                 ENUM("email","sms") NOT NULL DEFAULT "email",
                `type`                    VARCHAR(60) NOT NULL,
                `created`                 DATETIME NOT NULL,
                CONSTRAINT
                    FOREIGN KEY (customer_appointment_id)
                    REFERENCES  ' . Entities\CustomerAppointment::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES  ' . Entities\Staff::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci'
        );
    }

    function update_7_0()
    {
        global $wpdb;

        $wpdb->query( 'ALTER TABLE `ab_customer_appointment` ADD `coupon_deduction` DECIMAL(10,2) DEFAULT NULL AFTER `coupon_discount`' );
        $wpdb->query( 'ALTER TABLE `ab_coupons` CHANGE COLUMN `used` `used` INT UNSIGNED NOT NULL DEFAULT 0,
                       ADD COLUMN `deduction` DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER `discount`,
                       ADD COLUMN `usage_limit` INT UNSIGNED NOT NULL DEFAULT 1' );

        $wpdb->query( 'ALTER TABLE `ab_notifications` CHANGE `slug` `type` VARCHAR(255) NOT NULL DEFAULT ""' );

        // SMS.
        $wpdb->query( 'ALTER TABLE `ab_notifications` ADD `gateway` ENUM("email","sms") NOT NULL DEFAULT "email"' );
        $wpdb->query( 'UPDATE `ab_notifications` SET `gateway` = "email"' );
        $sms_notifies = array(
            array(
                'type'    => 'client_new_appointment',
                'message' => __( "Dear {client_name}.\nThis is confirmation that you have booked {service_name}.\nWe are waiting you at {company_address} on {appointment_date} at {appointment_time}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 1,
            ),
            array(
                'type'    => 'staff_new_appointment',
                'message' => __( "Hello.\nYou have new booking.\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'type'    => 'client_reminder',
                'message' => __( "Dear {client_name}.\nWe would like to remind you that you have booked {service_name} tomorrow on {appointment_time}. We are waiting you at {company_address}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'type'    => 'client_follow_up',
                'message' => __( "Dear {client_name}.\nThank you for choosing {company_name}. We hope you were satisfied with your {service_name}.\nThank you and we look forward to seeing you again soon.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'type'    => 'staff_agenda',
                'message' => __( "Hello.\nYour agenda for tomorrow is:\n{next_day_agenda}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'type'    => 'staff_cancelled_appointment',
                'message' => __( "Hello.\nThe following booking has been cancelled.\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'type'    => 'client_new_wp_user',
                'message' => __( "Hello.\nAn account was created for you at {site_address}\nYour user details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookly' ),
                'active'  => 1,
            ),
        );
        // Insert notifications.
        foreach ( $sms_notifies as $data ) {
            $wpdb->insert( 'ab_notifications', array(
                'gateway' => 'sms',
                'type'    => $data['type'],
                'subject' => '',
                'message' => $data['message'],
                'active'  => $data['active'],
            ) );
        }

        // Rename notifications.
        $notifications = array(
            'client_info'        => 'client_new_appointment',
            'provider_info'      => 'staff_new_appointment',
            'evening_next_day'   => 'client_reminder',
            'evening_after'      => 'client_follow_up',
            'event_next_day'     => 'staff_agenda',
            'cancel_appointment' => 'staff_cancelled_appointment',
            'new_wp_user'        => 'client_new_wp_user'
        );
        foreach ( $notifications as $from => $to ) {
            $wpdb->query( "UPDATE `ab_notifications` SET `type` = '$to' WHERE `type` = '$from'" );
        }

        $this->drop( 'ab_email_notification' );

        // Rename tables.
        $ab_tables = array(
            'ab_appointment'          => Entities\Appointment::getTableName(),
            'ab_category'             => Entities\Category::getTableName(),
            'ab_coupons'              => Entities\Coupon::getTableName(),
            'ab_customer'             => Entities\Customer::getTableName(),
            'ab_customer_appointment' => Entities\CustomerAppointment::getTableName(),
            'ab_holiday'              => Entities\Holiday::getTableName(),
            'ab_notifications'        => Entities\Notification::getTableName(),
            'ab_payment'              => Entities\Payment::getTableName(),
            'ab_schedule_item_break'  => Entities\ScheduleItemBreak::getTableName(),
            'ab_service'              => Entities\Service::getTableName(),
            'ab_staff'                => Entities\Staff::getTableName(),
            'ab_staff_schedule_item'  => Entities\StaffScheduleItem::getTableName(),
            'ab_staff_service'        => Entities\StaffService::getTableName(),
        );
        foreach ( $ab_tables as $from => $to ) {
            $wpdb->query( "ALTER TABLE `{$from}` RENAME TO `{$to}`" );
        }

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS  `" . Entities\SentNotification::getTableName() . "` (
                `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `customer_appointment_id` INT UNSIGNED,
                `staff_id`                INT UNSIGNED,
                `gateway`                 ENUM('email','sms') NOT NULL DEFAULT 'email',
                `type`                    VARCHAR(60) NOT NULL,
                `created`                 DATETIME NOT NULL,
                CONSTRAINT fk_" . Entities\SentNotification::getTableName() . "_" . Entities\CustomerAppointment::getTableName() . "_id
                    FOREIGN KEY (customer_appointment_id)
                    REFERENCES  " . Entities\CustomerAppointment::getTableName() . "(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT fk_" . Entities\SentNotification::getTableName() . "_" . Entities\Staff::getTableName() . "_id
                    FOREIGN KEY (staff_id)
                    REFERENCES  " . Entities\Staff::getTableName() . "(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci"
        );

        // Google Calendar.
        add_option( 'ab_settings_google_event_title', '{service_name}' );
        // Link assets.
        add_option( 'ab_settings_link_assets_method', 'enqueue' );
        // SMS.
        add_option( 'ab_sms_default_country_code', '' );
    }

    function update_6_2()
    {
        global $wpdb;

        $wpdb->query( 'ALTER TABLE `ab_holiday` CHANGE `holiday` `date` DATE NOT NULL' );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS  `ab_email_notification` (
                `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `customer_appointment_id` INT UNSIGNED,
                `staff_id`                INT UNSIGNED,
                `type`                    VARCHAR(60) NOT NULL,
                `created`                 DATETIME NOT NULL,
                CONSTRAINT fk_ab_email_notification_customer_appointment_id
                    FOREIGN KEY (customer_appointment_id)
                    REFERENCES  ab_customer_appointment(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT fk_ab_email_notification_staff_id
                    FOREIGN KEY (staff_id)
                    REFERENCES  ab_staff(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci"
        );
    }

    function update_6_0()
    {
        // WooCommerce.
        add_option( 'ab_woocommerce', '0' );
        add_option( 'ab_woocommerce_product', '' );
        add_option( 'ab_woocommerce_cart_info_name',  __( 'Appointment', 'bookly' ) );
        add_option( 'ab_woocommerce_cart_info_value', __( 'Date', 'bookly' ) . ": {appointment_date}\n" . __( 'Time', 'bookly' ) . ": {appointment_time}\n" . __( 'Service', 'bookly' ) . ": {service_name}" );
        // Staff Members Profile.
        add_option( 'ab_settings_allow_staff_members_edit_profile', 0 );
    }

    function update_5_0()
    {
        global $wpdb;

        // User profiles.
        add_option('ab_settings_create_account', 0, '', 'yes');
        $wpdb->query('ALTER TABLE `ab_customer` ADD `wp_user_id` BIGINT(20) UNSIGNED');
        // Move coupons from ab_payment to ab_customer_appointment.
        $wpdb->query('ALTER TABLE `ab_customer_appointment` ADD `coupon_code` VARCHAR(255) DEFAULT NULL');
        $wpdb->query('ALTER TABLE `ab_customer_appointment` ADD `coupon_discount` DECIMAL(10,2) DEFAULT NULL');
        $payments = $wpdb->get_results( 'SELECT * FROM `ab_payment`', ARRAY_A );
        foreach ( $payments as $payment ) {
            if ( $payment['coupon'] ) {
                $discount = $wpdb->get_var( $wpdb->prepare( 'SELECT `discount` FROM `ab_coupons` WHERE `code` = %s', $payment['coupon'] ) );
                $wpdb->update(
                    'ab_customer_appointment',
                    array(
                        'coupon_code' => $payment['coupon'],
                        'coupon_discount' => $discount ?: 0
                    ),
                    array( 'id' => $payment['customer_appointment_id'] )
                );
            }
        }
        $wpdb->query('ALTER TABLE `ab_payment` DROP `coupon`');
        // New notifications.
        $wpdb->insert( 'ab_notifications', array(
            'slug'    => 'cancel_appointment',
            'subject' => __( 'Booking cancellation', 'bookly' ),
            'message' => __( "Hello.\n\nThe following booking has been cancelled.\n\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
            'active'  => 0,
        ) );

        $wpdb->insert( 'ab_notifications', array(
            'slug'    => 'new_wp_user',
            'subject' => __( 'New customer', 'bookly' ),
            'message' => __( "Hello.\n\nAn account was created for you at {site_address}\n\nYour user details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookly' ),
            'active'  => 1,
        ) );
        // Link ab_email_notification to ab_customer_appointment.
        $wpdb->query('TRUNCATE TABLE `ab_email_notification`');
        $wpdb->query('ALTER TABLE `ab_email_notification` ADD `customer_appointment_id` INT UNSIGNED');
        $wpdb->query('ALTER TABLE `ab_email_notification`
            ADD CONSTRAINT fk_ab_email_notification_customer_appointment_id
              FOREIGN KEY (customer_appointment_id)
              REFERENCES  ab_customer_appointment(id)
              ON DELETE   CASCADE
              ON UPDATE   CASCADE
        ');
        $wpdb->query('ALTER TABLE `ab_email_notification` DROP FOREIGN KEY fk_ab_email_notification_customer_id, DROP INDEX ab_email_notification_customer_id_idx');
        $wpdb->query('ALTER TABLE `ab_email_notification` DROP `customer_id`');
    }

    function update_4_6()
    {
        global $wpdb;

        add_option('ab_appearance_text_label_number_of_persons', __( 'Number of persons', 'bookly' ), '', 'yes');
        add_option('ab_settings_google_limit_events', 0, '', 'yes');
        add_option('ab_appearance_show_calendar', 0, '', 'yes');

        $wpdb->query('ALTER TABLE `ab_customer_appointment` ADD time_zone_offset INT');
        $wpdb->query('ALTER TABLE `ab_customer_appointment` ADD number_of_persons INT UNSIGNED NOT NULL DEFAULT 1');
    }

    function update_4_4()
    {
        add_option('ab_settings_maximum_available_days_for_booking', 365, '', 'yes');
    }

    function update_4_3()
    {
        global $wpdb;

        // Positioning in lists.
        $wpdb->query("ALTER TABLE `ab_staff` ADD `position` INT NOT NULL DEFAULT 9999;");
        $wpdb->query("ALTER TABLE `ab_category` ADD `position` INT NOT NULL DEFAULT 9999;");
        $wpdb->query("ALTER TABLE `ab_service` ADD `position` INT NOT NULL DEFAULT 9999;");

        add_option('ab_appearance_show_blocked_timeslots', 0, '', 'yes');
        add_option('ab_appearance_show_day_one_column', 0, '', 'yes');
    }

    function update_4_2()
    {
        global $wpdb;

        $wpdb->query( "ALTER TABLE ab_payment ADD `customer_appointment_id` INT UNSIGNED DEFAULT NULL" );
        $payments = $wpdb->get_results('SELECT id, customer_id, appointment_id from `ab_payment` ');

        foreach ($payments as $payment) {
            $customer_appointment = $wpdb->get_row($wpdb->prepare('SELECT id from `ab_customer_appointment` WHERE `customer_id` = %d and `appointment_id` = %d LIMIT 1', $payment->customer_id, $payment->appointment_id));
            if ($customer_appointment) {
                $wpdb->update('ab_payment', array('customer_appointment_id' => $customer_appointment->id), array('id' => $payment->id));
            }
        }

        $wpdb->query("ALTER TABLE ab_payment
                        DROP FOREIGN KEY fk_ab_payment_customer_id, DROP FOREIGN KEY fk_ab_payment_appointment_id, DROP customer_id, DROP appointment_id,
                        ADD INDEX ab_payment_customer_appointment_id_idx (customer_appointment_id),
                        ADD CONSTRAINT fk_ab_payment_customer_appointment_id
                          FOREIGN KEY ab_payment_customer_appointment_id_idx (customer_appointment_id)
                          REFERENCES  ab_customer_appointment(id)
                          ON DELETE   CASCADE
                          ON UPDATE   CASCADE;
            ");

        add_option('ab_appearance_text_label_pay_locally', __( 'I will pay locally', 'bookly' ), '', 'yes');
        add_option('ab_settings_google_two_way_sync', 1, '', 'yes');
    }

    function update_4_1()
    {
        add_option('ab_settings_final_step_url', '', '', 'yes');
    }

    function update_4_0()
    {
        global $wpdb;

        add_option('ab_custom_fields', '[{"type":"textarea","label":"Notes","required":false,"id":1}]', '', 'yes');

        // Create relation between customer and appointment
        $ab_customer_appointments = $wpdb->get_results('SELECT * from `ab_customer_appointment` ');
        foreach ($ab_customer_appointments as $ab_customer_appointment){
            $wpdb->update(
                'ab_customer_appointment',
                array('notes' => json_encode(array(array('id' => 1, 'value' => $ab_customer_appointment->notes)))),
                array('id' => $ab_customer_appointment->id)
            );
        }

        $wpdb->query( "ALTER TABLE ab_customer_appointment CHANGE `notes` `custom_fields` TEXT" );

        delete_option('ab_appearance_text_label_notes');

        $wpdb->query( "ALTER TABLE ab_payment CHANGE `type` `type` ENUM('local', 'coupon', 'paypal', 'authorizeNet', 'stripe') NOT NULL DEFAULT 'local';" );
    }

    function update_3_4()
    {
        global $wpdb;

        $wpdb->query("ALTER TABLE `ab_payment` DROP `status`;");

        add_option('ab_settings_minimum_time_prior_booking', 0, '', 'yes');

        delete_option('ab_settings_no_current_day_appointments');
    }

    function update_3_2()
    {
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

        add_option('ab_appearance_text_label_coupon', __( 'Coupon', 'bookly' ), '', 'yes');
        add_option('ab_appearance_text_info_coupon', __( 'The price for the service is {service_price}.', 'bookly' ), '', 'yes');
        add_option('ab_settings_coupons', '0', '', 'yes');
        add_option('ab_settings_google_client_id', '', '', 'yes');
        add_option('ab_settings_google_client_secret', '', '', 'yes');
    }

    function update_3_0()
    {
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

    function update_2_2_0()
    {
        global $wpdb;

        // stripe.com
        $wpdb->query( "ALTER TABLE ab_payment CHANGE `type` `type` ENUM('local', 'paypal', 'authorizeNet', 'stripe') NOT NULL DEFAULT 'local'" );
        add_option( 'ab_stripe', '0', '', 'yes' );
        add_option( 'ab_stripe_secret_key', '', '', 'yes' );

        // Remove old options.
        delete_option( 'ab_appearance_progress_tracker_type' );
    }

    function update_2_1_0()
    {
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

    function update_2_0_1()
    {
        global $wpdb;

        // In previous migration there was a problem with adding these 2 fields. The problem has been resolved,
        // but we need to take care of users who have already run the previous migration script.
        $wpdb->query( 'ALTER TABLE `ab_staff` ADD `google_user` VARCHAR(255) DEFAULT NULL ;' );
        $wpdb->query( 'ALTER TABLE `ab_staff` ADD `google_pass` VARCHAR(255) DEFAULT NULL ;' );

        delete_option( 'ab_fixtures' );
        delete_option( 'ab_send_notifications_cron_sh_path' );
    }

    function update_2_0()
    {
        global $wpdb;

        add_option( 'ab_settings_time_slot_length', '15', '', 'yes' );
        add_option( 'ab_settings_no_current_day_appointments', '0', '', 'yes' );
        add_option( 'ab_settings_use_client_time_zone', '0', '', 'yes' );
        add_option( 'ab_settings_cancel_page_url', home_url(), '', 'yes' );

        // Add new appearance text options.
        add_option( 'ab_appearance_text_step_service', __( 'Service', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_step_time', __( 'Time', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_step_details', __( 'Details', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_step_payment', __( 'Payment', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_step_done', __( 'Done', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_category', __( 'Category', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_service', __( 'Service', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_employee', __( 'Employee', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_select_date', __( 'I\'m available on or after', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_start_from', __( 'Start from', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_finish_by', __( 'Finish by', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_name', __( 'Name', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_phone', __( 'Phone', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_email', __( 'Email', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_label_notes', __( 'Notes (optional)', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_option_service', __( 'Select service', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_option_category', __( 'Select category', 'bookly' ), '', 'yes' );
        add_option( 'ab_appearance_text_option_employee', __( 'Any', 'bookly' ), '', 'yes' );

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