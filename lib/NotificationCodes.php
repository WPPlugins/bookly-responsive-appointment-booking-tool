<?php
namespace BooklyLite\Lib;

/**
 * Class NotificationCodes
 * @package BooklyLite\Lib
 */
class NotificationCodes
{
    /**
     * Source data for all replacements.
     * @var array
     */
    private $data = array(
        'amount_due'          => '',
        'amount_paid'         => '',
        'appointment_end'     => '',
        'appointment_start'   => '',
        'appointment_token'   => '',
        'cancellation_reason' => '',
        'cart_info'           => array(),
        'category_name'       => '',
        'client_email'        => '',
        'client_name'         => '',
        'client_phone'        => '',
        'custom_fields'       => '',
        'custom_fields_2c'    => '',
        'new_password'        => '',
        'new_username'        => '',
        'next_day_agenda'     => '',
        'number_of_persons'   => '',
        'payment_type'        => '',
        'service_info'        => '',
        'service_name'        => '',
        'service_price'       => '',
        'site_address'        => '',
        'staff_email'         => '',
        'staff_info'          => '',
        'staff_name'          => '',
        'staff_phone'         => '',
        'staff_photo'         => '',
        'total_price'         => '',
        // Extras
        'extras'             => '',
        'extras_total_price' => 0,
        // Recurring Appointments
        'appointment_schedule'       => '',
        'appointment_schedule_c'     => '',
    );

    /**
     * Set data parameter.
     *
     * @param string $name
     * @param mixed $value
     */
    public function set( $name, $value )
    {
        $this->data[ $name ] = $value;
    }

    /**
     * Get data parameter.
     *
     * @param        $name
     * @param string $default
     * @return mixed|string
     */
    public function get( $name, $default = null )
    {
        return array_key_exists( $name, $this->data ) ? $this->data[ $name ] : $default;
    }

    /**
     * Do replacements.
     *
     * @since 10.9 format codes {code}, [[CODE]] is deprecated.
     *
     * @param string $text
     * @param string $format
     * @return string
     */
    public function replace( $text, $format = 'text' )
    {
        $company_logo = '';
        $staff_photo  = '';
        $cart_info_c  = $cart_info = '';
        $format = 'text';

        // Approve/Cancel appointment URL and <a> tag.
        $approve_appointment_url = admin_url( 'admin-ajax.php?action=bookly_approve_appointment&token=' . urlencode( Utils\Common::xorEncrypt( $this->get( 'appointment_token' ), 'approve' ) ) );
        $cancel_appointment = $cancel_appointment_url = admin_url( 'admin-ajax.php?action=bookly_cancel_appointment&token=' . $this->get( 'appointment_token' ) );

        // Add to Google Calendar link.
        $google_calendar_url = sprintf( 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=%s&dates=%s/%s&details=%s',
            urlencode( $this->get( 'service_name' ) ),
            date( 'Ymd\THis', strtotime( $this->get( 'appointment_start' ) ) ),
            date( 'Ymd\THis', strtotime( $this->get( 'appointment_end' ) ) ),
            urlencode( sprintf( "%s\n%s", $this->get( 'service_name' ), $this->get( 'staff_name' ) ) )
        );

        // Cart info.
        $cart_info_data = $this->get( 'cart_info' );
        if ( ! empty ( $cart_info_data ) ) {
            $cart_columns = get_option( 'bookly_cart_show_columns', array() );
            $ths = array();
            foreach ( $cart_columns as $column => $attr ) {
                if ( $attr['show'] ) {
                    switch ( $column ) {
                        case 'service':
                            $ths[] = Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' );
                            break;
                        case 'date':
                            $ths[] = __( 'Date', 'bookly' );
                            break;
                        case 'time':
                            $ths[] = __( 'Time', 'bookly' );
                            break;
                        case 'employee':
                            $ths[] = Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' );
                            break;
                        case 'price':
                            $ths[] = __( 'Price', 'bookly' );
                            break;
                    }
                }
            }
            $trs = array();
            foreach ( $cart_info_data as $codes ) {
                $tds = array();
                foreach ( $cart_columns as $column => $attr ) {
                    if ( $attr['show'] ) {
                        switch ( $column ) {
                            case 'service':
                                $service_name = $codes['service_name'];
                                if ( ! empty ( $codes['extras'] ) ) {
                                    $extras = '';
                                    foreach ( $codes['extras'] as $extra ) {
                                        $extras .= ', ' . str_replace( '&nbsp;&times;&nbsp;', ' x ', $extra['title'] );
                                    }
                                    $service_name .= $extras;
                                }
                                $tds[] = $service_name;
                                break;
                            case 'date':
                                $tds[] = Utils\DateTime::formatDate( $codes['appointment_start'] );
                                break;
                            case 'time':
                                $tds[] = Utils\DateTime::formatTime( $codes['appointment_start'] );
                                break;
                            case 'employee':
                                $tds[] = $codes['staff_name'];
                                break;
                            case 'price':
                                $tds[] = Utils\Common::formatPrice( $codes['appointment_price'] );
                                break;
                        }
                    }
                }
                $tds[] = $codes['cancel_url'];
                $trs[] = $tds;
            }
            foreach ( $trs as $tr ) {
                $cancel_url = array_pop( $tr );
                foreach ( $ths as $position => $column ) {
                    $cart_info   .= $column . ' ' . $tr[ $position ] . "\r\n";
                    $cart_info_c .= $column . ' ' . $tr[ $position ] . "\r\n";
                }
                $cart_info .= "\r\n";
                $cart_info_c .= __( 'Cancel', 'bookly' )  . ' ' . $cancel_url . "\r\n\r\n";
            }
        }
        // Codes.
        $codes = array(
            '{amount_due}'             => Utils\Common::formatPrice( $this->get( 'amount_due' ) ),
            '{amount_paid}'            => Utils\Common::formatPrice( $this->get( 'amount_paid' ) ),
            '{appointment_date}'       => Utils\DateTime::formatDate( $this->get( 'appointment_start' ) ),
            '{appointment_time}'       => Utils\DateTime::formatTime( $this->get( 'appointment_start' ) ),
            '{approve_appointment_url}'=> $approve_appointment_url,
            '{booking_number}'         => $this->get( 'booking_number' ),
            '{cancel_appointment}'     => $cancel_appointment,
            '{cancel_appointment_url}' => $cancel_appointment_url,
            '{cart_info}'              => $cart_info,
            '{cart_info_c}'            => $cart_info_c,
            '{category_name}'          => $this->get( 'category_name' ),
            '{client_email}'           => $this->get( 'client_email' ),
            '{client_name}'            => $this->get( 'client_name' ),
            '{client_phone}'           => $this->get( 'client_phone' ),
            '{company_address}'        => get_option( 'bookly_co_address' ),
            '{company_logo}'           => $company_logo,
            '{company_name}'           => get_option( 'bookly_co_name' ),
            '{company_phone}'          => get_option( 'bookly_co_phone' ),
            '{company_website}'        => get_option( 'bookly_co_website' ),
            '{custom_fields}'          => $this->get( 'custom_fields' ),
            '{custom_fields_2c}'       => $this->get( 'custom_fields' ),
            '{google_calendar_url}'    => $google_calendar_url,
            '{new_password}'           => $this->get( 'new_password' ),
            '{new_username}'           => $this->get( 'new_username' ),
            '{next_day_agenda}'        => $this->get( 'next_day_agenda' ),
            '{number_of_persons}'      => $this->get( 'number_of_persons' ),
            '{payment_type}'           => $this->get( 'payment_type' ),
            '{service_info}'           => $this->get( 'service_info' ),
            '{service_name}'           => $this->get( 'service_name' ),
            '{service_price}'          => Utils\Common::formatPrice( $this->get( 'service_price' ) ),
            '{site_address}'           => $this->get( 'site_address' ),
            '{staff_email}'            => $this->get( 'staff_email' ),
            '{staff_info}'             => $this->get( 'staff_info' ),
            '{staff_name}'             => $this->get( 'staff_name' ),
            '{staff_phone}'            => $this->get( 'staff_phone' ),
            '{staff_photo}'            => $staff_photo,
            '{tomorrow_date}'          => Utils\DateTime::formatDate( $this->get( 'appointment_start' ) ),
            '{total_price}'            => Utils\Common::formatPrice( $this->get( 'total_price' ) ),
            '{cancellation_reason}'    => $this->get( 'cancellation_reason' ),
        );
        $codes = apply_filters( 'bookly_replace_notification_codes', $codes, $this, $format );

        // Support deprecated codes [[CODE]]
        foreach ( array_keys( $codes ) as $code_key ) {
            if ( $code_key{1} == '[' ) {
                $codes[ '{' . strtolower( substr( $code_key, 2, -2 ) ) . '}' ] = $codes[ $code_key ];
            } else {
                $codes[ '[[' . strtoupper( substr( $code_key, 1, -1 ) ) . ']]' ] = $codes[ $code_key ];
            }
        }

        return strtr( $text, $codes );
    }
}