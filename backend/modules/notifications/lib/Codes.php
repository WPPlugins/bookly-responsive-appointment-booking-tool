<?php
namespace Bookly\Backend\Modules\Notifications\Lib;

use Bookly\Lib;
use Bookly\Lib\Entities\Notification;
use Bookly\Backend\Modules\Notifications\Proxy;

/**
 * Class Codes
 * @package Bookly\Backend\Modules\Notifications\Lib
 */
class Codes
{
    /** @var string */
    protected $type;

    /** @var array */
    protected $codes;

    /**
     * Constructor.
     *
     * @param string $type
     */
    public function __construct( $type = 'email' )
    {
        $this->type  = $type;
        $this->codes = array(
            'appointment' => array(
                'appointment_date'               => __( 'date of appointment', 'bookly' ),
                'appointment_end_date'           => __( 'end date of appointment', 'bookly' ),
                'appointment_end_time'           => __( 'end time of appointment', 'bookly' ),
                'appointment_notes'              => __( 'customer notes for appointment', 'bookly' ),
                'appointment_time'               => __( 'time of appointment', 'bookly' ),
                'booking_number'                 => __( 'booking number', 'bookly' ),
            ),
            'cart' => array(
                'cart_info'                      => __( 'cart information', 'bookly' ),
                'cart_info_c'                    => __( 'cart information with cancel', 'bookly' ),
            ),
            'category' => array(
                'category_name'                  => __( 'name of category', 'bookly' ),
            ),
            'company' => array(
                'company_address'                => __( 'address of company', 'bookly' ),
                'company_name'                   => __( 'name of company', 'bookly' ),
                'company_phone'                  => __( 'company phone', 'bookly' ),
                'company_website'                => __( 'company web-site address', 'bookly' ),
            ),
            'customer' => array(
                'client_address'                 => __( 'address of client', 'bookly' ),
                'client_email'                   => __( 'email of client', 'bookly' ),
                'client_first_name'              => __( 'first name of client', 'bookly' ),
                'client_last_name'               => __( 'last name of client', 'bookly' ),
                'client_name'                    => __( 'full name of client', 'bookly' ),
                'client_phone'                   => __( 'phone of client', 'bookly' ),
            ),
            'customer_timezone' => array(
                'client_timezone'                => __( 'time zone of client', 'bookly' ),
            ),
            'customer_appointment' => array(
                'approve_appointment_url'        => __( 'URL of approve appointment link (to use inside <a> tag)', 'bookly' ),
                'cancel_appointment_confirm_url' => __( 'URL of cancel appointment link with confirmation (to use inside <a> tag)', 'bookly' ),
                'cancel_appointment_url'         => __( 'URL of cancel appointment link (to use inside <a> tag)', 'bookly' ),
                'cancellation_reason'            => __( 'reason you mentioned while deleting appointment', 'bookly' ),
                'google_calendar_url'            => __( 'URL for adding event to client\'s Google Calendar (to use inside <a> tag)', 'bookly' ),
                'number_of_persons'              => __( 'number of persons', 'bookly' ),
                'reject_appointment_url'         => __( 'URL of reject appointment link (to use inside <a> tag)', 'bookly' ),
            ),
            'payment' => array(
                'payment_type'                   => __( 'payment type', 'bookly' ),
                'total_price'                    => __( 'total price of booking (sum of all cart items after applying coupon)' ),
            ),
            'service' => array(
                'service_duration'               => __( 'duration of service', 'bookly' ),
                'service_info'                   => __( 'info of service', 'bookly' ),
                'service_name'                   => __( 'name of service', 'bookly' ),
                'service_price'                  => __( 'price of service', 'bookly' ),
            ),
            'staff' => array(
                'staff_email'                    => __( 'email of staff', 'bookly' ),
                'staff_info'                     => __( 'info of staff', 'bookly' ),
                'staff_name'                     => __( 'name of staff', 'bookly' ),
                'staff_phone'                    => __( 'phone of staff', 'bookly' ),
            ),
            'staff_agenda' => array(
                'agenda_date'                    => __( 'agenda date', 'bookly' ),
                'next_day_agenda'                => __( 'staff agenda for next day', 'bookly' ),
                'tomorrow_date'                  => __( 'date of next day', 'bookly' ),
            ),
            'user_credentials' => array(
                'new_password'                   => __( 'customer new password', 'bookly' ),
                'new_username'                   => __( 'customer new username', 'bookly' ),
                'site_address'                   => __( 'site address', 'bookly' ),
            ),
            'rating'           => array(),
        );

        if ( $type == 'email' ) {
            // Only email.
            $this->codes['company']['company_logo'] = __( 'company logo', 'bookly' );
            $this->codes['customer_appointment']['cancel_appointment'] = __( 'cancel appointment link', 'bookly' );
            $this->codes['staff']['staff_photo'] = __( 'photo of staff', 'bookly' );
        }

        // Add codes from add-ons.
        $this->codes = Proxy\Shared::prepareNotificationCodes( $this->codes, $type );
    }

    /**
     * Render codes for given notification type.
     *
     * @param $notification_type
     */
    public function render( $notification_type )
    {
        $codes = $this->_build( $notification_type );

        ksort( $codes );

        $tbody = '';
        foreach ( $codes as $code => $description ) {
            $tbody .= sprintf(
                '<tr><td><input value="{%s}" readonly="readonly" onclick="this.select()" /> - %s</td></tr>',
                $code,
                esc_html( $description )
            );
        }

        printf(
            '<table class="bookly-codes bookly-js-codes-%s"><tbody>%s</tbody></table>',
            $notification_type,
            $tbody
        );
    }

    /**
     * Build array of codes for given notification type.
     *
     * @param $notification_type
     * @return array
     */
    private function _build( $notification_type )
    {
        $codes = array();

        switch ( $notification_type ) {
            case Notification::TYPE_APPOINTMENT_START_TIME:
            case Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED:
            case Notification::TYPE_LAST_CUSTOMER_APPOINTMENT:
            case Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED:
                $codes = array_merge(
                    $this->codes['appointment'],
                    $this->codes['category'],
                    $this->codes['service'],
                    $this->codes['customer_appointment'],
                    $this->codes['customer'],
                    $this->codes['customer_timezone'],
                    $this->codes['staff'],
                    $this->codes['payment'],
                    $this->codes['company']
                );
                if ( Lib\Config::invoicesActive() &&
                    in_array( $notification_type, array(
                        Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED,
                        Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED,
                    ) )
                ) {
                    $codes = array_merge( $codes, $this->codes['invoice'] );
                }
                break;
            case 'staff_agenda':
            case Notification::TYPE_STAFF_DAY_AGENDA:
                $codes = array_merge(
                    $this->codes['staff'],
                    $this->codes['staff_agenda'],
                    $this->codes['company']
                );
                break;
            case 'client_birthday_greeting':
            case Notification::TYPE_CUSTOMER_BIRTHDAY:
                $codes = array_merge(
                    $this->codes['customer'],
                    $this->codes['company']
                );
                break;
            case 'client_new_wp_user':
                $codes = array_merge(
                    $this->codes['customer'],
                    $this->codes['user_credentials'],
                    $this->codes['company']
                );
                break;
            case 'client_pending_appointment_cart':
            case 'client_approved_appointment_cart':
                $codes = array_merge(
                    $this->codes['cart'],
                    $this->codes['customer'],
                    $this->codes['customer_timezone'],
                    $this->codes['payment'],
                    $this->codes['company']
                );
                break;
            case 'client_pending_appointment':
            case 'staff_pending_appointment':
            case 'client_approved_appointment':
            case 'staff_approved_appointment':
            case 'client_cancelled_appointment':
            case 'staff_cancelled_appointment':
            case 'client_rejected_appointment':
            case 'staff_rejected_appointment':
            case 'client_waitlisted_appointment':
            case 'staff_waitlisted_appointment':
            case 'client_reminder':
            case 'client_reminder_1st':
            case 'client_reminder_2nd':
            case 'client_reminder_3rd':
                $codes = array_merge(
                    $this->codes['appointment'],
                    $this->codes['category'],
                    $this->codes['service'],
                    $this->codes['customer_appointment'],
                    $this->codes['staff'],
                    $this->codes['customer'],
                    $this->codes['customer_timezone'],
                    $this->codes['payment'],
                    $this->codes['company']
                );
                if ( Lib\Config::invoicesActive() &&
                    in_array( $notification_type, array(
                        'client_pending_appointment',
                        'client_approved_appointment'
                    ) )
                ) {
                    $codes = array_merge( $codes, $this->codes['invoice'] );
                }
                break;
            case 'client_follow_up':
                $codes = array_merge(
                    $this->codes['appointment'],
                    $this->codes['category'],
                    $this->codes['service'],
                    $this->codes['customer_appointment'],
                    $this->codes['staff'],
                    $this->codes['customer'],
                    $this->codes['customer_timezone'],
                    $this->codes['payment'],
                    $this->codes['company'],
                    $this->codes['rating'],
                    isset( $this->codes['invoice'] ) ? $this->codes['invoice'] : array()
                );
                break;
            default:
                $codes = Proxy\Shared::buildNotificationCodesList( $codes, $notification_type, $this->codes );
        }

        return $codes;
    }

    /**
     * @param array $groups
     * @param bool  $echo
     * @return string
     */
    public function renderGroups( array $groups, $echo = true )
    {
        $codes = array();
        foreach ( $groups as $group ) {
            if ( array_key_exists( $group, $this->codes ) ) {
                $codes = array_merge( $codes, $this->codes[ $group ] );
            }
        }

        ksort( $codes );

        $tbody = '';
        foreach ( $codes as $code => $description ) {
            $tbody .= sprintf(
                '<tr><td><input value="{%s}" readonly="readonly" onclick="this.select()" /> - %s</td></tr>',
                $code,
                esc_html( $description )
            );
        }

        $result = sprintf(
            '<table class="bookly-codes"><tbody>%s</tbody></table>',
            $tbody
        );

        if ( $echo ) {
            echo $result;
        }

        return $result;
    }
}